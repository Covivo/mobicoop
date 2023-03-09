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
 */

namespace App\Carpool\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\MyAdCommunity;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\MyAd;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use App\User\Service\ReviewManager;

/**
 * MyAd manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class MyAdManager
{
    private $proposalRepository;
    private $carpoolItemRepository;
    private $reviewManager;
    private $paymentActive;
    private $paymentActiveDate;
    private $proofManager;
    private $matchingRepository;

    /**
     * Constructor.
     *
     * @param ProposalRepository    $proposalRepository    The proposal repository
     * @param CarpoolItemRepository $carpoolItemRepository The carpool item repository
     * @param string                $paymentActive         The date of the payment activation, or false (as string!)
     */
    public function __construct(
        ProposalRepository $proposalRepository,
        CarpoolItemRepository $carpoolItemRepository,
        ReviewManager $reviewManager,
        string $paymentActive,
        ProofManager $proofManager,
        MatchingRepository $matchingRepository
    ) {
        $this->proposalRepository = $proposalRepository;
        $this->carpoolItemRepository = $carpoolItemRepository;
        $this->reviewManager = $reviewManager;
        $this->paymentActive = false;
        $this->proofManager = $proofManager;
        $this->matchingRepository = $matchingRepository;
        if ($this->paymentActiveDate = \DateTime::createFromFormat('Y-m-d', $paymentActive)) {
            $this->paymentActiveDate->setTime(0, 0);
            $this->paymentActive = true;
        }
    }

    /**
     * Get MyAds for a given user.
     *
     * @param User $user The user
     *
     * @return array The MyAds found
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
     * Create a MyAd object from a Proposal object.
     *
     * @param Proposal $proposal The proposal
     *
     * @return MyAd The resulting MyAd
     */
    private function createMyAdFromProposal(Proposal $proposal)
    {
        $myAd = new MyAd();
        $myAd->setId($proposal->getId());
        $myAd->setPublished(!$proposal->isPrivate());
        $myAd->setPaused($proposal->isPaused());
        $myAd->setFrequency($proposal->getCriteria()->getFrequency());
        $myAd->setRoleDriver((true === $proposal->getCriteria()->isDriver()) ? true : false);
        $myAd->setRolePassenger((true === $proposal->getCriteria()->isPassenger()) ? true : false);
        $myAd->setSolidaryExclusive($proposal->getCriteria()->isSolidaryExclusive() ? true : false);

        if (is_array($proposal->getCommunities()) && count($proposal->getCommunities()) > 0) {
            foreach ($proposal->getCommunities() as $community) {
                $myAdCommunity = new MyAdCommunity();
                $myAdCommunity->setId($community->getId());
                $myAdCommunity->setName($community->getName());
                if (count($community->getImages()) > 0) {
                    $versions = $community->getImages()[0]->getVersions();
                    $myAdCommunity->setImage(isset($versions['square_100']) ? $versions['square_100'] : $versions['original']);
                }
                $myAd->addCommunity($myAdCommunity);
            }
        }

        switch ($proposal->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                /**
                 * @var \DateTime $fromDate
                 */
                $fromDate = $proposal->getCriteria()->getFromDate();
                if (!is_null($proposal->getCriteria()->getFromTime())) {
                    $fromDate->setTime(
                        $proposal->getCriteria()->getFromTime()->format('H'),
                        $proposal->getCriteria()->getFromTime()->format('i')
                    );
                }

                $myAd->setOutwardDate($fromDate->format('Y-m-d'));
                $myAd->setOutwardTime($fromDate->format('H:i'));

                if (Proposal::TYPE_ONE_WAY == $proposal->getType()) {
                    $myAd->setType(MyAd::TYPE_ONE_WAY);
                } elseif (Proposal::TYPE_OUTWARD == $proposal->getType()) {
                    $myAd->setType(MyAd::TYPE_OUTWARD);
                } elseif (Proposal::TYPE_RETURN == $proposal->getType()) {
                    $myAd->setType(MyAd::TYPE_RETURN);
                }

                break;

            case Criteria::FREQUENCY_REGULAR:
                $myAd->setFromDate($proposal->getCriteria()->getFromDate()->format('Y-m-d'));
                $myAd->setToDate($proposal->getCriteria()->getToDate()->format('Y-m-d'));
                $myAd->setSchedule($this->getScheduleFromCriteria($proposal->getCriteria()));
                if (Proposal::TYPE_ONE_WAY == $proposal->getType()) {
                    $myAd->setType(MyAd::TYPE_ONE_WAY);
                } elseif (Proposal::TYPE_OUTWARD == $proposal->getType()) {
                    $myAd->setType(MyAd::TYPE_OUTWARD);
                } elseif (Proposal::TYPE_RETURN == $proposal->getType()) {
                    $myAd->setType(MyAd::TYPE_RETURN);
                }

                break;
        }

        // waypoints
        $waypoints = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if (!$waypoint->isFloating()) {
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
                    'longitude' => $waypoint->getAddress()->getLongitude(),
                    'latitude' => $waypoint->getAddress()->getLatitude(),
                    'addressId' => $waypoint->getAddress()->getId(),
                    'name' => $waypoint->getAddress()->getName(),
                ];
            }
        }
        $myAd->setWaypoints($waypoints);

        // the price is the computed rounded price, as a driver first then as a passenger
        $myAd->setPrice($myAd->hasRoleDriver() ? $proposal->getCriteria()->getDriverComputedRoundedPrice() : $proposal->getCriteria()->getPassengerComputedRoundedPrice());
        $myAd->setPriceKm($proposal->getCriteria()->getPriceKm());
        // the number of seats is as a driver first then as a passenger
        $myAd->setSeats($myAd->hasRoleDriver() ? $proposal->getCriteria()->getSeatsDriver() : $proposal->getCriteria()->getSeatsPassenger());
        $myAd->setComment($proposal->getComment());

        // init the payment status
        $myAd->setPaymentStatus(MyAd::PAYMENT_STATUS_NULL);

        // are there potential carpoolers ? and/or (accepted) asks ?
        $carpoolers = [];
        $driver = [];
        $passengers = [];
        $myAd->setAsks(false);

        $today = new \DateTime('now');
        foreach ($this->matchingRepository->getProposalMatchingAsOffersWithBothUsers($proposal) as $matchingOffer) {
            // the user is passenger
            /**
             * @var Matching $matchingOffer
             */
            // we exclude private proposals and expired matchings for the carpooler count
            // We need them though to treat former ask without sending another request
            if (!$matchingOffer->getProposalOffer()->isPrivate() && $matchingOffer->getCriteria()->getFromDate()->format('Y-m-d') >= $today->format('Y-m-d')) {
                $carpoolers[] = $matchingOffer->getProposalOffer()->getUser()->getId();
            }
            // check for asks (driver)
            foreach ($matchingOffer->getAsks() as $ask) {
                /**
                 * @var Ask $ask
                 */
                if (
                    Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER == $ask->getStatus()
                    && ($ask->getUser()->getId() == $proposal->getUser()->getId() || $ask->getUserRelated()->getId() == $proposal->getUser()->getId())
                ) {
                    // accepted ask
                    $myAd->setAsks(true);
                    $driver = $this->getDriverDetailsForUserAndAsk($proposal->getUser(), $ask);
                    $driver['canReceiveReview'] = $this->reviewManager->canReceiveReview(
                        $proposal->getUser(),
                        $ask->getUser()->getId() == $proposal->getUser()->getId() ? $ask->getUserRelated() : $ask->getUser()
                    );

                    if (CRITERIA::FREQUENCY_PUNCTUAL === $ask->getCriteria()->getFrequency()) {
                        foreach ($ask->getCarpoolProofs() as $carpoolProof) {
                            if ($carpoolProof->getDriver()->getId() === $driver['id']) {
                                $driver['classicProof'] = $this->proofManager->getClassicProof($carpoolProof->getId());
                            }
                        }
                    } else {
                        $today = new \DateTime('Today');
                        foreach ($ask->getCarpoolProofs() as $carpoolProof) {
                            $date = $carpoolProof->getStartDriverDate();
                            $date->setTime(0, 0, 0);
                            if (($carpoolProof->getDriver()->getId() === $driver['id']) && $today == $date) {
                                $driver['classicProof'] = $this->proofManager->getClassicProof($carpoolProof->getId());
                            }
                        }
                    }
                    // the overall payment status is the driver payment status
                    $myAd->setPaymentStatus($driver['payment']['status']);
                    // theorically, only one driver, if we found it we exit the loop
                    break;
                }
                if (
                    Ask::STATUS_INITIATED == $ask->getStatus()
                    || Ask::STATUS_PENDING_AS_DRIVER == $ask->getStatus()
                    || Ask::STATUS_PENDING_AS_PASSENGER == $ask->getStatus()
                ) {
                    // pending ask
                    $myAd->setAsks(true);
                }
            }
        }

        foreach ($this->matchingRepository->getProposalMatchingAsRequestsWithBothUsers($proposal) as $matchingRequest) {
            // the user is driver
            /**
             * @var Matching $matchingRequest
             */
            // we exclude private proposals for the carpooler count, as well as solidaries and expired matching
            // We need them though to treat former ask without sending another request
            if (
                !$matchingRequest->getProposalRequest()->isPrivate()
                && !$matchingRequest->getProposalRequest()->getSolidary()
                && !in_array($matchingRequest->getProposalRequest()->getUser()->getId(), $carpoolers)
                && $matchingRequest->getCriteria()->getFromDate()->format('Y-m-d') >= $today->format('Y-m-d')
            ) {
                $carpoolers[] = $matchingRequest->getProposalRequest()->getUser()->getId();
            }
            // check for asks (passengers)
            foreach ($matchingRequest->getAsks() as $ask) {
                /**
                 * @var Ask $ask
                 */
                if (
                    Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER == $ask->getStatus()
                    && ($ask->getUser()->getId() == $proposal->getUser()->getId() || $ask->getUserRelated()->getId() == $proposal->getUser()->getId())
                ) {
                    // accepted ask
                    $myAd->setAsks(true);
                    $passenger = $this->getPassengerDetailsForUserAndAsk($proposal->getUser(), $ask);
                    $passenger['canReceiveReview'] = $this->reviewManager->canReceiveReview(
                        $proposal->getUser(),
                        $ask->getUser()->getId() == $proposal->getUser()->getId() ? $ask->getUserRelated() : $ask->getUser()
                    );
                    if (MyAd::PAYMENT_STATUS_TODO == $passenger['payment']['status']) {
                        $myAd->setPaymentStatus(MyAd::PAYMENT_STATUS_TODO);
                    } elseif (MyAd::PAYMENT_STATUS_NULL == $myAd->getPaymentStatus()) {
                        $myAd->setPaymentStatus($passenger['payment']['status']);
                    }
                    if (CRITERIA::FREQUENCY_PUNCTUAL === $ask->getCriteria()->getFrequency()) {
                        foreach ($ask->getCarpoolProofs() as $carpoolProof) {
                            if ($carpoolProof->getPassenger()->getId() === $passenger['id']) {
                                $passenger['classicProof'] = $this->proofManager->getClassicProof($carpoolProof->getId());
                            }
                        }
                    } else {
                        $today = new \DateTime('Today');
                        foreach ($ask->getCarpoolProofs() as $carpoolProof) {
                            $date = $carpoolProof->getStartDriverDate();
                            $date->setTime(0, 0, 0);
                            if (($carpoolProof->getPassenger()->getId() === $passenger['id']) && $today == $date) {
                                $passenger['classicProof'] = $this->proofManager->getClassicProof($carpoolProof->getId());
                            }
                        }
                    }
                    $passengers[] = $passenger;
                } elseif (
                    Ask::STATUS_INITIATED == $ask->getStatus()
                    || Ask::STATUS_PENDING_AS_DRIVER == $ask->getStatus()
                    || Ask::STATUS_PENDING_AS_PASSENGER == $ask->getStatus()
                ) {
                    // pending ask
                    $myAd->setAsks(true);
                }
            }
        }
        $myAd->setCarpoolers(count($carpoolers));

        $myAd->setDriver($driver);
        $myAd->setPassengers($passengers);

        return $myAd;
    }

    /**
     * Create schedule from Criteria.
     *
     * @param Criteria      $criteria       The outward Criteria
     * @param null|Criteria $returnCriteria The return Criteria
     *
     * @return array The schedule
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
                $schedule['outwardTime'] = 'multiple';
            }
        }
        if ($criteria->isWedCheck() && !is_null($criteria->getWedTime()) && 'multiple' !== $schedule['outwardTime']) {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getWedTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getWedTime()->format('H:i')) {
                $schedule['outwardTime'] = 'multiple';
            }
        }
        if ($criteria->isThuCheck() && !is_null($criteria->getThuTime()) && 'multiple' !== $schedule['outwardTime']) {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getThuTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getThuTime()->format('H:i')) {
                $schedule['outwardTime'] = 'multiple';
            }
        }
        if ($criteria->isFriCheck() && !is_null($criteria->getFriTime()) && 'multiple' !== $schedule['outwardTime']) {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getFriTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getFriTime()->format('H:i')) {
                $schedule['outwardTime'] = 'multiple';
            }
        }
        if ($criteria->isSatCheck() && !is_null($criteria->getSatTime()) && 'multiple' !== $schedule['outwardTime']) {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getSatTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getSatTime()->format('H:i')) {
                $schedule['outwardTime'] = 'multiple';
            }
        }
        if ($criteria->isSunCheck() && !is_null($criteria->getSunTime()) && 'multiple' !== $schedule['outwardTime']) {
            if (is_null($schedule['outwardTime'])) {
                $schedule['outwardTime'] = $criteria->getSunTime()->format('H:i');
            } elseif ($schedule['outwardTime'] != $criteria->getSunTime()->format('H:i')) {
                $schedule['outwardTime'] = 'multiple';
            }
        }
        $schedule['returnTime'] = null;
        if (!is_null($returnCriteria)) {
            if ($returnCriteria->isMonCheck() && !is_null($returnCriteria->getMonTime())) {
                $schedule['returnTime'] = $returnCriteria->getMonTime()->format('H:i');
            }
            if ($returnCriteria->isTueCheck() && !is_null($returnCriteria->getTueTime())) {
                if (is_null($schedule['returnTime'])) {
                    $schedule['returnTime'] = $returnCriteria->getTueTime()->format('H:i');
                } elseif ($schedule['returnTime'] !== $returnCriteria->getTueTime()->format('H:i')) {
                    $schedule['returnTime'] = 'multiple';
                }
            }
            if ($returnCriteria->isWedCheck() && !is_null($returnCriteria->getWedTime()) && 'multiple' !== $schedule['returnTime']) {
                if (is_null($schedule['returnTime'])) {
                    $schedule['returnTime'] = $returnCriteria->getWedTime()->format('H:i');
                } elseif ($schedule['returnTime'] != $returnCriteria->getWedTime()->format('H:i')) {
                    $schedule['returnTime'] = 'multiple';
                }
            }
            if ($returnCriteria->isThuCheck() && !is_null($returnCriteria->getThuTime()) && 'multiple' !== $schedule['returnTime']) {
                if (is_null($schedule['returnTime'])) {
                    $schedule['returnTime'] = $returnCriteria->getThuTime()->format('H:i');
                } elseif ($schedule['returnTime'] != $returnCriteria->getThuTime()->format('H:i')) {
                    $schedule['returnTime'] = 'multiple';
                }
            }
            if ($returnCriteria->isFriCheck() && !is_null($returnCriteria->getFriTime()) && 'multiple' !== $schedule['returnTime']) {
                if (is_null($schedule['returnTime'])) {
                    $schedule['returnTime'] = $returnCriteria->getFriTime()->format('H:i');
                } elseif ($schedule['returnTime'] != $returnCriteria->getFriTime()->format('H:i')) {
                    $schedule['returnTime'] = 'multiple';
                }
            }
            if ($returnCriteria->isSatCheck() && !is_null($returnCriteria->getSatTime()) && 'multiple' !== $schedule['returnTime']) {
                if (is_null($schedule['returnTime'])) {
                    $schedule['returnTime'] = $returnCriteria->getSatTime()->format('H:i');
                } elseif ($schedule['returnTime'] != $returnCriteria->getSatTime()->format('H:i')) {
                    $schedule['returnTime'] = 'multiple';
                }
            }
            if ($returnCriteria->isSunCheck() && !is_null($returnCriteria->getSunTime()) && 'multiple' !== $schedule['returnTime']) {
                if (is_null($schedule['returnTime'])) {
                    $schedule['returnTime'] = $returnCriteria->getSunTime()->format('H:i');
                } elseif ($schedule['returnTime'] != $returnCriteria->getsunTime()->format('H:i')) {
                    $schedule['returnTime'] = 'multiple';
                }
            }
        }

        return $schedule;
    }

    /**
     * Get the driver details for a given user and ask.
     *
     * @param User $user The user
     * @param Ask  $ask  The ask
     *
     * @return array The driver details
     */
    private function getDriverDetailsForUserAndAsk(User $user, Ask $ask)
    {
        $waypoints = [];
        $endDuration = 0;
        $pickUpDuration = 0;
        $dropOffDuration = 0;
        $pickUpPosition = 9999;
        $dropOffPosition = 0;
        foreach ($ask->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if (Waypoint::ROLE_DRIVER == $waypoint->getRole()) {
                $waypoints[] = [
                    'origin' => 0 == $waypoint->getPosition(),
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
                if ($waypoint->isDestination()) {
                    $endDuration = $waypoint->getDuration();
                }
            } else {
                if ($waypoint->getPosition() < $pickUpPosition) {
                    $pickUpPosition = $waypoint->getPosition();
                    $pickUpDuration = $waypoint->getDuration();
                }
                if ($waypoint->getPosition() > $dropOffPosition) {
                    $dropOffPosition = $waypoint->getPosition();
                    $dropOffDuration = $waypoint->getDuration();
                }
            }
        }

        $driver = [
            'id' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getId() : $ask->getUser()->getId(),
            'givenName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getGivenName() : $ask->getUser()->getGivenName(),
            'shortFamilyName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getShortFamilyName() : $ask->getUser()->getShortFamilyName(),
            'birthYear' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getBirthYear() : $ask->getUser()->getBirthYear(),
            'age' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getAge() : $ask->getUser()->getAge(),
            'telephone' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getTelephone() : $ask->getUser()->getTelephone(),
            'avatars' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getAvatars() : $ask->getUser()->getAvatars(),
            'waypoints' => $waypoints,
            'price' => $ask->getCriteria()->getPassengerComputedRoundedPrice(),
            'askId' => $ask->getId(),
            'askFrequency' => $ask->getCriteria()->getFrequency(),
        ];

        // date and time
        $schedule = [];

        switch ($ask->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                /**
                 * @var \DateTime $startDate
                 */
                $startDate = $ask->getCriteria()->getFromDate();
                if (!is_null($ask->getCriteria()->getFromTime())) {
                    $startDate->setTime(
                        $ask->getCriteria()->getFromTime()->format('H'),
                        $ask->getCriteria()->getFromTime()->format('i')
                    );
                }
                $pickupDate = clone $startDate;
                $dropOffDate = clone $startDate;
                $endDate = clone $startDate;
                $pickupDate->modify('+'.$pickUpDuration.' second');
                $dropOffDate->modify('+'.$dropOffDuration.' second');
                $endDate->modify('+'.$endDuration.' second');
                $driver['fromDate'] = $ask->getCriteria()->getFromDate()->format('Y-m-d');
                $driver['startTime'] = $startDate->format('H:i');
                $driver['pickUpTime'] = $pickupDate->format('H:i');
                $driver['dropOffTime'] = $dropOffDate->format('H:i');
                $driver['endTime'] = $endDate->format('H:i');

                break;

            case Criteria::FREQUENCY_REGULAR:
                $driver['fromDate'] = $ask->getCriteria()->getFromDate()->format('Y-m-d');
                $driver['toDate'] = $ask->getCriteria()->getToDate()->format('Y-m-d');
                $schedule['pickUpTime'] = null;
                $schedule['mon']['check'] = $schedule['tue']['check'] = $schedule['wed']['check'] = $schedule['thu']['check'] = $schedule['fri']['check'] = $schedule['sat']['check'] = $schedule['sun']['check'] = false;
                $schedule['mon']['startTime'] = $schedule['tue']['startTime'] = $schedule['wed']['startTime'] = $schedule['thu']['startTime'] = $schedule['fri']['startTime'] = $schedule['sat']['startTime'] = $schedule['sun']['startTime'] = null;
                $schedule['mon']['pickUpTime'] = $schedule['tue']['pickUpTime'] = $schedule['wed']['pickUpTime'] = $schedule['thu']['pickUpTime'] = $schedule['fri']['pickUpTime'] = $schedule['sat']['pickUpTime'] = $schedule['sun']['pickUpTime'] = null;
                $schedule['mon']['dropOffTime'] = $schedule['tue']['dropOffTime'] = $schedule['wed']['dropOffTime'] = $schedule['thu']['dropOffTime'] = $schedule['fri']['dropOffTime'] = $schedule['sat']['dropOffTime'] = $schedule['sun']['dropOffTime'] = null;
                $schedule['mon']['endTime'] = $schedule['tue']['endTime'] = $schedule['wed']['endTime'] = $schedule['thu']['endTime'] = $schedule['fri']['endTime'] = $schedule['sat']['endTime'] = $schedule['sun']['endTime'] = null;
                if ($ask->getCriteria()->isMonCheck() && $ask->getCriteria()->getMonTime()) {
                    $schedule['mon']['check'] = true;
                    $schedule['mon']['startTime'] = $ask->getCriteria()->getMonTime()->format('H:i');
                    $schedule['mon']['pickUpTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['mon']['pickUpTime'] = $schedule['mon']['pickUpTime']->format('H:i');
                    $schedule['mon']['dropOffTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['mon']['dropOffTime'] = $schedule['mon']['dropOffTime']->format('H:i');
                    $schedule['mon']['endTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['mon']['endTime'] = $schedule['mon']['endTime']->format('H:i');
                    $schedule['pickUpTime'] = $schedule['mon']['pickUpTime'];
                }
                if ($ask->getCriteria()->isTueCheck() && $ask->getCriteria()->getTueTime()) {
                    $schedule['tue']['check'] = true;
                    $schedule['tue']['startTime'] = $ask->getCriteria()->getTueTime()->format('H:i');
                    $schedule['tue']['pickUpTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['tue']['pickUpTime'] = $schedule['tue']['pickUpTime']->format('H:i');
                    $schedule['tue']['dropOffTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['tue']['dropOffTime'] = $schedule['tue']['dropOffTime']->format('H:i');
                    $schedule['tue']['endTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['tue']['endTime'] = $schedule['tue']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['tue']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['tue']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isWedCheck() && $ask->getCriteria()->getWedTime()) {
                    $schedule['wed']['check'] = true;
                    $schedule['wed']['startTime'] = $ask->getCriteria()->getWedTime()->format('H:i');
                    $schedule['wed']['pickUpTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['wed']['pickUpTime'] = $schedule['wed']['pickUpTime']->format('H:i');
                    $schedule['wed']['dropOffTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['wed']['dropOffTime'] = $schedule['wed']['dropOffTime']->format('H:i');
                    $schedule['wed']['endTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['wed']['endTime'] = $schedule['wed']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['wed']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['wed']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isThuCheck() && $ask->getCriteria()->getThuTime()) {
                    $schedule['thu']['check'] = true;
                    $schedule['thu']['startTime'] = $ask->getCriteria()->getThuTime()->format('H:i');
                    $schedule['thu']['pickUpTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['thu']['pickUpTime'] = $schedule['thu']['pickUpTime']->format('H:i');
                    $schedule['thu']['dropOffTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['thu']['dropOffTime'] = $schedule['thu']['dropOffTime']->format('H:i');
                    $schedule['thu']['endTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['thu']['endTime'] = $schedule['thu']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['thu']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['thu']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isFriCheck() && $ask->getCriteria()->getFriTime()) {
                    $schedule['fri']['check'] = true;
                    $schedule['fri']['startTime'] = $ask->getCriteria()->getFriTime()->format('H:i');
                    $schedule['fri']['pickUpTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['fri']['pickUpTime'] = $schedule['fri']['pickUpTime']->format('H:i');
                    $schedule['fri']['dropOffTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['fri']['dropOffTime'] = $schedule['fri']['dropOffTime']->format('H:i');
                    $schedule['fri']['endTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['fri']['endTime'] = $schedule['fri']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['fri']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['fri']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isSatCheck() && $ask->getCriteria()->getSatTime()) {
                    $schedule['sat']['check'] = true;
                    $schedule['sat']['startTime'] = $ask->getCriteria()->getSatTime()->format('H:i');
                    $schedule['sat']['pickUpTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['sat']['pickUpTime'] = $schedule['sat']['pickUpTime']->format('H:i');
                    $schedule['sat']['dropOffTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['sat']['dropOffTime'] = $schedule['sat']['dropOffTime']->format('H:i');
                    $schedule['sat']['endTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['sat']['endTime'] = $schedule['sat']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['sat']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['sat']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isSunCheck() && $ask->getCriteria()->getSunTime()) {
                    $schedule['sun']['check'] = true;
                    $schedule['sun']['startTime'] = $ask->getCriteria()->getSunTime()->format('H:i');
                    $schedule['sun']['pickUpTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['sun']['pickUpTime'] = $schedule['sun']['pickUpTime']->format('H:i');
                    $schedule['sun']['dropOffTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['sun']['dropOffTime'] = $schedule['sun']['dropOffTime']->format('H:i');
                    $schedule['sun']['endTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['sun']['endTime'] = $schedule['sun']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['sun']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['sun']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                $driver['schedule'] = $schedule;

                break;
        }
        // return trip ?
        if ($ask->getAskLinked()) {
            $waypoints = [];
            $endDuration = 0;
            $pickUpDuration = 0;
            $dropOffDuration = 0;
            $pickUpPosition = 9999;
            $dropOffPosition = 0;
            foreach ($ask->getAskLinked()->getWaypoints() as $waypoint) {
                /**
                 * @var Waypoint $waypoint
                 */
                if (Waypoint::ROLE_DRIVER == $waypoint->getRole()) {
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
                    if ($waypoint->isDestination()) {
                        $endDuration = $waypoint->getDuration();
                    }
                } else {
                    if ($waypoint->getPosition() < $pickUpPosition) {
                        $pickUpPosition = $waypoint->getPosition();
                        $pickUpDuration = $waypoint->getDuration();
                    }
                    if ($waypoint->getPosition() > $dropOffPosition) {
                        $dropOffPosition = $waypoint->getPosition();
                        $dropOffDuration = $waypoint->getDuration();
                    }
                }
            }
            $driver['returnWaypoints'] = $waypoints;

            switch ($ask->getAskLinked()->getCriteria()->getFrequency()) {
                case Criteria::FREQUENCY_PUNCTUAL:
                    /**
                     * @var \DateTime $startDate
                     */
                    $startDate = $ask->getAskLinked()->getCriteria()->getFromDate();
                    if (!is_null($ask->getAskLinked()->getCriteria()->getFromTime())) {
                        $startDate->setTime(
                            $ask->getAskLinked()->getCriteria()->getFromTime()->format('H'),
                            $ask->getAskLinked()->getCriteria()->getFromTime()->format('i')
                        );
                    }
                    $pickupDate = clone $startDate;
                    $dropOffDate = clone $startDate;
                    $endDate = clone $startDate;
                    $pickupDate->modify('+'.$pickUpDuration.' second');
                    $dropOffDate->modify('+'.$dropOffDuration.' second');
                    $endDate->modify('+'.$endDuration.' second');
                    $driver['returnFromDate'] = $ask->getAskLinked()->getCriteria()->getFromDate()->format('Y-m-d');
                    $driver['returnStartTime'] = $startDate->format('H:i');
                    $driver['returnPickUpTime'] = $pickupDate->format('H:i');
                    $driver['returnDropOffTime'] = $dropOffDate->format('H:i');
                    $driver['returnEndTime'] = $endDate->format('H:i');

                    break;

                case Criteria::FREQUENCY_REGULAR:
                    $driver['returnFromDate'] = $ask->getAskLinked()->getCriteria()->getFromDate()->format('Y-m-d');
                    $driver['returnToDate'] = $ask->getAskLinked()->getCriteria()->getToDate()->format('Y-m-d');
                    $schedule['returnPickUpTime'] = null;
                    $schedule['mon']['returnStartTime'] = $schedule['tue']['returnStartTime'] = $schedule['wed']['returnStartTime'] = $schedule['thu']['returnStartTime'] = $schedule['fri']['returnStartTime'] = $schedule['sat']['returnStartTime'] = $schedule['sun']['returnStartTime'] = null;
                    $schedule['mon']['returnPickUpTime'] = $schedule['tue']['returnPickUpTime'] = $schedule['wed']['returnPickUpTime'] = $schedule['thu']['returnPickUpTime'] = $schedule['fri']['returnPickUpTime'] = $schedule['sat']['returnPickUpTime'] = $schedule['sun']['returnPickUpTime'] = null;
                    $schedule['mon']['returnDropOffTime'] = $schedule['tue']['returnDropOffTime'] = $schedule['wed']['returnDropOffTime'] = $schedule['thu']['returnDropOffTime'] = $schedule['fri']['returnDropOffTime'] = $schedule['sat']['returnDropOffTime'] = $schedule['sun']['returnDropOffTime'] = null;
                    $schedule['mon']['returnEndTime'] = $schedule['tue']['returnEndTime'] = $schedule['wed']['returnEndTime'] = $schedule['thu']['returnEndTime'] = $schedule['fri']['returnEndTime'] = $schedule['sat']['returnEndTime'] = $schedule['sun']['returnEndTime'] = null;
                    if ($ask->getAskLinked()->getCriteria()->isMonCheck() && $ask->getAskLinked()->getCriteria()->getMonTime()) {
                        $schedule['mon']['check'] = true;
                        $schedule['mon']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getMonTime()->format('H:i');
                        $schedule['mon']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getMonTime();
                        $schedule['mon']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['mon']['returnPickUpTime'] = $schedule['mon']['returnPickUpTime']->format('H:i');
                        $schedule['mon']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getMonTime();
                        $schedule['mon']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['mon']['returnDropOffTime'] = $schedule['mon']['returnDropOffTime']->format('H:i');
                        $schedule['mon']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getMonTime();
                        $schedule['mon']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['mon']['returnEndTime'] = $schedule['mon']['returnEndTime']->format('H:i');
                        $schedule['returnPickUpTime'] = $schedule['mon']['returnPickUpTime'];
                    }
                    if ($ask->getAskLinked()->getCriteria()->isTueCheck() && $ask->getAskLinked()->getCriteria()->getTueTime()) {
                        $schedule['tue']['check'] = true;
                        $schedule['tue']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getTueTime()->format('H:i');
                        $schedule['tue']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getTueTime();
                        $schedule['tue']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['tue']['returnPickUpTime'] = $schedule['tue']['returnPickUpTime']->format('H:i');
                        $schedule['tue']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getTueTime();
                        $schedule['tue']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['tue']['returnDropOffTime'] = $schedule['tue']['returnDropOffTime']->format('H:i');
                        $schedule['tue']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getTueTime();
                        $schedule['tue']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['tue']['returnEndTime'] = $schedule['tue']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['tue']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['tue']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isWedCheck() && $ask->getAskLinked()->getCriteria()->getWedTime()) {
                        $schedule['wed']['check'] = true;
                        $schedule['wed']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getWedTime()->format('H:i');
                        $schedule['wed']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getWedTime();
                        $schedule['wed']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['wed']['returnPickUpTime'] = $schedule['wed']['returnPickUpTime']->format('H:i');
                        $schedule['wed']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getWedTime();
                        $schedule['wed']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['wed']['returnDropOffTime'] = $schedule['wed']['returnDropOffTime']->format('H:i');
                        $schedule['wed']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getWedTime();
                        $schedule['wed']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['wed']['returnEndTime'] = $schedule['wed']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['wed']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['wed']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isThuCheck() && $ask->getAskLinked()->getCriteria()->getThuTime()) {
                        $schedule['thu']['check'] = true;
                        $schedule['thu']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getThuTime()->format('H:i');
                        $schedule['thu']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getThuTime();
                        $schedule['thu']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['thu']['returnPickUpTime'] = $schedule['thu']['returnPickUpTime']->format('H:i');
                        $schedule['thu']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getThuTime();
                        $schedule['thu']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['thu']['returnDropOffTime'] = $schedule['thu']['returnDropOffTime']->format('H:i');
                        $schedule['thu']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getThuTime();
                        $schedule['thu']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['thu']['returnEndTime'] = $schedule['thu']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['thu']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['thu']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isFriCheck() && $ask->getAskLinked()->getCriteria()->getFriTime()) {
                        $schedule['fri']['check'] = true;
                        $schedule['fri']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getFriTime()->format('H:i');
                        $schedule['fri']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getFriTime();
                        $schedule['fri']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['fri']['returnPickUpTime'] = $schedule['fri']['returnPickUpTime']->format('H:i');
                        $schedule['fri']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getFriTime();
                        $schedule['fri']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['fri']['returnDropOffTime'] = $schedule['fri']['returnDropOffTime']->format('H:i');
                        $schedule['fri']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getFriTime();
                        $schedule['fri']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['fri']['returnEndTime'] = $schedule['fri']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['fri']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['fri']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isSatCheck() && $ask->getAskLinked()->getCriteria()->getSatTime()) {
                        $schedule['sat']['check'] = true;
                        $schedule['sat']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getSatTime()->format('H:i');
                        $schedule['sat']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getSatTime();
                        $schedule['sat']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['sat']['returnPickUpTime'] = $schedule['sat']['returnPickUpTime']->format('H:i');
                        $schedule['sat']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getSatTime();
                        $schedule['sat']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['sat']['returnDropOffTime'] = $schedule['sat']['returnDropOffTime']->format('H:i');
                        $schedule['sat']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getSatTime();
                        $schedule['sat']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['sat']['returnEndTime'] = $schedule['sat']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['sat']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['sat']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isSunCheck() && $ask->getAskLinked()->getCriteria()->getSunTime()) {
                        $schedule['sun']['check'] = true;
                        $schedule['sun']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getSunTime()->format('H:i');
                        $schedule['sun']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getSunTime();
                        $schedule['sun']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['sun']['returnPickUpTime'] = $schedule['sun']['returnPickUpTime']->format('H:i');
                        $schedule['sun']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getSunTime();
                        $schedule['sun']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['sun']['returnDropOffTime'] = $schedule['sun']['returnDropOffTime']->format('H:i');
                        $schedule['sun']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getSunTime();
                        $schedule['sun']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['sun']['returnEndTime'] = $schedule['sun']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['sun']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['sun']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    $driver['schedule'] = $schedule;

                    break;
            }
            $driver['returnPrice'] = $ask->getAskLinked()->getCriteria()->getPassengerComputedRoundedPrice();
        }

        // group days with similar times
        if (isset($driver['schedule'])) {
            $schedules = [];
            foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day) {
                $key = $this->getSchedulesKey(
                    $schedules,
                    $schedule[$day]['startTime'],
                    $schedule[$day]['endTime'],
                    $schedule[$day]['pickUpTime'],
                    $schedule[$day]['dropOffTime'],
                    isset($schedule[$day]['returnStartTime']) ? $schedule[$day]['returnStartTime'] : null,
                    isset($schedule[$day]['returnEndTime']) ? $schedule[$day]['returnEndTime'] : null,
                    isset($schedule[$day]['returnPickUpTime']) ? $schedule[$day]['returnPickUpTime'] : null,
                    isset($schedule[$day]['returnDropOffTime']) ? $schedule[$day]['returnDropOffTime'] : null
                );
                if (is_null($key)) {
                    $schedules[] = [
                        'mon' => 'mon' == $day ? $schedule[$day]['check'] : false,
                        'tue' => false,
                        'wed' => false,
                        'thu' => false,
                        'fri' => false,
                        'sat' => false,
                        'sun' => false,
                    ];
                    $key = count($schedules) - 1;
                    $schedules[$key][$day] = $schedule[$day]['check'];
                    $schedules[$key]['startTime'] = $schedule[$day]['startTime'];
                    $schedules[$key]['endTime'] = $schedule[$day]['endTime'];
                    $schedules[$key]['pickUpTime'] = $schedule[$day]['pickUpTime'];
                    $schedules[$key]['dropOffTime'] = $schedule[$day]['dropOffTime'];
                    $schedules[$key]['returnStartTime'] = isset($schedule[$day]['returnStartTime']) ? $schedule[$day]['returnStartTime'] : null;
                    $schedules[$key]['returnEndTime'] = isset($schedule[$day]['returnEndTime']) ? $schedule[$day]['returnEndTime'] : null;
                    $schedules[$key]['returnPickUpTime'] = isset($schedule[$day]['returnPickUpTime']) ? $schedule[$day]['returnPickUpTime'] : null;
                    $schedules[$key]['returnDropOffTime'] = isset($schedule[$day]['returnDropOffTime']) ? $schedule[$day]['returnDropOffTime'] : null;
                } else {
                    $schedules[$key][$day] = $schedule[$day]['check'];
                }
            }
            // we remove schedule without day carpooled
            $emptySchedule = null;
            foreach ($schedules as $key => $schedule) {
                if (
                    false == $schedules[$key]['mon']
                    && false == $schedules[$key]['tue']
                    && false == $schedules[$key]['wed']
                    && false == $schedules[$key]['thu']
                    && false == $schedules[$key]['fri']
                    && false == $schedules[$key]['sat']
                    && false == $schedules[$key]['sun']) {
                    $emptySchedule = $key;

                    break;
                }
            }
            if (!is_null($emptySchedule)) {
                unset($schedules[$emptySchedule]);
            }
            $driver['schedules'] = $schedules;
        }

        // payment
        $driver['payment']['status'] = MyAd::PAYMENT_STATUS_NULL;

        switch ($ask->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                // punctual trip, we search if there's a related carpoolItem
                if ($carpoolItem = $this->carpoolItemRepository->findByAskAndDate($ask, $ask->getCriteria()->getFromDate())) {
                    if (CarpoolItem::DEBTOR_STATUS_NULL == $carpoolItem->getDebtorStatus() || CarpoolItem::CREDITOR_STATUS_NULL == $carpoolItem->getCreditorStatus()) {
                        $driver['payment']['status'] = MyAd::PAYMENT_STATUS_NULL;
                    } elseif (!is_null($carpoolItem->getUnpaidDate())) {
                        $driver['payment']['status'] = MyAd::PAYMENT_STATUS_TODO;
                        $driver['payment']['unpaidDate'] = $carpoolItem->getUnpaidDate()->format('Y-m-d');
                        $driver['payment']['itemId'] = $carpoolItem->getId();
                    } else {
                        switch ($carpoolItem->getDebtorStatus()) {
                            case CarpoolItem::DEBTOR_STATUS_DIRECT:
                            case CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT:
                            case CarpoolItem::DEBTOR_STATUS_ONLINE:
                                $driver['payment']['status'] = MyAd::PAYMENT_STATUS_PAID;

                                break;

                            case CarpoolItem::DEBTOR_STATUS_NULL:
                                $driver['payment']['status'] = MyAd::PAYMENT_STATUS_NULL;

                                break;

                            default:
                                $driver['payment']['status'] = MyAd::PAYMENT_STATUS_TODO;
                                $driver['payment']['itemId'] = $carpoolItem->getId();

                                break;
                        }
                    }
                }

                break;

            case Criteria::FREQUENCY_REGULAR:
                $driver['payment'] = $this->getPaymentDetailsForRegularAsk($ask, MyAd::ROLE_PASSENGER);

                break;
        }

        return $driver;
    }

    /**
     * Get the passenger details for a given user and ask.
     *
     * @param User $user The user
     * @param Ask  $ask  The ask
     *
     * @return array The passenger details
     */
    private function getPassengerDetailsForUserAndAsk(User $user, Ask $ask)
    {
        $waypoints = [];
        $endDuration = 0;
        $pickUpDuration = 0;
        $dropOffDuration = 0;
        $pickUpPosition = 9999;
        $dropOffPosition = 0;
        foreach ($ask->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if (Waypoint::ROLE_PASSENGER == $waypoint->getRole()) {
                $waypoints[] = [
                    'origin' => false,      // computed after !
                    'destination' => false, // computed after !
                    'position' => $waypoint->getPosition(),
                    'houseNumber' => $waypoint->getAddress()->getHouseNumber(),
                    'street' => $waypoint->getAddress()->getStreet(),
                    'streetAddress' => $waypoint->getAddress()->getStreetAddress(),
                    'postalCode' => $waypoint->getAddress()->getPostalCode(),
                    'addressLocality' => $waypoint->getAddress()->getAddressLocality(),
                    'region' => $waypoint->getAddress()->getRegion(),
                    'addressCountry' => $waypoint->getAddress()->getAddressCountry(),
                ];
                if ($waypoint->getPosition() < $pickUpPosition) {
                    $pickUpPosition = $waypoint->getPosition();
                    $pickUpDuration = $waypoint->getDuration();
                }
                if ($waypoint->getPosition() > $dropOffPosition) {
                    $dropOffPosition = $waypoint->getPosition();
                    $dropOffDuration = $waypoint->getDuration();
                }
            } elseif ($waypoint->isDestination()) {
                $endDuration = $waypoint->getDuration();
            }
        }
        // we need to find the origin and destination of the passenger (not the one of the ask !)
        $cwaypoints = [];
        foreach ($waypoints as $waypoint) {
            if ($waypoint['position'] == $pickUpPosition) {
                $waypoint['origin'] = true;
            }
            if ($waypoint['position'] == $dropOffPosition) {
                $waypoint['destination'] = true;
            }
            $cwaypoints[] = $waypoint;
        }
        $waypoints = $cwaypoints;
        $passenger = [
            'id' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getId() : $ask->getUser()->getId(),
            'givenName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getGivenName() : $ask->getUser()->getGivenName(),
            'shortFamilyName' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getShortFamilyName() : $ask->getUser()->getShortFamilyName(),
            'birthYear' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getBirthYear() : $ask->getUser()->getBirthYear(),
            'age' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getAge() : $ask->getUser()->getAge(),
            'telephone' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getTelephone() : $ask->getUser()->getTelephone(),
            'avatars' => $ask->getUser()->getId() == $user->getId() ? $ask->getUserRelated()->getAvatars() : $ask->getUser()->getAvatars(),
            'waypoints' => $waypoints,
            'price' => $ask->getCriteria()->getPassengerComputedRoundedPrice(),
            'askId' => $ask->getId(),
            'askFrequency' => $ask->getCriteria()->getFrequency(),
        ];

        // date and time
        $schedule = [];

        switch ($ask->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                /**
                 * @var \DateTime $startDate
                 */
                $startDate = $ask->getCriteria()->getFromDate();
                if (!is_null($ask->getCriteria()->getFromTime())) {
                    $startDate->setTime(
                        $ask->getCriteria()->getFromTime()->format('H'),
                        $ask->getCriteria()->getFromTime()->format('i')
                    );
                }
                $pickupDate = clone $startDate;
                $dropOffDate = clone $startDate;
                $endDate = clone $startDate;
                $pickupDate->modify('+'.$pickUpDuration.' second');
                $dropOffDate->modify('+'.$dropOffDuration.' second');
                $endDate->modify('+'.$endDuration.' second');
                $passenger['fromDate'] = $ask->getCriteria()->getFromDate()->format('Y-m-d');
                $passenger['startTime'] = $startDate->format('H:i');
                $passenger['pickUpTime'] = $pickupDate->format('H:i');
                $passenger['dropOffTime'] = $dropOffDate->format('H:i');
                $passenger['endTime'] = $endDate->format('H:i');

                break;

            case Criteria::FREQUENCY_REGULAR:
                $passenger['fromDate'] = $ask->getCriteria()->getFromDate()->format('Y-m-d');
                $passenger['toDate'] = $ask->getCriteria()->getToDate()->format('Y-m-d');
                $schedule['pickUpTime'] = null;
                $schedule['mon']['check'] = $schedule['tue']['check'] = $schedule['wed']['check'] = $schedule['thu']['check'] = $schedule['fri']['check'] = $schedule['sat']['check'] = $schedule['sun']['check'] = false;
                $schedule['mon']['startTime'] = $schedule['tue']['startTime'] = $schedule['wed']['startTime'] = $schedule['thu']['startTime'] = $schedule['fri']['startTime'] = $schedule['sat']['startTime'] = $schedule['sun']['startTime'] = null;
                $schedule['mon']['pickUpTime'] = $schedule['tue']['pickUpTime'] = $schedule['wed']['pickUpTime'] = $schedule['thu']['pickUpTime'] = $schedule['fri']['pickUpTime'] = $schedule['sat']['pickUpTime'] = $schedule['sun']['pickUpTime'] = null;
                $schedule['mon']['dropOffTime'] = $schedule['tue']['dropOffTime'] = $schedule['wed']['dropOffTime'] = $schedule['thu']['dropOffTime'] = $schedule['fri']['dropOffTime'] = $schedule['sat']['dropOffTime'] = $schedule['sun']['dropOffTime'] = null;
                $schedule['mon']['endTime'] = $schedule['tue']['endTime'] = $schedule['wed']['endTime'] = $schedule['thu']['endTime'] = $schedule['fri']['endTime'] = $schedule['sat']['endTime'] = $schedule['sun']['endTime'] = null;
                if ($ask->getCriteria()->isMonCheck() && $ask->getCriteria()->getMonTime()) {
                    $schedule['mon']['check'] = true;
                    $schedule['mon']['startTime'] = $ask->getCriteria()->getMonTime()->format('H:i');
                    $schedule['mon']['pickUpTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['mon']['pickUpTime'] = $schedule['mon']['pickUpTime']->format('H:i');
                    $schedule['mon']['dropOffTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['mon']['dropOffTime'] = $schedule['mon']['dropOffTime']->format('H:i');
                    $schedule['mon']['endTime'] = clone $ask->getCriteria()->getMonTime();
                    $schedule['mon']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['mon']['endTime'] = $schedule['mon']['endTime']->format('H:i');
                    $schedule['pickUpTime'] = $schedule['mon']['pickUpTime'];
                }
                if ($ask->getCriteria()->isTueCheck() && $ask->getCriteria()->getTueTime()) {
                    $schedule['tue']['check'] = true;
                    $schedule['tue']['startTime'] = $ask->getCriteria()->getTueTime()->format('H:i');
                    $schedule['tue']['pickUpTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['tue']['pickUpTime'] = $schedule['tue']['pickUpTime']->format('H:i');
                    $schedule['tue']['dropOffTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['tue']['dropOffTime'] = $schedule['tue']['dropOffTime']->format('H:i');
                    $schedule['tue']['endTime'] = clone $ask->getCriteria()->getTueTime();
                    $schedule['tue']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['tue']['endTime'] = $schedule['tue']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['tue']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['tue']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isWedCheck() && $ask->getCriteria()->getWedTime()) {
                    $schedule['wed']['check'] = true;
                    $schedule['wed']['startTime'] = $ask->getCriteria()->getWedTime()->format('H:i');
                    $schedule['wed']['pickUpTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['wed']['pickUpTime'] = $schedule['wed']['pickUpTime']->format('H:i');
                    $schedule['wed']['dropOffTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['wed']['dropOffTime'] = $schedule['wed']['dropOffTime']->format('H:i');
                    $schedule['wed']['endTime'] = clone $ask->getCriteria()->getWedTime();
                    $schedule['wed']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['wed']['endTime'] = $schedule['wed']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['wed']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['wed']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isThuCheck() && $ask->getCriteria()->getThuTime()) {
                    $schedule['thu']['check'] = true;
                    $schedule['thu']['startTime'] = $ask->getCriteria()->getThuTime()->format('H:i');
                    $schedule['thu']['pickUpTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['thu']['pickUpTime'] = $schedule['thu']['pickUpTime']->format('H:i');
                    $schedule['thu']['dropOffTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['thu']['dropOffTime'] = $schedule['thu']['dropOffTime']->format('H:i');
                    $schedule['thu']['endTime'] = clone $ask->getCriteria()->getThuTime();
                    $schedule['thu']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['thu']['endTime'] = $schedule['thu']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['thu']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['thu']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isFriCheck() && $ask->getCriteria()->getFriTime()) {
                    $schedule['fri']['check'] = true;
                    $schedule['fri']['startTime'] = $ask->getCriteria()->getFriTime()->format('H:i');
                    $schedule['fri']['pickUpTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['fri']['pickUpTime'] = $schedule['fri']['pickUpTime']->format('H:i');
                    $schedule['fri']['dropOffTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['fri']['dropOffTime'] = $schedule['fri']['dropOffTime']->format('H:i');
                    $schedule['fri']['endTime'] = clone $ask->getCriteria()->getFriTime();
                    $schedule['fri']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['fri']['endTime'] = $schedule['fri']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['fri']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['fri']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isSatCheck() && $ask->getCriteria()->getSatTime()) {
                    $schedule['sat']['check'] = true;
                    $schedule['sat']['startTime'] = $ask->getCriteria()->getSatTime()->format('H:i');
                    $schedule['sat']['pickUpTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['sat']['pickUpTime'] = $schedule['sat']['pickUpTime']->format('H:i');
                    $schedule['sat']['dropOffTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['sat']['dropOffTime'] = $schedule['sat']['dropOffTime']->format('H:i');
                    $schedule['sat']['endTime'] = clone $ask->getCriteria()->getSatTime();
                    $schedule['sat']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['sat']['endTime'] = $schedule['sat']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['sat']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['sat']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                if ($ask->getCriteria()->isSunCheck() && $ask->getCriteria()->getSunTime()) {
                    $schedule['sun']['check'] = true;
                    $schedule['sun']['startTime'] = $ask->getCriteria()->getSunTime()->format('H:i');
                    $schedule['sun']['pickUpTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['pickUpTime']->modify('+'.$pickUpDuration.' second');
                    $schedule['sun']['pickUpTime'] = $schedule['sun']['pickUpTime']->format('H:i');
                    $schedule['sun']['dropOffTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['dropOffTime']->modify('+'.$dropOffDuration.' second');
                    $schedule['sun']['dropOffTime'] = $schedule['sun']['dropOffTime']->format('H:i');
                    $schedule['sun']['endTime'] = clone $ask->getCriteria()->getSunTime();
                    $schedule['sun']['endTime']->modify('+'.$endDuration.' second');
                    $schedule['sun']['endTime'] = $schedule['sun']['endTime']->format('H:i');
                    if (is_null($schedule['pickUpTime'])) {
                        $schedule['pickUpTime'] = $schedule['sun']['pickUpTime'];
                    } elseif ($schedule['pickUpTime'] != $schedule['sun']['pickUpTime']) {
                        $schedule['pickUpTime'] = 'multiple';
                    }
                }
                $passenger['schedule'] = $schedule;

                break;
        }
        if ($ask->getAskLinked()) {
            $waypoints = [];
            $endDuration = 0;
            $pickUpDuration = 0;
            $dropOffDuration = 0;
            $pickUpPosition = 9999;
            $dropOffPosition = 0;
            foreach ($ask->getAskLinked()->getWaypoints() as $waypoint) {
                /**
                 * @var Waypoint $waypoint
                 */
                if (Waypoint::ROLE_PASSENGER == $waypoint->getRole()) {
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
                    if ($waypoint->getPosition() < $pickUpPosition) {
                        $pickUpPosition = $waypoint->getPosition();
                        $pickUpDuration = $waypoint->getDuration();
                    }
                    if ($waypoint->getPosition() > $dropOffPosition) {
                        $dropOffPosition = $waypoint->getPosition();
                        $dropOffDuration = $waypoint->getDuration();
                    }
                } elseif ($waypoint->isDestination()) {
                    $endDuration = $waypoint->getDuration();
                }
            }
            $passenger['returnWaypoints'] = $waypoints;

            switch ($ask->getAskLinked()->getCriteria()->getFrequency()) {
                case Criteria::FREQUENCY_PUNCTUAL:
                    /**
                     * @var \DateTime $startDate
                     */
                    $startDate = $ask->getAskLinked()->getCriteria()->getFromDate();
                    if (!is_null($ask->getAskLinked()->getCriteria()->getFromTime())) {
                        $startDate->setTime(
                            $ask->getAskLinked()->getCriteria()->getFromTime()->format('H'),
                            $ask->getAskLinked()->getCriteria()->getFromTime()->format('i')
                        );
                    }
                    $pickupDate = clone $startDate;
                    $dropOffDate = clone $startDate;
                    $endDate = clone $startDate;
                    $pickupDate->modify('+'.$pickUpDuration.' second');
                    $dropOffDate->modify('+'.$dropOffDuration.' second');
                    $endDate->modify('+'.$endDuration.' second');
                    $passenger['returnFromDate'] = $ask->getAskLinked()->getCriteria()->getFromDate()->format('Y-m-d');
                    $passenger['returnStartTime'] = $startDate->format('H:i');
                    $passenger['returnPickUpTime'] = $pickupDate->format('H:i');
                    $passenger['returnDropOffTime'] = $dropOffDate->format('H:i');
                    $passenger['returnEndTime'] = $endDate->format('H:i');

                    break;

                case Criteria::FREQUENCY_REGULAR:
                    $passenger['returnFromDate'] = $ask->getAskLinked()->getCriteria()->getFromDate()->format('Y-m-d');
                    $passenger['returnToDate'] = $ask->getAskLinked()->getCriteria()->getToDate()->format('Y-m-d');
                    $schedule['returnPickUpTime'] = null;
                    $schedule['mon']['returnStartTime'] = $schedule['tue']['returnStartTime'] = $schedule['wed']['returnStartTime'] = $schedule['thu']['returnStartTime'] = $schedule['fri']['returnStartTime'] = $schedule['sat']['returnStartTime'] = $schedule['sun']['returnStartTime'] = null;
                    $schedule['mon']['returnPickUpTime'] = $schedule['tue']['returnPickUpTime'] = $schedule['wed']['returnPickUpTime'] = $schedule['thu']['returnPickUpTime'] = $schedule['fri']['returnPickUpTime'] = $schedule['sat']['returnPickUpTime'] = $schedule['sun']['returnPickUpTime'] = null;
                    $schedule['mon']['returnDropOffTime'] = $schedule['tue']['returnDropOffTime'] = $schedule['wed']['returnDropOffTime'] = $schedule['thu']['returnDropOffTime'] = $schedule['fri']['returnDropOffTime'] = $schedule['sat']['returnDropOffTime'] = $schedule['sun']['returnDropOffTime'] = null;
                    $schedule['mon']['returnEndTime'] = $schedule['tue']['returnEndTime'] = $schedule['wed']['returnEndTime'] = $schedule['thu']['returnEndTime'] = $schedule['fri']['returnEndTime'] = $schedule['sat']['returnEndTime'] = $schedule['sun']['returnEndTime'] = null;
                    if ($ask->getAskLinked()->getCriteria()->isMonCheck() && $ask->getAskLinked()->getCriteria()->getMonTime()) {
                        $schedule['mon']['check'] = true;
                        $schedule['mon']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getMonTime()->format('H:i');
                        $schedule['mon']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getMonTime();
                        $schedule['mon']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['mon']['returnPickUpTime'] = $schedule['mon']['returnPickUpTime']->format('H:i');
                        $schedule['mon']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getMonTime();
                        $schedule['mon']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['mon']['returnDropOffTime'] = $schedule['mon']['returnDropOffTime']->format('H:i');
                        $schedule['mon']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getMonTime();
                        $schedule['mon']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['mon']['returnEndTime'] = $schedule['mon']['returnEndTime']->format('H:i');
                        $schedule['returnPickUpTime'] = $schedule['mon']['returnPickUpTime'];
                    }
                    if ($ask->getAskLinked()->getCriteria()->isTueCheck() && $ask->getAskLinked()->getCriteria()->getTueTime()) {
                        $schedule['tue']['check'] = true;
                        $schedule['tue']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getTueTime()->format('H:i');
                        $schedule['tue']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getTueTime();
                        $schedule['tue']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['tue']['returnPickUpTime'] = $schedule['tue']['returnPickUpTime']->format('H:i');
                        $schedule['tue']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getTueTime();
                        $schedule['tue']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['tue']['returnDropOffTime'] = $schedule['tue']['returnDropOffTime']->format('H:i');
                        $schedule['tue']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getTueTime();
                        $schedule['tue']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['tue']['returnEndTime'] = $schedule['tue']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['tue']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['tue']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isWedCheck() && $ask->getAskLinked()->getCriteria()->getWedTime()) {
                        $schedule['wed']['check'] = true;
                        $schedule['wed']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getWedTime()->format('H:i');
                        $schedule['wed']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getWedTime();
                        $schedule['wed']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['wed']['returnPickUpTime'] = $schedule['wed']['returnPickUpTime']->format('H:i');
                        $schedule['wed']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getWedTime();
                        $schedule['wed']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['wed']['returnDropOffTime'] = $schedule['wed']['returnDropOffTime']->format('H:i');
                        $schedule['wed']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getWedTime();
                        $schedule['wed']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['wed']['returnEndTime'] = $schedule['wed']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['wed']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['wed']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isThuCheck() && $ask->getAskLinked()->getCriteria()->getThuTime()) {
                        $schedule['thu']['check'] = true;
                        $schedule['thu']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getThuTime()->format('H:i');
                        $schedule['thu']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getThuTime();
                        $schedule['thu']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['thu']['returnPickUpTime'] = $schedule['thu']['returnPickUpTime']->format('H:i');
                        $schedule['thu']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getThuTime();
                        $schedule['thu']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['thu']['returnDropOffTime'] = $schedule['thu']['returnDropOffTime']->format('H:i');
                        $schedule['thu']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getThuTime();
                        $schedule['thu']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['thu']['returnEndTime'] = $schedule['thu']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['thu']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['thu']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isFriCheck() && $ask->getAskLinked()->getCriteria()->getFriTime()) {
                        $schedule['fri']['check'] = true;
                        $schedule['fri']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getFriTime()->format('H:i');
                        $schedule['fri']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getFriTime();
                        $schedule['fri']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['fri']['returnPickUpTime'] = $schedule['fri']['returnPickUpTime']->format('H:i');
                        $schedule['fri']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getFriTime();
                        $schedule['fri']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['fri']['returnDropOffTime'] = $schedule['fri']['returnDropOffTime']->format('H:i');
                        $schedule['fri']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getFriTime();
                        $schedule['fri']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['fri']['returnEndTime'] = $schedule['fri']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['fri']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['fri']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isSatCheck() && $ask->getAskLinked()->getCriteria()->getSatTime()) {
                        $schedule['sat']['check'] = true;
                        $schedule['sat']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getSatTime()->format('H:i');
                        $schedule['sat']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getSatTime();
                        $schedule['sat']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['sat']['returnPickUpTime'] = $schedule['sat']['returnPickUpTime']->format('H:i');
                        $schedule['sat']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getSatTime();
                        $schedule['sat']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['sat']['returnDropOffTime'] = $schedule['sat']['returnDropOffTime']->format('H:i');
                        $schedule['sat']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getSatTime();
                        $schedule['sat']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['sat']['returnEndTime'] = $schedule['sat']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['sat']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['sat']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    if ($ask->getAskLinked()->getCriteria()->isSunCheck() && $ask->getAskLinked()->getCriteria()->getSunTime()) {
                        $schedule['sun']['check'] = true;
                        $schedule['sun']['returnStartTime'] = $ask->getAskLinked()->getCriteria()->getSunTime()->format('H:i');
                        $schedule['sun']['returnPickUpTime'] = clone $ask->getAskLinked()->getCriteria()->getSunTime();
                        $schedule['sun']['returnPickUpTime']->modify('+'.$pickUpDuration.' second');
                        $schedule['sun']['returnPickUpTime'] = $schedule['sun']['returnPickUpTime']->format('H:i');
                        $schedule['sun']['returnDropOffTime'] = clone $ask->getAskLinked()->getCriteria()->getSunTime();
                        $schedule['sun']['returnDropOffTime']->modify('+'.$dropOffDuration.' second');
                        $schedule['sun']['returnDropOffTime'] = $schedule['sun']['returnDropOffTime']->format('H:i');
                        $schedule['sun']['returnEndTime'] = clone $ask->getAskLinked()->getCriteria()->getSunTime();
                        $schedule['sun']['returnEndTime']->modify('+'.$endDuration.' second');
                        $schedule['sun']['returnEndTime'] = $schedule['sun']['returnEndTime']->format('H:i');
                        if (is_null($schedule['returnPickUpTime'])) {
                            $schedule['returnPickUpTime'] = $schedule['sun']['returnPickUpTime'];
                        } elseif ($schedule['returnPickUpTime'] != $schedule['sun']['returnPickUpTime']) {
                            $schedule['returnPickUpTime'] = 'multiple';
                        }
                    }
                    $passenger['schedule'] = $schedule;

                    break;
            }
            $passenger['returnPrice'] = $ask->getAskLinked()->getCriteria()->getPassengerComputedRoundedPrice();
        }

        // group days with similar times
        if (isset($passenger['schedule'])) {
            $schedules = [];
            foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day) {
                $key = $this->getSchedulesKey(
                    $schedules,
                    $schedule[$day]['startTime'],
                    $schedule[$day]['endTime'],
                    $schedule[$day]['pickUpTime'],
                    $schedule[$day]['dropOffTime'],
                    isset($schedule[$day]['returnStartTime']) ? $schedule[$day]['returnStartTime'] : null,
                    isset($schedule[$day]['returnEndTime']) ? $schedule[$day]['returnEndTime'] : null,
                    isset($schedule[$day]['returnPickUpTime']) ? $schedule[$day]['returnPickUpTime'] : null,
                    isset($schedule[$day]['returnDropOffTime']) ? $schedule[$day]['returnDropOffTime'] : null
                );
                if (is_null($key)) {
                    $schedules[] = [
                        'mon' => 'mon' == $day ? $schedule[$day]['check'] : false,
                        'tue' => false,
                        'wed' => false,
                        'thu' => false,
                        'fri' => false,
                        'sat' => false,
                        'sun' => false,
                    ];
                    $key = count($schedules) - 1;
                    $schedules[$key][$day] = $schedule[$day]['check'];
                    $schedules[$key]['startTime'] = $schedule[$day]['startTime'];
                    $schedules[$key]['endTime'] = $schedule[$day]['endTime'];
                    $schedules[$key]['pickUpTime'] = $schedule[$day]['pickUpTime'];
                    $schedules[$key]['dropOffTime'] = $schedule[$day]['dropOffTime'];
                    $schedules[$key]['returnStartTime'] = isset($schedule[$day]['returnStartTime']) ? $schedule[$day]['returnStartTime'] : null;
                    $schedules[$key]['returnEndTime'] = isset($schedule[$day]['returnEndTime']) ? $schedule[$day]['returnEndTime'] : null;
                    $schedules[$key]['returnPickUpTime'] = isset($schedule[$day]['returnPickUpTime']) ? $schedule[$day]['returnPickUpTime'] : null;
                    $schedules[$key]['returnDropOffTime'] = isset($schedule[$day]['returnDropOffTime']) ? $schedule[$day]['returnDropOffTime'] : null;
                } else {
                    $schedules[$key][$day] = $schedule[$day]['check'];
                }
            }
            // we remove schedule without day carpooled
            $emptySchedule = null;
            foreach ($schedules as $key => $schedule) {
                if (
                    false == $schedules[$key]['mon']
                    && false == $schedules[$key]['tue']
                    && false == $schedules[$key]['wed']
                    && false == $schedules[$key]['thu']
                    && false == $schedules[$key]['fri']
                    && false == $schedules[$key]['sat']
                    && false == $schedules[$key]['sun']) {
                    $emptySchedule = $key;

                    break;
                }
            }
            if (!is_null($emptySchedule)) {
                unset($schedules[$emptySchedule]);
            }
            $passenger['schedules'] = $schedules;
        }

        // payment
        $passenger['payment']['status'] = MyAd::PAYMENT_STATUS_NULL;

        switch ($ask->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                // punctual trip, we search if there's a related carpoolItem
                if ($carpoolItem = $this->carpoolItemRepository->findByAskAndDate($ask, $ask->getCriteria()->getFromDate())) {
                    if (!is_null($carpoolItem->getUnpaidDate())) {
                        $passenger['payment']['status'] = MyAd::PAYMENT_STATUS_TODO;
                        $passenger['payment']['unpaidDate'] = $carpoolItem->getUnpaidDate()->format('Y-m-d');
                        $passenger['payment']['itemId'] = $carpoolItem->getId();
                    } else {
                        switch ($carpoolItem->getCreditorStatus()) {
                            case CarpoolItem::CREDITOR_STATUS_ONLINE:
                            case CarpoolItem::CREDITOR_STATUS_DIRECT:
                                $passenger['payment']['status'] = MyAd::PAYMENT_STATUS_PAID;

                                break;

                            case CarpoolItem::CREDITOR_STATUS_NULL:
                                $passenger['payment']['status'] = MyAd::PAYMENT_STATUS_NULL;

                                break;

                            default:
                                $passenger['payment']['status'] = MyAd::PAYMENT_STATUS_TODO;
                                $passenger['payment']['itemId'] = $carpoolItem->getId();

                                break;
                        }
                    }
                } else {
                    $passenger['payment']['status'] = MyAd::PAYMENT_STATUS_NULL;
                }

                break;

            case Criteria::FREQUENCY_REGULAR:
                $passenger['payment'] = $this->getPaymentDetailsForRegularAsk($ask, MyAd::ROLE_DRIVER);

                break;
        }

        return $passenger;
    }

    private function getSchedulesKey($schedules, $startTime, $endTime, $pickUpTime, $dropOffTime, $returnStartTime, $returnEndTime, $returnPickUpTime, $returnDropOffTime)
    {
        foreach ($schedules as $key => $schedule) {
            if (
                $schedule['startTime'] == $startTime
                && $schedule['endTime'] == $endTime
                && $schedule['pickUpTime'] == $pickUpTime
                && $schedule['dropOffTime'] == $dropOffTime
                && $schedule['returnStartTime'] == $returnStartTime
                && $schedule['returnEndTime'] == $returnEndTime
                && $schedule['returnPickUpTime'] == $returnPickUpTime
                && $schedule['returnDropOffTime'] == $returnDropOffTime
            ) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get the payment details for a regular ask.
     *
     * @param Ask $ask  The ask
     * @param int $role The role for which we want the details
     *
     * @return array The details of the first pending week
     */
    private function getPaymentDetailsForRegularAsk(Ask $ask, int $role)
    {
        // we limit to the last day of the previous week
        $maxDate = new \DateTime();
        $maxDate->modify('last week +6 days');

        $startDate = $ask->getCriteria()->getFromDate();
        $toDate = min($maxDate, $ask->getCriteria()->getToDate());

        // we get all the carpool items for the period, ordered by item date
        $carpoolItems = $this->carpoolItemRepository->findByAskAndPeriod($ask, $startDate, $toDate);

        $curWeek = null;
        $firstCarpoolItem = null;
        foreach ($carpoolItems as $carpoolItem) {
            $weekNumber = $carpoolItem->getItemDate()->format('W');
            if ($curWeek != $weekNumber) {
                // new week
                $curWeek = $weekNumber;
                $firstCarpoolItem = $carpoolItem;
            }
            if (!is_null($carpoolItem->getUnpaidDate())) {
                // declared as unpaid
                return [
                    'status' => MyAd::PAYMENT_STATUS_TODO,
                    'unpaidDate' => $carpoolItem->getUnpaidDate()->format('Y-m-d'),
                    'itemId' => $firstCarpoolItem->getId(),
                    'week' => $carpoolItem->getItemDate()->format('WY'),
                ];
            }
            if (MyAd::ROLE_PASSENGER == $role && CarpoolItem::STATUS_NOT_REALIZED !== $carpoolItem->getItemStatus() && CarpoolItem::DEBTOR_STATUS_PENDING == $carpoolItem->getDebtorStatus()) {
                // passenger has to pay
                return [
                    'status' => MyAd::PAYMENT_STATUS_TODO,
                    'unpaidDate' => !is_null($carpoolItem->getUnpaidDate()) ? $carpoolItem->getUnpaidDate()->format('Y-m-d') : null,
                    'itemId' => $firstCarpoolItem->getId(),
                    'week' => $carpoolItem->getItemDate()->format('WY'),
                ];
            }
            if (MyAd::ROLE_DRIVER == $role && CarpoolItem::STATUS_NOT_REALIZED !== $carpoolItem->getItemStatus() && CarpoolItem::CREDITOR_STATUS_PENDING == $carpoolItem->getCreditorStatus()) {
                // driver has to validate
                return [
                    'status' => MyAd::PAYMENT_STATUS_TODO,
                    'unpaidDate' => !is_null($carpoolItem->getUnpaidDate()) ? $carpoolItem->getUnpaidDate()->format('Y-m-d') : null,
                    'itemId' => $firstCarpoolItem->getId(),
                    'week' => $carpoolItem->getItemDate()->format('WY'),
                ];
            }
        }

        // default
        return [
            'status' => ($this->paymentActive && count($carpoolItems) > 0) ? MyAd::PAYMENT_STATUS_PAID : MyAd::PAYMENT_STATUS_NULL,
        ];
    }
}
