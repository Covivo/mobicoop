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

namespace App\Carpool\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Ask;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\MyAd;
use App\Carpool\Entity\Waypoint;
use App\User\Entity\User;
use DateTime;

/**
 * MyAd manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class MyAdManager
{
    private $proposalRepository;

    /**
     * Constructor.
     *
     * @param ProposalRepository $proposalRepository    The proposal repository
     */
    public function __construct(ProposalRepository $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
    }

    /**
     * Get MyAds for a given user
     *
     * @param User $user    The user
     * @return array        The MyAds found
     */
    public function getMyAds(User $user): array
    {
        $myAds = [];

        // we retrieve all the proposals of the user
        $proposals = $this->proposalRepository->findAllForUser($user);
        foreach ($proposals as $proposal) {
            $myAds[] = $this->createMyAdFromProposal($proposal);
        }
        return $myAds;
    }

    /**
     * Create a MyAd object from a Proposal object
     *
     * @param Proposal $proposal    The proposal
     * @return MyAd                 The resulting MyAd
     */
    private function createMyAdFromProposal(Proposal $proposal)
    {
        $myAd = new MyAd();
        $myAd->setId($proposal->getId());
        $myAd->setPublished(!$proposal->isPrivate());
        $myAd->setFrequency($proposal->getCriteria()->getFrequency());
        $myAd->setRoleDriver($proposal->getCriteria()->isDriver());
        $myAd->setRolePassenger($proposal->getCriteria()->isPassenger());

        /**
         * @var DateTime $outwardDate
         */
        $outwardDate = $proposal->getCriteria()->getFromDate();
        switch ($proposal->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                $outwardDate->setTime(
                    $proposal->getCriteria()->getFromTime()->format('H'),
                    $proposal->getCriteria()->getFromTime()->format('i')
                );
                if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
                    // there's a return trip
                    /**
                     * @var DateTime $returnDate
                     */
                    $returnDate = $proposal->getProposalLinked()->getCriteria()->getFromDate();
                    if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $returnDate->setTime(
                            $proposal->getProposalLinked()->getCriteria()->getFromTime()->format('H'),
                            $proposal->getProposalLinked()->getCriteria()->getFromTime()->format('i')
                        );
                    }
                    $myAd->setReturnDate($returnDate);
                }
                break;
            case Criteria::FREQUENCY_REGULAR:
                $myAd->setSchedule($this->getScheduleFromCriteria($proposal->getCriteria(), $proposal->getType() != Proposal::TYPE_ONE_WAY ? $proposal->getProposalLinked()->getCriteria() : null));
                break;

        }
        $myAd->setOutwardDate($outwardDate);
        $myAd->setToDate($proposal->getCriteria()->getToDate());

        // waypoints
        $waypoints = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            $waypoints[] = [
                'position' => $waypoint->getPosition(),
                'destination' => $waypoint->isDestination(),
                'houseNumber' => $waypoint->getAddress()->getHouseNumber(),
                'street' => $waypoint->getAddress()->getStreet(),
                'streetAddress' => $waypoint->getAddress()->getStreetAddress(),
                'postalCode' => $waypoint->getAddress()->getPostalCode(),
                'addressLocality' => $waypoint->getAddress()->getAddressLocality(),
                'region' => $waypoint->getAddress()->getRegion(),
                'addressCountry' => $waypoint->getAddress()->getAddressCountry(),
            ];
        }
        $myAd->setWaypoints($waypoints);

        // the price is the computed rounded price, as a driver first then as a passenger
        $myAd->setPrice($myAd->hasRoleDriver() ? $proposal->getCriteria()->getDriverComputedRoundedPrice() : $proposal->getCriteria()->getPassengerComputedRoundedPrice());
        $myAd->setPriceKm($proposal->getCriteria()->getPriceKm());
        // the number of seats is as a driver first then as a passenger
        $myAd->setSeats($myAd->hasRoleDriver() ? $proposal->getCriteria()->getSeatsDriver() : $proposal->getCriteria()->getSeatsPassenger());
        $myAd->setComment($proposal->getComment());

        // are there potential carpoolers ? and/or accepted asks ?
        $carpoolers = [];
        $driver = [];
        $passengers = [];
        foreach ($proposal->getMatchingOffers() as $matchingOffer) {
            // the user is passenger
            /**
             * @var Matching $matchingOffer
             */
            // we exclude private proposals for the carpooler count
            if (!$matchingOffer->getProposalOffer()->isPrivate()) {
                $carpoolers[] = $matchingOffer->getProposalOffer()->getUser()->getId();
            }
            // check for accepted asks (driver)
            foreach ($matchingOffer->getAsks() as $ask) {
                /**
                 * @var Ask $ask
                 */
                if (
                    $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER &&
                    ($ask->getUser()->getId() == $proposal->getUser()->getId() || $ask->getUserRelated()->getId() == $proposal->getUser()->getId())
                ) {
                    $driver = $this->getDriverDetailsForUserAndAsk($proposal->getUser(), $ask);
                    // theorically, only one driver, if we found it we exit the loop
                    break;
                }
            }
        }
        foreach ($proposal->getMatchingRequests() as $matchingRequest) {
            // the user is driver
            /**
             * @var Matching $matchingRequest
             */
            // we exclude private proposals for the carpooler count
            if (!$matchingRequest->getProposalRequest()->isPrivate() && !in_array($matchingRequest->getProposalRequest()->getUser()->getId(), $carpoolers)) {
                $carpoolers[] = $matchingRequest->getProposalRequest()->getUser()->getId();
            }
            // check for accepted asks (passengers)
            foreach ($matchingRequest->getAsks() as $ask) {
                /**
                 * @var Ask $ask
                 */
                if (
                    $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER &&
                    ($ask->getUser()->getId() == $proposal->getUser()->getId() || $ask->getUserRelated()->getId() == $proposal->getUser()->getId())
                ) {
                    $passengers[] = $this->getPassengerDetailsForUserAndAsk($proposal->getUser(), $ask);
                }
            }
        }
        $myAd->setCarpoolers(count($carpoolers));

        $myAd->setDriver($driver);
        $myAd->setPassengers($passengers);

        return $myAd;
    }

    /**
     * Create schedule from Criteria
     *
     * @param Criteria $criteria                The outward Criteria
     * @param Criteria|null $returnCriteria     The return Criteria
     * @return array                            The schedule
     */
    private function getScheduleFromCriteria(Criteria $criteria, ?Criteria $returnCriteria = null)
    {
        // /!\ a day can be checked with no associated time, when private regular search
        $schedule['mon']['check'] = $criteria->isMonCheck() || ($returnCriteria ? $returnCriteria->isMonCheck() : false);
        $schedule['mon']['outwardTime'] = $criteria->isMonCheck() && !is_null($criteria->getMonTime()) ? $criteria->getMonTime()->format('H:i') : null;
        $schedule['mon']['returnTime'] = $returnCriteria && $returnCriteria->isMonCheck() && !is_null($returnCriteria->getMonTime()) ? $returnCriteria->getMonTime()->format('H:i') : null;
        $schedule['tue']['check'] = $criteria->isTueCheck() || ($returnCriteria ? $returnCriteria->isTueCheck() : false);
        $schedule['tue']['outwardTime'] = $criteria->isTueCheck() && !is_null($criteria->getTueTime()) ? $criteria->getTueTime()->format('H:i') : null;
        $schedule['tue']['returnTime'] = $returnCriteria && $returnCriteria->isTueCheck() && !is_null($returnCriteria->getTueTime()) ? $returnCriteria->getTueTime()->format('H:i') : null;
        $schedule['wed']['check'] = $criteria->isWedCheck() || ($returnCriteria ? $returnCriteria->isWedCheck() : false);
        $schedule['wed']['outwardTime'] = $criteria->isWedCheck() && !is_null($criteria->getWedTime()) ? $criteria->getWedTime()->format('H:i') : null;
        $schedule['wed']['returnTime'] = $returnCriteria && $returnCriteria->isWedCheck() && !is_null($returnCriteria->getWedTime()) ? $returnCriteria->getWedTime()->format('H:i') : null;
        $schedule['thu']['check'] = $criteria->isThuCheck() || ($returnCriteria ? $returnCriteria->isThuCheck() : false);
        $schedule['thu']['outwardTime'] = $criteria->isThuCheck() && !is_null($criteria->getThuTime()) ? $criteria->getThuTime()->format('H:i') : null;
        $schedule['thu']['returnTime'] = $returnCriteria && $returnCriteria->isThuCheck() && !is_null($returnCriteria->getThuTime()) ? $returnCriteria->getThuTime()->format('H:i') : null;
        $schedule['fri']['check'] = $criteria->isFriCheck() || ($returnCriteria ? $returnCriteria->isFriCheck() : false);
        $schedule['fri']['outwardTime'] = $criteria->isFriCheck() && !is_null($criteria->getFriTime()) ? $criteria->getFriTime()->format('H:i') : null;
        $schedule['fri']['returnTime'] = $returnCriteria && $returnCriteria->isFriCheck() && !is_null($returnCriteria->getFriTime()) ? $returnCriteria->getFriTime()->format('H:i') : null;
        $schedule['sat']['check'] = $criteria->isSatCheck() || ($returnCriteria ? $returnCriteria->isSatCheck() : false);
        $schedule['sat']['outwardTime'] = $criteria->isSatCheck() && !is_null($criteria->getSatTime()) ? $criteria->getSatTime()->format('H:i') : null;
        $schedule['sat']['returnTime'] = $returnCriteria && $returnCriteria->isSatCheck() && !is_null($returnCriteria->getSatTime()) ? $returnCriteria->getSatTime()->format('H:i') : null;
        $schedule['sun']['check'] = $criteria->isSunCheck() || ($returnCriteria ? $returnCriteria->isSunCheck() : false);
        $schedule['sun']['outwardTime'] = $criteria->isSunCheck() && !is_null($criteria->getSunTime()) ? $criteria->getSunTime()->format('H:i') : null;
        $schedule['sun']['returnTime'] = $returnCriteria && $returnCriteria->isSunCheck() && !is_null($returnCriteria->getSunTime()) ? $returnCriteria->getSunTime()->format('H:i') : null;

        $schedule['outwardTime'] = null;
        $schedule['returnTime'] = null;
        if ($criteria->isMonCheck() && !is_null($criteria->getMonTime())) {
            $schedule['outwardTime'] = $criteria->getMonTime()->format('H:i');
        }
        if ($criteria->isTueCheck() && !is_null($criteria->getTueTime())) {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getTueTime()->format('H:i');
            } elseif ($schedule['outwardTime'] !== $criteria->getTueTime()->format('H:i')) {
                $schedule['outwardTime'] = "multiple";
            }
        }
        if ($criteria->isWedCheck() && !is_null($criteria->getWedTime()) && $schedule['outwardTime'] !== "multiple") {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getWedTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getWedTime()->format('H:i')) {
                $schedule['outwardTime'] = "multiple";
            }
        }
        if ($criteria->isThuCheck() && !is_null($criteria->getThuTime()) && $schedule['outwardTime'] !== "multiple") {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getThuTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getThuTime()->format('H:i')) {
                $schedule['outwardTime'] = "multiple";
            }
        }
        if ($criteria->isFriCheck() && !is_null($criteria->getFriTime()) && $schedule['outwardTime'] !== "multiple") {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getFriTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getFriTime()->format('H:i')) {
                $schedule['outwardTime'] = "multiple";
            }
        }
        if ($criteria->isSatCheck() && !is_null($criteria->getSatTime()) && $schedule['outwardTime'] !== "multiple") {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getSatTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getSatTime()->format('H:i')) {
                $schedule['outwardTime'] = "multiple";
            }
        }
        if ($criteria->isSunCheck() && !is_null($criteria->getSunTime()) && $schedule['outwardTime'] !== "multiple") {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getSunTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getSunTime()->format('H:i')) {
                $schedule['outwardTime'] = "multiple";
            }
        }
        $schedule['returnTime'] = null;
        if ($returnCriteria->isMonCheck() && !is_null($returnCriteria->getMonTime())) {
            $schedule['returnTime'] = $returnCriteria->getMonTime()->format('H:i');
        }
        if ($returnCriteria->isTueCheck() && !is_null($returnCriteria->getTueTime())) {
            if (is_null($schedule['returnTime'])) {
                $schedule['returnTime'] = $returnCriteria->getTueTime()->format('H:i');
            } elseif ($schedule['returnTime'] !== $returnCriteria->getTueTime()->format('H:i')) {
                $schedule['returnTime'] = "multiple";
            }
        }
        if ($returnCriteria->isWedCheck() && !is_null($returnCriteria->getWedTime()) && $schedule['returnTime'] !== "multiple") {
            if (is_null($schedule['returnTime'])) {
                $schedule['returnTime'] = $returnCriteria->getWedTime()->format('H:i');
            } elseif ($schedule['returnTime'] != $returnCriteria->getWedTime()->format('H:i')) {
                $schedule['returnTime'] = "multiple";
            }
        }
        if ($returnCriteria->isThuCheck() && !is_null($returnCriteria->getThuTime()) && $schedule['returnTime'] !== "multiple") {
            if (is_null($schedule['returnTime'])) {
                $schedule['returnTime'] = $returnCriteria->getThuTime()->format('H:i');
            } elseif ($schedule['returnTime'] != $returnCriteria->getThuTime()->format('H:i')) {
                $schedule['returnTime'] = "multiple";
            }
        }
        if ($returnCriteria->isFriCheck() && !is_null($returnCriteria->getFriTime()) && $schedule['returnTime'] !== "multiple") {
            if (is_null($schedule['returnTime'])) {
                $schedule['returnTime'] = $returnCriteria->getFriTime()->format('H:i');
            } elseif ($schedule['returnTime'] != $returnCriteria->getFriTime()->format('H:i')) {
                $schedule['returnTime'] = "multiple";
            }
        }
        if ($returnCriteria->isSatCheck() && !is_null($returnCriteria->getSatTime()) && $schedule['returnTime'] !== "multiple") {
            if (is_null($schedule['returnTime'])) {
                $schedule['returnTime'] = $returnCriteria->getSatTime()->format('H:i');
            } elseif ($schedule['returnTime'] != $returnCriteria->getSatTime()->format('H:i')) {
                $schedule['returnTime'] = "multiple";
            }
        }
        if ($returnCriteria->isSunCheck() && !is_null($returnCriteria->getSunTime()) && $schedule['returnTime'] !== "multiple") {
            if (is_null($schedule['returnTime'])) {
                $schedule['returnTime'] = $returnCriteria->getSunTime()->format('H:i');
            } elseif ($schedule['returnTime'] != $returnCriteria->getsunTime()->format('H:i')) {
                $schedule['returnTime'] = "multiple";
            }
        }
        return $schedule;
    }

    /**
     * Get the driver details for a given user and ask
     *
     * @param User $user    The user
     * @param Ask $ask      The ask
     * @return array        The driver details
     */
    private function getDriverDetailsForUserAndAsk(User $user, Ask $ask)
    {
        $waypoints = [];
        $pickUpDuration = 0;
        $dropOffDuration = 0;
        $pickUpPosition = 9999;
        $dropOffPosition = 0;
        foreach ($ask->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if ($waypoint->getRole() == Waypoint::ROLE_DRIVER) {
                $waypoints[] = [
                    'position' => $waypoint->getPosition(),
                    'destination' => $waypoint->isDestination(),
                    'houseNumber' => $waypoint->getAddress()->getHouseNumber(),
                    'street' => $waypoint->getAddress()->getStreet(),
                    'streetAddress' => $waypoint->getAddress()->getStreetAddress(),
                    'postalCode' => $waypoint->getAddress()->getPostalCode(),
                    'addressLocality' => $waypoint->getAddress()->getAddressLocality(),
                    'region' => $waypoint->getAddress()->getRegion(),
                    'addressCountry' => $waypoint->getAddress()->getAddressCountry(),
                ];
            } else {
                if ($waypoint->getPosition()<$pickUpPosition) {
                    $pickUpPosition = $waypoint->getPosition();
                    $pickUpDuration = $waypoint->getDuration();
                }
                if ($waypoint->getPosition()>$dropOffPosition) {
                    $dropOffPosition = $waypoint->getPosition();
                    $dropOffDuration = $waypoint->getDuration();
                }
            }
        }

        $driver = [
            'givenName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getGivenName() : $ask->getUser()->getGivenName(),
            'shortFamilyName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getShortFamilyName() : $ask->getUser()->getShortFamilyName(),
            'waypoints' => $waypoints
        ];

        // date and time
        /**
         * @var DateTime $startDate
         */
        $startDate = $ask->getCriteria()->getFromDate();
        $schedule = [];
        switch ($ask->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                $startDate->setTime(
                    $ask->getCriteria()->getFromTime()->format('H'),
                    $ask->getCriteria()->getFromTime()->format('i')
                );
                $endDate = clone $startDate;
                $startDate->modify('+' . $pickUpDuration . ' second');
                $endDate->modify('+' . $dropOffDuration . ' second');
                $driver['pickUpTime'] = $startDate->format('H:i');
                $driver['dropOffTime'] = $endDate->format('H:i');
                break;
            case Criteria::FREQUENCY_REGULAR:
                $schedule['mon']['check'] = $schedule['tue']['check'] = $schedule['wed']['check'] = $schedule['thu']['check'] = $schedule['fri']['check'] = $schedule['sat']['check'] = $schedule['sun']['check'] = false;
                $schedule['mon']['pickUpTime'] = $schedule['tue']['pickUpTime'] = $schedule['wed']['pickUpTime'] = $schedule['thu']['pickUpTime'] = $schedule['fri']['pickUpTime'] = $schedule['sat']['pickUpTime'] = $schedule['sun']['pickUpTime'] = null;
                $schedule['mon']['dropOffTime'] = $schedule['tue']['dropOffTime'] = $schedule['wed']['dropOffTime'] = $schedule['thu']['dropOffTime'] = $schedule['fri']['dropOffTime'] = $schedule['sat']['dropOffTime'] = $schedule['sun']['dropOffTime'] = null;
                if ($ask->getCriteria()->isMonCheck()) {
                    $schedule['mon']['check'] = true;
                    $schedule['mon']['pickUpTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['mon']['pickUpTime'] = $schedule['mon']['pickUpTime']->format('H:i');
                    $schedule['mon']['dropOffTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['mon']['dropOffTime'] = $schedule['mon']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isTueCheck()) {
                    $schedule['tue']['check'] = true;
                    $schedule['tue']['pickUpTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['tue']['pickUpTime'] = $schedule['tue']['pickUpTime']->format('H:i');
                    $schedule['tue']['dropOffTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['tue']['dropOffTime'] = $schedule['tue']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isWedCheck()) {
                    $schedule['wed']['check'] = true;
                    $schedule['wed']['pickUpTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['wed']['pickUpTime'] = $schedule['wed']['pickUpTime']->format('H:i');
                    $schedule['wed']['dropOffTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['wed']['dropOffTime'] = $schedule['wed']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isThuCheck()) {
                    $schedule['thu']['check'] = true;
                    $schedule['thu']['pickUpTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['thu']['pickUpTime'] = $schedule['thu']['pickUpTime']->format('H:i');
                    $schedule['thu']['dropOffTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['thu']['dropOffTime'] = $schedule['thu']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isFriCheck()) {
                    $schedule['fri']['check'] = true;
                    $schedule['fri']['pickUpTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['fri']['pickUpTime'] = $schedule['fri']['pickUpTime']->format('H:i');
                    $schedule['fri']['dropOffTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['fri']['dropOffTime'] = $schedule['fri']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isSatCheck()) {
                    $schedule['sat']['check'] = true;
                    $schedule['sat']['pickUpTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['sat']['pickUpTime'] = $schedule['sat']['pickUpTime']->format('H:i');
                    $schedule['sat']['dropOffTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['sat']['dropOffTime'] = $schedule['sat']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isSunCheck()) {
                    $schedule['sun']['check'] = true;
                    $schedule['sun']['pickUpTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['sun']['pickUpTime'] = $schedule['sun']['pickUpTime']->format('H:i');
                    $schedule['sun']['dropOffTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['sun']['dropOffTime'] = $schedule['sun']['dropOffTime']->format('H:i');
                }
                $driver['schedule'] = $schedule;
                break;
        }
        return $driver;
    }

    /**
     * Get the passenger details for a given user and ask
     *
     * @param User $user    The user
     * @param Ask $ask      The ask
     * @return array        The passenger details
     */
    private function getPassengerDetailsForUserAndAsk(User $user, Ask $ask)
    {
        $waypoints = [];
        $pickUpDuration = 0;
        $dropOffDuration = 0;
        $pickUpPosition = 9999;
        $dropOffPosition = 0;
        foreach ($ask->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if ($waypoint->getRole() == Waypoint::ROLE_PASSENGER) {
                $waypoints[] = [
                    'position' => $waypoint->getPosition(),
                    'destination' => $waypoint->isDestination(),
                    'houseNumber' => $waypoint->getAddress()->getHouseNumber(),
                    'street' => $waypoint->getAddress()->getStreet(),
                    'streetAddress' => $waypoint->getAddress()->getStreetAddress(),
                    'postalCode' => $waypoint->getAddress()->getPostalCode(),
                    'addressLocality' => $waypoint->getAddress()->getAddressLocality(),
                    'region' => $waypoint->getAddress()->getRegion(),
                    'addressCountry' => $waypoint->getAddress()->getAddressCountry(),
                ];
            } else {
                if ($waypoint->getPosition()<$pickUpPosition) {
                    $pickUpPosition = $waypoint->getPosition();
                    $pickUpDuration = $waypoint->getDuration();
                }
                if ($waypoint->getPosition()>$dropOffPosition) {
                    $dropOffPosition = $waypoint->getPosition();
                    $dropOffDuration = $waypoint->getDuration();
                }
            }
        }
        $passenger = [
            'givenName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getGivenName() : $ask->getUser()->getGivenName(),
            'shortFamilyName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getShortFamilyName() : $ask->getUser()->getShortFamilyName(),
            'waypoints' => $waypoints
        ];

        // date and time
        /**
         * @var DateTime $startDate
         */
        $startDate = $ask->getCriteria()->getFromDate();
        $schedule = [];
        switch ($ask->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                $startDate->setTime(
                    $ask->getCriteria()->getFromTime()->format('H'),
                    $ask->getCriteria()->getFromTime()->format('i')
                );
                $endDate = clone $startDate;
                $startDate->modify('+' . $pickUpDuration . ' second');
                $endDate->modify('+' . $dropOffDuration . ' second');
                $passenger['pickUpTime'] = $startDate->format('H:i');
                $passenger['dropOffTime'] = $endDate->format('H:i');
                break;
            case Criteria::FREQUENCY_REGULAR:
                $schedule['mon']['check'] = $schedule['tue']['check'] = $schedule['wed']['check'] = $schedule['thu']['check'] = $schedule['fri']['check'] = $schedule['sat']['check'] = $schedule['sun']['check'] = false;
                $schedule['mon']['pickUpTime'] = $schedule['tue']['pickUpTime'] = $schedule['wed']['pickUpTime'] = $schedule['thu']['pickUpTime'] = $schedule['fri']['pickUpTime'] = $schedule['sat']['pickUpTime'] = $schedule['sun']['pickUpTime'] = null;
                $schedule['mon']['dropOffTime'] = $schedule['tue']['dropOffTime'] = $schedule['wed']['dropOffTime'] = $schedule['thu']['dropOffTime'] = $schedule['fri']['dropOffTime'] = $schedule['sat']['dropOffTime'] = $schedule['sun']['dropOffTime'] = null;
                if ($ask->getCriteria()->isMonCheck()) {
                    $schedule['mon']['check'] = true;
                    $schedule['mon']['pickUpTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['mon']['pickUpTime'] = $schedule['mon']['pickUpTime']->format('H:i');
                    $schedule['mon']['dropOffTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['mon']['dropOffTime'] = $schedule['mon']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isTueCheck()) {
                    $schedule['tue']['check'] = true;
                    $schedule['tue']['pickUpTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['tue']['pickUpTime'] = $schedule['tue']['pickUpTime']->format('H:i');
                    $schedule['tue']['dropOffTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['tue']['dropOffTime'] = $schedule['tue']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isWedCheck()) {
                    $schedule['wed']['check'] = true;
                    $schedule['wed']['pickUpTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['wed']['pickUpTime'] = $schedule['wed']['pickUpTime']->format('H:i');
                    $schedule['wed']['dropOffTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['wed']['dropOffTime'] = $schedule['wed']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isThuCheck()) {
                    $schedule['thu']['check'] = true;
                    $schedule['thu']['pickUpTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['thu']['pickUpTime'] = $schedule['thu']['pickUpTime']->format('H:i');
                    $schedule['thu']['dropOffTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['thu']['dropOffTime'] = $schedule['thu']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isFriCheck()) {
                    $schedule['fri']['check'] = true;
                    $schedule['fri']['pickUpTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['fri']['pickUpTime'] = $schedule['fri']['pickUpTime']->format('H:i');
                    $schedule['fri']['dropOffTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['fri']['dropOffTime'] = $schedule['fri']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isSatCheck()) {
                    $schedule['sat']['check'] = true;
                    $schedule['sat']['pickUpTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['sat']['pickUpTime'] = $schedule['sat']['pickUpTime']->format('H:i');
                    $schedule['sat']['dropOffTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['sat']['dropOffTime'] = $schedule['sat']['dropOffTime']->format('H:i');
                }
                if ($ask->getCriteria()->isSunCheck()) {
                    $schedule['sun']['check'] = true;
                    $schedule['sun']['pickUpTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['pickUpTime']->modify('+' . $pickUpDuration . ' second');
                    $schedule['sun']['pickUpTime'] = $schedule['sun']['pickUpTime']->format('H:i');
                    $schedule['sun']['dropOffTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['dropOffTime']->modify('+' . $dropOffDuration . ' second');
                    $schedule['sun']['dropOffTime'] = $schedule['sun']['dropOffTime']->format('H:i');
                }
                $passenger['schedule'] = $schedule;
                break;
        }

        return $passenger;
    }
}
