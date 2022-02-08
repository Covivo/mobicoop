<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace App\Carpool\Service;

use App\Carpool\Entity\AntiFraudResponse;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Exception\AntiFraudException;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Geography\Entity\Address;
use App\Geography\Service\GeoRouter;
use App\User\Service\UserManager;
use DateTime;

/**
 * Anti-Fraud system manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AntiFraudManager
{
    private $geoRouter;
    private $proposalRepository;
    private $userManager;

    // Parameters
    private $distanceMinCheck;
    private $nbCarpoolsMax;
    private $active;

    private $sameDayProposals;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        GeoRouter $geoRouter,
        ProposalRepository $proposalRepository,
        UserManager $userManager,
        array $params
    ) {
        $this->geoRouter = $geoRouter;
        $this->proposalRepository = $proposalRepository;
        $this->userManager = $userManager;
        $this->distanceMinCheck = $params['distanceMinCheck'];
        $this->nbCarpoolsMax = $params['nbCarpoolsMax'];
        $this->active = $params['active'];
    }

    /**
     * Check if an Ad is valid against the Anti-Fraud system rules
     * If the Anti-Fraud system is inactive, or the Ad is a Search or the role is passenger only, it's an automatic validation.
     *
     * @param Ad $ad The Ad to check
     *
     * @return AntiFraudResponse The response
     */
    public function validAd(Ad $ad, bool $unpausedAd = false): AntiFraudResponse
    {
        // Default response is that the Ad is valid
        $response = new AntiFraudResponse(true, AntiFraudException::OK);

        // If the Anti-Fraud system is inactive, or the Ad is a Search or the role is passenger only, it's an automatic validation
        if (!$this->active || Ad::ROLE_PASSENGER == $ad->getRole() || $ad->isSearch()) {
            return $response;
        }

        // Compute the distance of the journey

        $addressesToValidate = [];
        foreach ($ad->getOutwardWaypoints() as $pointToValidate) {
            $waypointToValidate = new Address();
            if (is_array($pointToValidate)) {
                $waypointToValidate->setLatitude(isset($pointToValidate['latitude']) ? $pointToValidate['latitude'] : $pointToValidate['address']['latitude']);
                $waypointToValidate->setLongitude(isset($pointToValidate['longitude']) ? $pointToValidate['longitude'] : $pointToValidate['address']['longitude']);
            } else {
                $waypointToValidate->setLatitude($pointToValidate->getLatitude());
                $waypointToValidate->setLongitude($pointToValidate->getLongitude());
            }
            $addressesToValidate[] = $waypointToValidate;
        }

        $route = $this->geoRouter->getRoutes($addressesToValidate, false, true);
        // var_dump($route);

        // If the journey is above the $distanceMinCheck paramaters we need to check it otherwise, it's an immediate validation
        if (($route[0]->getDistance() / 1000) > $this->distanceMinCheck) {
            // FIRST CHECK
            $response = $this->validAdFirstCheck($ad);

            if (!$response->isValid()) {
                // we check if the arrival date is after the start date of the proposal we are trying to post or unpaused on the same day
                foreach ($this->sameDayProposals as $sameDayProposal) {
                    if ($ad->getProposalId() == $sameDayProposal->getId()) {
                        // In case of adding a return, we avoid checkin the Ad against the outward proposal
                        continue;
                    }
                    if ($unpausedAd) {
                        $response = $this->checkValidHoursUnpausedAd($ad, $sameDayProposal);
                        if (!$response->isValid()) {
                            return $response;
                        }
                    }
                    $response = $this->checkValidHours($ad, $sameDayProposal);
                    if (!$response->isValid()) {
                        return $response;
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Anti Fraud System first check - Max number of journeys
     * A user can only have $nbCarpoolsMax on the same day.
     *
     * @param Ad $ad The Ad we are trying to post
     */
    private function validAdFirstCheck(Ad $ad): AntiFraudResponse
    {
        // By default, the outward date is immutable, we need to make a regular Datetime
        $dateTime = new \DateTime(null, $ad->getOutwardDate()->getTimezone());
        $dateTime->setTimestamp($ad->getOutwardDate()->getTimestamp());

        // Setup the User if it exists
        $user = null;
        if (!is_null($ad->getUser())) {
            $user = $ad->getUser();
        } elseif (!is_null($ad->getUserId())) {
            $user = $this->userManager->getUser($ad->getUserId());
        }

        $proposals = $this->proposalRepository->findByDate($dateTime, $user, true, $this->distanceMinCheck * 1000, null, [$ad->getProposalId()]);

        if (!is_null($proposals) && is_array($proposals) && count($proposals) >= $this->nbCarpoolsMax) {
            $this->sameDayProposals = $proposals;

            return new AntiFraudResponse(false, AntiFraudException::TOO_MANY_AD);
        }

        return new AntiFraudResponse(true, AntiFraudException::OK);
    }

    /**
     * Check if the hours are valid regarding an unpaused ad that is active on the same day.
     *
     * @param Ad       $ad              The Ad we are trying to unpaused
     * @param Proposal $sameDayProposal The Proposal active on the same day that the Ad
     */
    private function checkValidHours(Ad $ad, Proposal $sameDayProposal): AntiFraudResponse
    {
        if (Criteria::FREQUENCY_PUNCTUAL == $sameDayProposal->getCriteria()->getFrequency()) {
            // We try to unpaused a Regular Ad that matches a punctual ad
            $dayOfTheProposal = $sameDayProposal->getCriteria()->getFromDate()->format('D');

            $arrivalProposalDateTime = $sameDayProposal->getCriteria()->getArrivalDateTime();
            if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
                foreach ($ad->getSchedule() as $schedule) {
                    if (isset($schedule[strtolower($dayOfTheProposal)]) && $schedule[strtolower($dayOfTheProposal)]) {
                        $adOutwardDateTime = DateTime::createFromFormat('U', $sameDayProposal->getCriteria()->getFromDate()->getTimestamp());
                        $outwardTime = explode(':', $schedule['outwardTime']);
                        $adOutwardDateTime->setTime((int) $outwardTime[0], (int) $outwardTime[1]);

                        break;
                    }
                }
            } else {
                $adOutwardDateTime = DateTime::createFromFormat('U', $sameDayProposal->getCriteria()->getFromDate()->getTimestamp());
                $outwardTime = explode(':', $ad->getOutwardTime());
                $adOutwardDateTime->setTime((int) $outwardTime[0], (int) $outwardTime[1]);
            }

            if ($adOutwardDateTime <= $arrivalProposalDateTime) {
                return new AntiFraudResponse(false, AntiFraudException::INVALID_TIME);
            }

            return new AntiFraudResponse(true, AntiFraudException::OK);
        }
        // Regular journey

        // We unpaused a regular ad that matches a Regular

        // We will only compare the hours without considering the date

        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        foreach ($days as $day) {
            // We check if the Ad we are trying to post as a monday check in a schedule

            foreach ($ad->getSchedule() as $schedule) {
                if (isset($schedule[$day]) && $schedule[$day]) {
                    $adDateTime = new \DateTime('now');
                    $outwardTime = explode(':', $schedule['outwardTime']);
                    $adDateTime->setTime((int) $outwardTime[0], (int) $outwardTime[1]);

                    // We check if there is a time for this day in the matching proposal
                    switch ($day) {
                                case 'sun': $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalSunTime();

                                break;

                                case 'mon': $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalMonTime();

                                break;

                                case 'tue': $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalTueTime();

                                break;

                                case 'wed': $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalWedTime();

                                break;

                                case 'thu': $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalThuTime();

                                break;

                                case 'fri': $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalFriTime();

                                break;

                                case 'sat': $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalSatTime();

                                break;
                            }

                    if (!is_null($arrivalProposalTime)) {
                        if ($adDateTime <= $arrivalProposalTime) {
                            return new AntiFraudResponse(false, AntiFraudException::INVALID_TIME);
                        }
                    }
                }
            }
        }

        return new AntiFraudResponse(true, AntiFraudException::OK);
    }

    /**
     * Check if the hours are valid regarding a proposal that is active on the same day.
     *
     * @param Ad       $ad              The Ad we are trying to post
     * @param Proposal $sameDayProposal The Proposal active on the same day that the Ad
     */
    private function checkValidHoursUnpausedAd(Ad $ad, Proposal $sameDayProposal): AntiFraudResponse
    {
        if (!is_null($sameDayProposal->getCriteria()->getDirectionDriver())) {
            $duration = $sameDayProposal->getCriteria()->getDirectionDriver()->getDuration();
        } else {
            $duration = $sameDayProposal->getCriteria()->getDirectionPassenger()->getDuration();
        }

        if (Criteria::FREQUENCY_PUNCTUAL == $sameDayProposal->getCriteria()->getFrequency()) {
            // Punctual journey matches puntucal journey
            if (Criteria::FREQUENCY_PUNCTUAL == $ad->getFrequency()) {
                $outwardDate = DateTime::createFromFormat('U', $ad->getOutwardDate()->getTimestamp());
                $outwardTime = explode(':', $ad->getOutwardTime());
                $outwardDate->setTime((int) $outwardTime[0], (int) $outwardTime[1]);
                $arrivalDateTime = $sameDayProposal->getCriteria()->getArrivalDateTime();

                if ($outwardDate <= $arrivalDateTime) {
                    return new AntiFraudResponse(false, AntiFraudException::INVALID_TIME);
                }
            } else {
                // We try to post a Regular Ad that matches a punctual ad
                $dayOfTheProposal = $sameDayProposal->getCriteria()->getFromDate()->format('D');

                $arrivalProposalDateTime = $sameDayProposal->getCriteria()->getArrivalDateTime();
                $schedule = $ad->getSchedule();

                if (isset($schedule[strtolower($dayOfTheProposal)]) && $schedule[strtolower($dayOfTheProposal)]) {
                    $adOutwardDateTime = DateTime::createFromFormat('U', $sameDayProposal->getCriteria()->getFromDate()->getTimestamp());

                    switch (strtolower($dayOfTheProposal)) {
                            case 'sun':
                                $adOutwardDateTime->setTime((new DateTime($schedule['sunOutwardTime']))->format('H'), (new DateTime($schedule['sunOutwardTime']))->format('i'));

                                break;

                            case 'mon':
                                $adOutwardDateTime->setTime((new DateTime($schedule['monOutwardTime']))->format('H'), (new DateTime($schedule['monOutwardTime']))->format('i'));

                                break;

                            case 'tue':
                                $adOutwardDateTime->setTime((new DateTime($schedule['tueOutwardTime']))->format('H'), (new DateTime($schedule['tueOutwardTime']))->format('i'));

                                break;

                            case 'wed':
                                $adOutwardDateTime->setTime((new DateTime($schedule['wedOutwardTime']))->format('H'), (new DateTime($schedule['wedOutwardTime']))->format('i'));

                                break;

                            case 'thu':
                                $adOutwardDateTime->setTime((new DateTime($schedule['thuOutwardTime']))->format('H'), (new DateTime($schedule['thuOutwardTime']))->format('i'));

                                break;

                            case 'fri':
                                $adOutwardDateTime->setTime((new DateTime($schedule['friOutwardTime']))->format('H'), (new DateTime($schedule['friOutwardTime']))->format('i'));

                                break;

                            case 'sat':
                                $adOutwardDateTime->setTime((new DateTime($schedule['satOutwardTime']))->format('H'), (new DateTime($schedule['satOutwardTime']))->format('i'));

                                break;
                        }
                }

                if ($adOutwardDateTime <= $arrivalProposalDateTime) {
                    return new AntiFraudResponse(false, AntiFraudException::INVALID_TIME);
                }

                return new AntiFraudResponse(true, AntiFraudException::OK);
            }
        } else {
            // Regular journey
            // We post a punctual that matches a Regular
            if (Criteria::FREQUENCY_PUNCTUAL == $ad->getFrequency()) {
                $dayOfTheAd = $ad->getOutwardDate()->format('D');

                switch ($dayOfTheAd) {
                    case 'Sun': $arrivalTime = $sameDayProposal->getCriteria()->getArrivalSunTime();

                    break;

                    case 'Mon': $arrivalTime = $sameDayProposal->getCriteria()->getArrivalMonTime();

                    break;

                    case 'Tue': $arrivalTime = $sameDayProposal->getCriteria()->getArrivalTueTime();

                    break;

                    case 'Wed': $arrivalTime = $sameDayProposal->getCriteria()->getArrivalWedTime();

                    break;

                    case 'Thu': $arrivalTime = $sameDayProposal->getCriteria()->getArrivalThuTime();

                    break;

                    case 'Fri': $arrivalTime = $sameDayProposal->getCriteria()->getArrivalFriTime();

                    break;

                    case 'Sat': $arrivalTime = $sameDayProposal->getCriteria()->getArrivalSatTime();

                    break;
                }

                $arrivalProposalDateTime = DateTime::createFromFormat('U', $ad->getOutwardDate()->getTimestamp());
                $arrivalProposalDateTime->setTime($arrivalTime->format('H'), $arrivalTime->format('i'));

                $adOutwardDateTime = DateTime::createFromFormat('U', $ad->getOutwardDate()->getTimestamp());
                $outwardTime = explode(':', $ad->getOutwardTime());
                $adOutwardDateTime->setTime((int) $outwardTime[0], (int) $outwardTime[1]);

                if ($adOutwardDateTime <= $arrivalProposalDateTime) {
                    return new AntiFraudResponse(false, AntiFraudException::INVALID_TIME);
                }
            } else {
                // We post a regular that matches a Regular
                // We will only compare the hours without considering the date

                $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

                foreach ($days as $day) {
                    // We check if the Ad we are trying to post as a monday check in a schedule

                    $schedule = $ad->getSchedule();

                    if (isset($schedule[$day]) && $schedule[$day]) {
                        $adDateTime = new \DateTime('now');

                        // We check if there is a time for this day in the matching proposal
                        switch ($day) {
                                case 'sun':
                                    $adDateTime = $schedule['sunOutwardTime'];
                                    $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalSunTime();

                                    break;

                                case 'mon':
                                    $adDateTime = $schedule['monOutwardTime'];
                                    $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalMonTime();

                                    break;

                                case 'tue':
                                    $adDateTime = $schedule['tueOutwardTime'];
                                    $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalTueTime();

                                    break;

                                case 'wed':
                                    $adDateTime = $schedule['wedOutwardTime'];
                                    $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalWedTime();

                                break;

                                case 'thu':
                                    $adDateTime = $schedule['thuOutwardTime'];
                                    $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalThuTime();

                                    break;

                                case 'fri':
                                    $adDateTime = $schedule['friOutwardTime'];
                                    $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalFriTime();

                                    break;

                                case 'sat':
                                    $adDateTime = $schedule['satOutwardTime'];
                                    $arrivalProposalTime = $sameDayProposal->getCriteria()->getArrivalSatTime();

                                    break;
                            }

                        if (!is_null($arrivalProposalTime)) {
                            if ($adDateTime <= $arrivalProposalTime) {
                                return new AntiFraudResponse(false, AntiFraudException::INVALID_TIME);
                            }
                        }
                    }
                }
            }
        }

        return new AntiFraudResponse(true, AntiFraudException::OK);
    }
}
