<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

use App\Carpool\Entity\Ad;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Community\Exception\CommunityNotFoundException;
use App\Community\Service\CommunityManager;
use App\Event\Exception\EventNotFoundException;
use App\Event\Service\EventManager;
use App\Geography\Entity\Address;
use App\User\Exception\UserNotFoundException;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Ad manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class AdManager
{
    private $entityManager;
    private $proposalManager;
    private $userManager;
    private $communityManager;
    private $eventManager;
    private $resultManager;
    private $params;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalManager $proposalManager
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalManager $proposalManager, UserManager $userManager, CommunityManager $communityManager, EventManager $eventManager, ResultManager $resultManager, array $params)
    {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->communityManager = $communityManager;
        $this->eventManager = $eventManager;
        $this->resultManager = $resultManager;
        $this->params = $params;
    }
    
    /**
     * Create an ad.
     * This method creates a proposal, and its linked proposal for a return trip.
     * It returns the ad created, with its outward and return results.
     *
     * @param Ad $ad            The ad to create
     * @return Ad
     */
    public function createAd(Ad $ad)
    {
        $outwardProposal = new Proposal();
        $outwardCriteria = new Criteria();

        // the proposal is private if it's a search only ad
        $outwardProposal->setPrivate($ad->isSearch());

        // we check if it's a round trip
        if ($ad->isOneWay()) {
            // the ad has explicitly been set to one way
            $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
        } else {
            // the ad type has not been set, we assume it's a round trip
            $outwardProposal->setType(Proposal::TYPE_OUTWARD);
        }

        // we set the user of the proposal
        if ($user = $this->userManager->getUser($ad->getUserId())) {
            $outwardProposal->setUser($user);
        } else {
            throw new UserNotFoundException('User ' . $ad->getUserId() . ' not found');
        }

        // we check if the ad is posted for another user (delegation)
        if ($ad->getPosterId()) {
            if ($poster = $this->userManager->getUser($ad->getPosterId())) {
                $outwardProposal->setUserDelegate($poster);
            } else {
                throw new UserNotFoundException('Poster ' . $ad->getPosterId() . ' not found');
            }
        }

        // comment
        $outwardProposal->setComment($ad->getComment());

        // communities
        if ($ad->getCommunities()) {
            foreach ($ad->getCommunities() as $communityId) {
                if ($community = $this->communityManager->getCommunity($communityId)) {
                    $outwardProposal->addCommunity($community);
                } else {
                    throw new CommunityNotFoundException('Community ' . $communityId . ' not found');
                }
            }
        }

        // event
        if ($ad->getEventId()) {
            if ($event = $this->eventManager->getEvent($ad->getEventId())) {
                $outwardProposal->setEvent($event);
            } else {
                throw new EventNotFoundException('Event ' . $ad->getEventId() . ' not found');
            }
        }
        
        // criteria

        // driver / passenger / seats
        $outwardCriteria->setDriver($ad->getRole() == Ad::ROLE_DRIVER || $ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER);
        $outwardCriteria->setPassenger($ad->getRole() == Ad::ROLE_PASSENGER || $ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER);
        $outwardCriteria->setSeats($ad->getSeats());

        // solidary
        $outwardCriteria->setSolidary($ad->isSolidary());
        $outwardCriteria->setSolidaryExclusive($ad->isSolidaryExclusive());

        // prices
        $outwardCriteria->setPriceKm($ad->getPriceKm());
        $outwardCriteria->setDriverPrice($ad->getOutwardDriverPrice());
        $outwardCriteria->setPassengerPrice($ad->getOutwardPassengerPrice());

        // strict
        $outwardCriteria->setStrictDate($ad->isStrictDate());
        $outwardCriteria->setStrictPunctual($ad->isStrictPunctual());
        $outwardCriteria->setStrictRegular($ad->isStrictRegular());

        // misc
        $outwardCriteria->setLuggage($ad->hasLuggage());
        $outwardCriteria->setBike($ad->hasBike());
        $outwardCriteria->setBackSeats($ad->hasBackSeats());

        // dates and times
        $outwardCriteria->setFromDate($ad->getOutwardDate() ? $ad->getOutwardDate() : new \DateTime());
        if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            $outwardCriteria->setToDate($ad->getOutwardLimitDate() ? \DateTime::createFromFormat('Y-m-d', $ad->getOutwardLimitDate()) : null);
            
            foreach ($ad->getSchedule() as $schedule) {
                if ($schedule['outwardTime'] != '') {
                    if (isset($schedule['mon']) && $schedule['mon']) {
                        $outwardCriteria->setMonCheck(true);
                        $outwardCriteria->setMonTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $outwardCriteria->setMonMarginDuration($this->params['defaultMarginTime']);
                    }
                    if (isset($schedule['tue']) && $schedule['tue']) {
                        $outwardCriteria->setTueCheck(true);
                        $outwardCriteria->setTueTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $outwardCriteria->setTueMarginDuration($this->params['defaultMarginTime']);
                    }
                    if (isset($schedule['wed']) && $schedule['wed']) {
                        $outwardCriteria->setWedCheck(true);
                        $outwardCriteria->setWedTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $outwardCriteria->setWedMarginDuration($this->params['defaultMarginTime']);
                    }
                    if (isset($schedule['thu']) && $schedule['thu']) {
                        $outwardCriteria->setThuCheck(true);
                        $outwardCriteria->setThuTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $outwardCriteria->setThuMarginDuration($this->params['defaultMarginTime']);
                    }
                    if (isset($schedule['fri']) && $schedule['fri']) {
                        $outwardCriteria->setFriCheck(true);
                        $outwardCriteria->setFriTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $outwardCriteria->setFriMarginDuration($this->params['defaultMarginTime']);
                    }
                    if (isset($schedule['sat']) && $schedule['sat']) {
                        $outwardCriteria->setSatCheck(true);
                        $outwardCriteria->setsatTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $outwardCriteria->setSatMarginDuration($this->params['defaultMarginTime']);
                    }
                    if (isset($schedule['sun']) && $schedule['sun']) {
                        $outwardCriteria->setSunCheck(true);
                        $outwardCriteria->setSunTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $outwardCriteria->setSunMarginDuration($this->params['defaultMarginTime']);
                    }
                }
            }
        } else {
            // punctual
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            $outwardCriteria->setFromTime($ad->getOutwardTime() ? \DateTime::createFromFormat('H:i', $ad->getOutwardTime()) : null);
            $outwardCriteria->setMarginDuration($this->params['defaultMarginTime']);
        }

        // waypoints
        foreach ($ad->getOutwardWaypoints() as $position => $point) {
            $waypoint = new Waypoint();
            $address = new Address();
            if (isset($point['houseNumber'])) {
                $address->setHouseNumber($point['houseNumber']);
            }
            if (isset($point['street'])) {
                $address->setStreet($point['street']);
            }
            if (isset($point['streetAddress'])) {
                $address->setStreetAddress($point['streetAddress']);
            }
            if (isset($point['postalCode'])) {
                $address->setPostalCode($point['postalCode']);
            }
            if (isset($point['subLocality'])) {
                $address->setSubLocality($point['subLocality']);
            }
            if (isset($point['addressLocality'])) {
                $address->setAddressLocality($point['addressLocality']);
            }
            if (isset($point['localAdmin'])) {
                $address->setLocalAdmin($point['localAdmin']);
            }
            if (isset($point['county'])) {
                $address->setCounty($point['county']);
            }
            if (isset($point['macroCounty'])) {
                $address->setMacroCounty($point['macroCounty']);
            }
            if (isset($point['region'])) {
                $address->setRegion($point['region']);
            }
            if (isset($point['macroRegion'])) {
                $address->setMacroRegion($point['macroRegion']);
            }
            if (isset($point['addressCountry'])) {
                $address->setAddressCountry($point['addressCountry']);
            }
            if (isset($point['countryCode'])) {
                $address->setCountryCode($point['countryCode']);
            }
            if (isset($point['latitude'])) {
                $address->setLatitude($point['latitude']);
            }
            if (isset($point['longitude'])) {
                $address->setLongitude($point['longitude']);
            }
            if (isset($point['elevation'])) {
                $address->setElevation($point['elevation']);
            }
            if (isset($point['name'])) {
                $address->setName($point['name']);
            }
            if (isset($point['home'])) {
                $address->setHome($point['home']);
            }
            $waypoint->setAddress($address);
            $waypoint->setPosition($position);
            $waypoint->setDestination($position == count($ad->getOutwardWaypoints())-1);
            $outwardProposal->addWaypoint($waypoint);
        }

        $outwardProposal->setCriteria($outwardCriteria);
        $outwardProposal = $this->proposalManager->prepareProposal($outwardProposal);

        $this->entityManager->persist($outwardProposal);

        // return trip ?
        if (!$ad->isOneWay()) {
            // we clone the outward proposal
            $returnProposal = clone $outwardProposal;
            // we link the outward and the return
            $outwardProposal->setProposalLinked($returnProposal);

            // criteria
            $returnCriteria = new Criteria();

            // driver / passenger / seats
            $returnCriteria->setDriver($outwardCriteria->isDriver());
            $returnCriteria->setPassenger($outwardCriteria->isPassenger());
            $returnCriteria->setSeats($outwardCriteria->getSeats());

            // solidary
            $returnCriteria->setSolidary($outwardCriteria->isSolidary());
            $returnCriteria->setSolidaryExclusive($outwardCriteria->isSolidaryExclusive());

            // prices
            $returnCriteria->setPriceKm($outwardCriteria->getPriceKm());
            $returnCriteria->setDriverPrice($ad->getReturnDriverPrice());
            $returnCriteria->setPassengerPrice($ad->getReturnPassengerPrice());

            // strict
            $returnCriteria->setStrictDate($outwardCriteria->isStrictDate());
            $returnCriteria->setStrictPunctual($outwardCriteria->isStrictPunctual());
            $returnCriteria->setStrictRegular($outwardCriteria->isStrictRegular());

            // misc
            $returnCriteria->setLuggage($outwardCriteria->hasLuggage());
            $returnCriteria->setBike($outwardCriteria->hasBike());
            $returnCriteria->setBackSeats($outwardCriteria->hasBackSeats());

            // dates and times
            // if no return date is specified, we use the outward date to be sure the return date is not before the outward date
            $returnCriteria->setFromDate($ad->getReturnDate() ? $ad->getReturnDate() : $ad->getOutwardDate());
            if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                $returnCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
                $returnCriteria->setToDate($ad->getReturnLimitDate() ? \DateTime::createFromFormat('Y-m-d', $ad->getReturnLimitDate()) : null);
                
                foreach ($ad->getSchedule() as $schedule) {
                    if ($schedule['returnTime'] != '') {
                        if (isset($schedule['mon']) && $schedule['mon']) {
                            $returnCriteria->setMonCheck(true);
                            $returnCriteria->setMonTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $returnCriteria->setMonMarginDuration($this->params['defaultMarginTime']);
                        }
                        if (isset($schedule['tue']) && $schedule['tue']) {
                            $returnCriteria->setTueCheck(true);
                            $returnCriteria->setTueTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $returnCriteria->setTueMarginDuration($this->params['defaultMarginTime']);
                        }
                        if (isset($schedule['wed']) && $schedule['wed']) {
                            $returnCriteria->setWedCheck(true);
                            $returnCriteria->setWedTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $returnCriteria->setWedMarginDuration($this->params['defaultMarginTime']);
                        }
                        if (isset($schedule['thu']) && $schedule['thu']) {
                            $returnCriteria->setThuCheck(true);
                            $returnCriteria->setThuTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $returnCriteria->setThuMarginDuration($this->params['defaultMarginTime']);
                        }
                        if (isset($schedule['fri']) && $schedule['fri']) {
                            $returnCriteria->setFriCheck(true);
                            $returnCriteria->setFriTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $returnCriteria->setFriMarginDuration($this->params['defaultMarginTime']);
                        }
                        if (isset($schedule['sat']) && $schedule['sat']) {
                            $returnCriteria->setSatCheck(true);
                            $returnCriteria->setsatTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $returnCriteria->setSatMarginDuration($this->params['defaultMarginTime']);
                        }
                        if (isset($schedule['sun']) && $schedule['sun']) {
                            $returnCriteria->setSunCheck(true);
                            $returnCriteria->setSunTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $returnCriteria->setSunMarginDuration($this->params['defaultMarginTime']);
                        }
                    }
                }
            } else {
                // punctual
                $returnCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                $returnCriteria->setFromTime($ad->getReturnTime() ? \DateTime::createFromFormat('H:i', $ad->getReturnTime()) : null);
                $returnCriteria->setMarginDuration($this->params['defaultMarginTime']);
            }

            // waypoints
            if (count($ad->getReturnWaypoints())==0) {
                // return waypoints are not set : we use the outward waypoints in reverse order
                $ad->setReturnWaypoints(array_reverse($ad->getOutwardWaypoints()));
            }
            foreach ($ad->getReturnWaypoints() as $position => $point) {
                $waypoint = new Waypoint();
                $address = new Address();
                if (isset($point['houseNumber'])) {
                    $address->setHouseNumber($point['houseNumber']);
                }
                if (isset($point['street'])) {
                    $address->setStreet($point['street']);
                }
                if (isset($point['streetAddress'])) {
                    $address->setStreetAddress($point['streetAddress']);
                }
                if (isset($point['postalCode'])) {
                    $address->setPostalCode($point['postalCode']);
                }
                if (isset($point['subLocality'])) {
                    $address->setSubLocality($point['subLocality']);
                }
                if (isset($point['addressLocality'])) {
                    $address->setAddressLocality($point['addressLocality']);
                }
                if (isset($point['localAdmin'])) {
                    $address->setLocalAdmin($point['localAdmin']);
                }
                if (isset($point['county'])) {
                    $address->setCounty($point['county']);
                }
                if (isset($point['macroCounty'])) {
                    $address->setMacroCounty($point['macroCounty']);
                }
                if (isset($point['region'])) {
                    $address->setRegion($point['region']);
                }
                if (isset($point['macroRegion'])) {
                    $address->setMacroRegion($point['macroRegion']);
                }
                if (isset($point['addressCountry'])) {
                    $address->setAddressCountry($point['addressCountry']);
                }
                if (isset($point['countryCode'])) {
                    $address->setCountryCode($point['countryCode']);
                }
                if (isset($point['latitude'])) {
                    $address->setLatitude($point['latitude']);
                }
                if (isset($point['longitude'])) {
                    $address->setLongitude($point['longitude']);
                }
                if (isset($point['elevation'])) {
                    $address->setElevation($point['elevation']);
                }
                if (isset($point['name'])) {
                    $address->setName($point['name']);
                }
                if (isset($point['home'])) {
                    $address->setHome($point['home']);
                }
                $waypoint->setAddress($address);
                $waypoint->setPosition($position);
                $waypoint->setDestination($position == count($ad->getReturnWaypoints())-1);
                $returnProposal->addWaypoint($waypoint);
            }

            $returnProposal->setCriteria($returnCriteria);
            $returnProposal = $this->proposalManager->prepareProposal($returnProposal);
            $this->entityManager->persist($returnProposal);
        }

        // we persist the proposals
        $this->entityManager->flush();

        // if the ad is a round trip, we want to link the potential matching results
        if (!$ad->isOneWay()) {
            $outwardProposal = $this->proposalManager->linkRelatedMatchings($outwardProposal);
            $this->entityManager->persist($outwardProposal);
            $this->entityManager->flush();
        }
        // if the requester can be driver and passenger, we want to link the potential opposite matching results
        if ($ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER) {
            // linking for the outward
            $outwardProposal = $this->proposalManager->linkOppositeMatchings($outwardProposal);
            $this->entityManager->persist($outwardProposal);
            if (!$ad->isOneWay()) {
                // linking for the return
                $returnProposal = $this->proposalManager->linkOppositeMatchings($returnProposal);
                $this->entityManager->persist($returnProposal);
            }
            $this->entityManager->flush();
        }

        // we compute the results
        $ad->setOutwardResults($this->resultManager->createAdResults($outwardProposal));
        if (isset($returnProposal)) {
            $ad->setReturnResults($this->resultManager->createAdResults($outwardProposal));
        }

        return $ad;
    }
}
