<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\Solidary\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidaryResult\SolidaryResult;
use App\Solidary\Entity\SolidaryResult\SolidaryResultCarpool;
use App\Solidary\Entity\SolidaryResult\SolidaryResultTransport;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryMatchingRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Result;
use App\Carpool\Repository\MatchingRepository;
use App\Solidary\Entity\Structure;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryMatcher
{
    private $solidaryMatchingRepository;
    private $entityManager;
    private $matchingRepository;
    private $params;

    public function __construct(SolidaryMatchingRepository $solidaryMatchingRepository, EntityManagerInterface $entityManager, MatchingRepository $matchingRepository, array $params)
    {
        $this->solidaryMatchingRepository = $solidaryMatchingRepository;
        $this->entityManager = $entityManager;
        $this->matchingRepository = $matchingRepository;
        $this->params = $params;
    }

    /**
     * Build and persist the solidary matchings for a Solidary based on a set of solidary search Transport results
     *
     * @param Solidary $solidary    The solidary
     * @param array $results        The results of a solidary search
     * @return array|null
     */
    public function buildSolidaryMatchingsForTransport(Solidary $solidary, array $results): ?array
    {
        $solidaryMatchings = [];
        
        // We get the previous SolidaryMatchings of this solidary
        $previousMatchings = $this->solidaryMatchingRepository->findSolidaryMatchingTransportOfSolidary($solidary);

        foreach ($results as $solidaryUser) {

            // We check if the matching already exists
            $matchingAlreadyExists = false;
            foreach ($previousMatchings as $previousMatching) {
                if ($previousMatching->getSolidaryUser()->getId() == $solidaryUser->getId() &&
                $previousMatching->getSolidary()->getId() == $solidary->getId()
                ) {
                    $matchingAlreadyExists = true;
                    // We keep the previous matching
                    $solidaryMatching = $previousMatching;
                    break;
                }
            }

            // If this matching doesn't already exists we persist it and we add it to the return
            if (!$matchingAlreadyExists) {
                $solidaryMatching = new SolidaryMatching();
                $solidaryMatching->setSolidaryUser($solidaryUser);
                $solidaryMatching->setSolidary($solidary);
                // We use the Criteria of the Proposal of the Matching
                $criteria = clone $solidary->getProposal()->getCriteria();
                $solidaryMatching->setCriteria($criteria);

                $this->entityManager->persist($solidaryMatching);
                $this->entityManager->flush();
                // We add the matching the return list
                $solidaryMatchings[] = $solidaryMatching;
            } else {
                // We check if there already is a SolidaryAsk for this matching
                $solidaryAsk = $this->solidaryMatchingRepository->findAskOfSolidaryMatching($solidaryMatching);
                // There is no Ask, we can add this solidaryMatching to the return
                if (is_null($solidaryAsk)) {
                    $solidaryMatchings[] = $solidaryMatching;
                }
            }
        }

        return $solidaryMatchings;
    }

    /**
     * Build and persist the solidary matchings for a Solidary based on a set of solidary search Carpool results
     *
     * @param Solidary $solidary    The solidary
     * @param array $results        The results of an Ad
     * @return array|null
     */
    public function buildSolidaryMatchingsForCarpool(Solidary $solidary, array $results): ?array
    {
        $solidaryMatchings = [];

        // We get the previous SolidaryMatchings of this solidary
        $previousMatchings = $this->solidaryMatchingRepository->findSolidaryMatchingCarpoolOfSolidary($solidary);

        foreach ($results as $result) {
            
            /**
             * @var Result $result
             */

            // We get the ResultItem
            $resultItem = $result->getResultPassenger()->getOutward();

            // We check if the matching already exists
            $matchingAlreadyExists = false;
            foreach ($previousMatchings as $previousMatching) {
                if ($previousMatching->getMatching()->getId() == $resultItem->getMatchingId() &&
                $previousMatching->getSolidary()->getId() == $solidary->getId()
                ) {
                    $matchingAlreadyExists = true;
                    // We keep the previous matching
                    $solidaryMatching = $previousMatching;
                    break;
                }
            }

            // If this matching doesn't already exists we persist it and we add it to the return
            if (!$matchingAlreadyExists) {
                $solidaryMatching = new SolidaryMatching();
                $solidaryMatching->setMatching($this->matchingRepository->find($resultItem->getMatchingId()));
                $solidaryMatching->setSolidary($solidary);
                // We use the Criteria of the Proposal of the Matching
                $criteria = clone $solidary->getProposal()->getCriteria();
                $solidaryMatching->setCriteria($criteria);

                $this->entityManager->persist($solidaryMatching);
                $this->entityManager->flush();
                // We add the matching the return list
                $solidaryMatchings[] = $solidaryMatching;
            } else {
                // We check if there already is a SolidaryAsk for this matching
                $solidaryAsk = $this->solidaryMatchingRepository->findAskOfSolidaryMatching($solidaryMatching);

                // There is no Ask, we can add this solidaryMatching to the return
                if (is_null($solidaryAsk)) {
                    $solidaryMatchings[] = $solidaryMatching;
                }
            }
        }


        return $solidaryMatchings;
    }


    /**
     * Build a SolidaryResult with a SolidaryResultTransport from a SolidaryMatching
     *
     * @param SolidaryMatching $solidaryUser    The Solidary User
     * @return SolidaryResult
     */
    public function buildSolidaryResultTransport(SolidaryMatching $solidaryMatching): SolidaryResult
    {
        $solidaryResult = new SolidaryResult();
        $solidaryResultTransport = new SolidaryResultTransport();
        
        // The volunteer
        $solidaryResultTransport->setVolunteer($solidaryMatching->getSolidaryUser()->getUser()->getGivenName()." ".$solidaryMatching->getSolidaryUser()->getUser()->getFamilyName());
        $solidaryResultTransport->setVolunteerId($solidaryMatching->getSolidaryUser()->getUser()->getId());
        // Home address of the volunteer
        $addresses = $solidaryMatching->getSolidaryUser()->getUser()->getAddresses();
        foreach ($addresses as $address) {
            if ($address->isHome()) {
                $solidaryResultTransport->setHome($address->getAddressLocality());
                break;
            }
        }
        
        // Schedule of the volunteer
        $solidaryResultTransport->setSchedule($this->getBuildedSchedule($solidaryMatching->getSolidaryUser()));

        $solidaryResult->setSolidaryResultTransport($solidaryResultTransport);

        // We set the source solidaryMatching
        $solidaryResult->setSolidaryMatching($solidaryMatching);


        return $solidaryResult;
    }

    /**
     * Build a SolidaryResult with a SolidaryResultCarpool from a SolidaryMatching
     *
     * @param SolidaryMatching $solidaryUser    The Solidary User
     * @return SolidaryResult
     */
    public function buildSolidaryResultCarpool(SolidaryMatching $solidaryMatching): SolidaryResult
    {
        $solidaryResult = new SolidaryResult();
        $solidaryResultCarpool = new SolidaryResultCarpool();
        
        // We get the Proposal Offer with all the infos
        
        // The author
        $solidaryResultCarpool->setAuthor($solidaryMatching->getMatching()->getProposalOffer()->getUser()->getGivenName()." ".$solidaryMatching->getMatching()->getProposalOffer()->getUser()->getShortFamilyName());
        $solidaryResultCarpool->setAuthorId($solidaryMatching->getMatching()->getProposalOffer()->getUser()->getId());

        // O/D
        $waypoints = $solidaryMatching->getMatching()->getWaypoints();
        $origin = $waypoints[0]->getAddress()->getAddressLocality();
        $destination = "";
        foreach ($waypoints as $waypoint) {
            if ($waypoint->isDestination()) {
                $destination = $waypoint->getAddress()->getAddressLocality();
                break;
            }
        }

        $solidaryResultCarpool->setOrigin($origin);
        $solidaryResultCarpool->setDestination($destination);
        // The frequency
        $solidaryResultCarpool->setFrequency($solidaryMatching->getMatching()->getCriteria()->getFrequency());


        // Date and schedule
        $solidaryResultCarpool->setDate($solidaryMatching->getMatching()->getCriteria()->getFromDate());
        if ($solidaryResultCarpool->getFrequency()==2) {
            // Schedule only for Regular
            $solidaryResultCarpool->setSchedule($this->getBuildedScheduleRegularCarpool($solidaryMatching->getMatching()->getCriteria()));
        }

        // Is the Proposal solidaryExclusive ?
        $solidaryResultCarpool->setSolidaryExlusive($solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isSolidaryExclusive());

        // Role of the owner of the Proposal
        if ($solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isDriver() && $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isPassenger()) {
            $solidaryResultCarpool->setRole(3);
        } elseif ($solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isDriver()) {
            $solidaryResultCarpool->setRole(1);
        } else {
            throw new SolidaryException(SolidaryException::NOT_A_DRIVER);
        }

        $solidaryResult->setSolidaryResultCarpool($solidaryResultCarpool);
        

        // We set the source solidaryMatching
        $solidaryResult->setSolidaryMatching($solidaryMatching);

        return $solidaryResult;
    }

    /**
     * Get the hour slot of this time
     * m : morning, a : afternoon, e : evening
     *
     * @param \DateTimeInterface $mintime
     * @param \DateTimeInterface $maxtime
     * @param Structure $structure
     * @return string
     */
    public function getHourSlot(\DateTimeInterface $mintime, \DateTimeInterface $maxtime, Structure $structure): string
    {
        // get The hours slot of the structure
        $hoursSlots = [
            "m" => ["min" => (!is_null($structure->getMMinRangeTime())) ? new \DateTime($structure->getMMinRangeTime()->format("H:i:s")) : $this->getDefaultHoursSlotsRanges()["m"]["min"],"max" => (!is_null($structure->getMMaxRangeTime())) ? new \DateTime($structure->getMMaxRangeTime()->format("H:i:s")) : $this->getDefaultHoursSlotsRanges()["m"]["max"]],
            "a" => ["min" => (!is_null($structure->getAMinRangeTime())) ? new \DateTime($structure->getAMinRangeTime()->format("H:i:s")) : $this->getDefaultHoursSlotsRanges()["a"]["min"],"max" => (!is_null($structure->getAMaxRangeTime())) ? new \DateTime($structure->getAMaxRangeTime()->format("H:i:s")) : $this->getDefaultHoursSlotsRanges()["a"]["max"]],
            "e" => ["min" => (!is_null($structure->getEMinRangeTime())) ? new \DateTime($structure->getEMinRangeTime()->format("H:i:s")) : $this->getDefaultHoursSlotsRanges()["e"]["min"],"max" => (!is_null($structure->getEMaxRangeTime())) ? new \DateTime($structure->getEMaxRangeTime()->format("H:i:s")) : $this->getDefaultHoursSlotsRanges()["e"]["max"]]
        ];
        foreach ($hoursSlots as $slot => $hoursSlot) {
            if ($hoursSlot['min']<=$mintime && $mintime<=$hoursSlot['max']) {
                return $slot;
            }
        }
        //should not append
        throw new SolidaryException(SolidaryException::INVALID_HOUR_SLOT);
        return "";
    }

    /**
     * Get the builded schedule of a SolidaryUser
     *
     * @param SolidaryUser $solidaryUser
     * @return array
     */
    public function getBuildedSchedule(SolidaryUser $solidaryUser): array
    {
        ($solidaryUser->hasMMon()) ? $schedule['mon']['m'] = true : $schedule['mon']['m'] = false;
        ($solidaryUser->hasMTue()) ? $schedule['tue']['m'] = true : $schedule['tue']['m'] = false;
        ($solidaryUser->hasMWed()) ? $schedule['wed']['m'] = true : $schedule['wed']['m'] = false;
        ($solidaryUser->hasMThu()) ? $schedule['thu']['m'] = true : $schedule['thu']['m'] = false;
        ($solidaryUser->hasMFri()) ? $schedule['fri']['m'] = true : $schedule['fri']['m'] = false;
        ($solidaryUser->hasMSat()) ? $schedule['sat']['m'] = true : $schedule['sat']['m'] = false;
        ($solidaryUser->hasMSun()) ? $schedule['sun']['m'] = true : $schedule['sun']['m'] = false;
        ($solidaryUser->hasAMon()) ? $schedule['mon']['a'] = true : $schedule['mon']['a'] = false;
        ($solidaryUser->hasATue()) ? $schedule['tue']['a'] = true : $schedule['tue']['a'] = false;
        ($solidaryUser->hasAWed()) ? $schedule['wed']['a'] = true : $schedule['wed']['a'] = false;
        ($solidaryUser->hasAThu()) ? $schedule['thu']['a'] = true : $schedule['thu']['a'] = false;
        ($solidaryUser->hasAFri()) ? $schedule['fri']['a'] = true : $schedule['fri']['a'] = false;
        ($solidaryUser->hasASat()) ? $schedule['sat']['a'] = true : $schedule['sat']['a'] = false;
        ($solidaryUser->hasASun()) ? $schedule['sun']['a'] = true : $schedule['sun']['a'] = false;
        ($solidaryUser->hasEMon()) ? $schedule['mon']['e'] = true : $schedule['mon']['e'] = false;
        ($solidaryUser->hasETue()) ? $schedule['tue']['e'] = true : $schedule['tue']['e'] = false;
        ($solidaryUser->hasEWed()) ? $schedule['wed']['e'] = true : $schedule['wed']['e'] = false;
        ($solidaryUser->hasEThu()) ? $schedule['thu']['e'] = true : $schedule['thu']['e'] = false;
        ($solidaryUser->hasEFri()) ? $schedule['fri']['e'] = true : $schedule['fri']['e'] = false;
        ($solidaryUser->hasESat()) ? $schedule['sat']['e'] = true : $schedule['sat']['e'] = false;
        ($solidaryUser->hasESun()) ? $schedule['sun']['e'] = true : $schedule['sun']['e'] = false;

        return $schedule;
    }

    /**
     * Get the builded schedule of a Criteria
     *
     * @param Criteria $criteria
     * @return array
     */
    public function getBuildedScheduleRegularCarpool(Criteria $criteria): array
    {
        $schedule = [];

        if ($criteria->isMonCheck()) {
            $schedule['mon']['minTime'] = $criteria->getMonMinTime();
            $schedule['mon']['maxTime'] = $criteria->getMonMaxTime();
        }
        if ($criteria->isTueCheck()) {
            $schedule['tue']['minTime'] = $criteria->getTueMinTime();
            $schedule['tue']['maxTime'] = $criteria->getTueMaxTime();
        }
        if ($criteria->isWedCheck()) {
            $schedule['wed']['minTime'] = $criteria->getWedMinTime();
            $schedule['wed']['maxTime'] = $criteria->getWedMaxTime();
        }
        if ($criteria->isThuCheck()) {
            $schedule['thu']['minTime'] = $criteria->getThuMinTime();
            $schedule['thu']['maxTime'] = $criteria->getThuMaxTime();
        }
        if ($criteria->isFriCheck()) {
            $schedule['fri']['minTime'] = $criteria->getFriMinTime();
            $schedule['fri']['maxTime'] = $criteria->getFriMaxTime();
        }
        if ($criteria->isSatCheck()) {
            $schedule['sat']['minTime'] = $criteria->getSatMinTime();
            $schedule['sat']['maxTime'] = $criteria->getSatMaxTime();
        }
        if ($criteria->isSunCheck()) {
            $schedule['sun']['minTime'] = $criteria->getSunMinTime();
            $schedule['sun']['maxTime'] = $criteria->getSunMaxTime();
        }

        return $schedule;
    }


    /**
     * Get the instance default hours slots ranges
     * (i.e. to determine if a time is in morning, afternoon or evening when no indication is given otherwise)
     *
     * @return array
     */
    public function getDefaultHoursSlotsRanges(): array
    {
        return [
            "m" => ["min" => new \DateTime($this->params['solidaryMMinRangeTime']),"max" => new \DateTime($this->params['solidaryMMaxRangeTime'])],
            "a" => ["min" => new \DateTime($this->params['solidaryAMinRangeTime']),"max" => new \DateTime($this->params['solidaryAMaxRangeTime'])],
            "e" => ["min" => new \DateTime($this->params['solidaryEMinRangeTime']),"max" => new \DateTime($this->params['solidaryEMaxRangeTime'])]
        ];
    }
}
