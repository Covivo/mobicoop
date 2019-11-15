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
    private $params;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalManager $proposalManager
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalManager $proposalManager, UserManager $userManager, CommunityManager $communityManager, EventManager $eventManager, array $params)
    {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->communityManager = $communityManager;
        $this->eventManager = $eventManager;
        $this->params = $params;
    }
    
    /**
     * Create an ad.
     * This method creates a proposal, and its linked proposal for a return trip.
     * It returns the ad created, with its outward and return results.
     *
     * @param Ad $ad    The ad to create
     * @return Ad
     */
    public function createAd(Ad $ad)
    {
        $outwardProposal = new Proposal();
        $outwardCriteria = new Criteria();
        $returnProposal = null;
        $returnCriteria = null;

        // we check if it's a round trip
        if ($ad->getReturnWaypoints()) {
            $outwardProposal->setType(Proposal::TYPE_OUTWARD);
        } else {
            $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
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
        $outwardCriteria->setPrice($ad->getOutwardPrice());
        $outwardCriteria->setRoundedPrice($ad->getOutwardRoundedPrice());
        $outwardCriteria->setComputedPrice($ad->getOutwardComputedPrice());
        $outwardCriteria->setComputedRoundedPrice($ad->getOutwardComputedRoundedPrice());
        
        // misc
        $outwardCriteria->setLuggage($ad->hasLuggage());
        $outwardCriteria->setBike($ad->hasBike());
        $outwardCriteria->setBackSeats($ad->hasBackSeats());

        // dates and times
        if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            $outwardCriteria->setFromDate($ad->getOutwardDate() ? $ad->getOutwardDate() : new \DateTime());
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
            $outwardCriteria->setFromDate($ad->getOutwardDate() ? $ad->getOutwardDate() : new \DateTime());
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

        // return trip ?
        if ($ad->getReturnWaypoints()) {
            $returnProposal = new Proposal();
            $returnCriteria = new Criteria();
        }

        return $ad;

        
        // creation of the outward proposal
        $response = $this->dataProvider->post($proposal);
        if ($response->getCode() != 201) {
            return $response->getValue();
        }
        $proposalOutward = $response->getValue();

        // proposal successfully created, we check if there's a return
        if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
            // creation of the return trip
            $proposalReturn = clone $proposal;
            if (isset($ad['communities'])) {
                foreach ($ad['communities'] as $community) {
                    $proposalReturn->addCommunity($community);
                }
            }
            // if there's a matching linked, it means the proposal we create may be the return trip of a "forced" matching proposal
            if ($proposalOutward->getMatchingLinked()) {
                $proposalReturn->setMatchingLinked($proposalOutward->getMatchingLinked()->getIri());
            }
            // if there's an ask linked, it means the proposal we create may be the return trip of a "forced" matching proposal, for which an ask has been created
            if ($proposalOutward->getAskLinked()) {
                $proposalReturn->setAskLinked($proposalOutward->getAskLinked()->getIri());
            }
            // we check if the proposal is private (usually if the proposal is created after a search)
            if (isset($ad['private']) && $ad['private']) {
                $proposalReturn->setPrivate(true);
            }
            // we check if there's a proposalID
            if (isset($ad['proposalId'])) {
                // there's a proposalId : we know that it's a match to force
                // as it's a return trip, this proposalId will be replaced by the linked proposalId
                $proposalReturn->setMatchingProposal(new Proposal($ad['proposalId']));
            }
            // we check if an formal ask has to be made after the creation of the proposal (usually if the proposal is created after a search)
            if (isset($ad['formalAsk'])) {
                $proposalReturn->setFormalAsk($ad['formalAsk']);
            }
            $criteriaReturn = new Criteria();
            $criteriaReturn->setDriver($ad['driver']);
            $criteriaReturn->setPassenger($ad['passenger']);
            $criteriaReturn->setSeats($ad['seats']);
            if (isset($ad['priceKm'])) {
                $criteriaReturn->setPriceKm($ad['priceKm']);
            }
            if (isset($ad['solidary'])) {
                $criteriaReturn->setSolidaryExclusive($ad['solidary']);
            }
            if (isset($ad['price'])) {
                $criteriaReturn->setPrice($ad['price']);
            }
            if (isset($ad['roundedPrice'])) {
                $criteriaReturn->setRoundedPrice($ad['roundedPrice']);
            }
            if (isset($ad['computedPrice'])) {
                $criteriaReturn->setComputedPrice($ad['computedPrice']);
            }
            if (isset($ad['computedRoundedPrice'])) {
                $criteriaReturn->setComputedRoundedPrice($ad['computedRoundedPrice']);
            }
            if (isset($ad['returnPrice'])) {
                $criteriaReturn->setPrice($ad['returnPrice']);
            }
            if (isset($ad['returnRoundedPrice'])) {
                $criteriaReturn->setRoundedPrice($ad['returnRoundedPrice']);
            }
            if (isset($ad['returnComputedPrice'])) {
                $criteriaReturn->setComputedPrice($ad['returnComputedPrice']);
            }
            if (isset($ad['returnComputedRoundedPrice'])) {
                $criteriaReturn->setComputedRoundedPrice($ad['returnComputedRoundedPrice']);
            }
            if (isset($ad['luggage'])) {
                $criteriaReturn->setLuggage($ad['luggage']);
            }
            if (isset($ad['bike'])) {
                $criteriaReturn->setBike($ad['bike']);
            }
            if (isset($ad['backSeats'])) {
                $criteriaReturn->setBackSeats($ad['backSeats']);
            }
            $proposalReturn->setType(Proposal::TYPE_RETURN);
            $proposalReturn->setCriteria($criteriaReturn);
            if ($ad['regular']) {
                // regular
                $criteriaReturn->setFrequency(Criteria::FREQUENCY_REGULAR);
                if (isset($ad['fromDate'])) {
                    $criteriaReturn->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['fromDate']));
                } else {
                    $criteriaReturn->setFromDate(new \Datetime());
                }
                if (isset($ad['toDate'])) {
                    $criteriaReturn->setToDate(\DateTime::createFromFormat('Y-m-d', $ad['toDate']));
                }
                foreach ($ad['schedules'] as $schedule) {
                    if (isset($schedule['returnTime']) && $schedule['returnTime'] != '') {
                        if (isset($schedule['mon']) && $schedule['mon']) {
                            $criteriaReturn->setMonCheck(true);
                            $criteriaReturn->setMonTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setMonMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['tue']) && $schedule['tue']) {
                            $criteriaReturn->setTueCheck(true);
                            $criteriaReturn->setTueTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setTueMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['wed']) && $schedule['wed']) {
                            $criteriaReturn->setWedCheck(true);
                            $criteriaReturn->setWedTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setWedMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['thu']) && $schedule['thu']) {
                            $criteriaReturn->setThuCheck(true);
                            $criteriaReturn->setThuTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setThuMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['fri']) && $schedule['fri']) {
                            $criteriaReturn->setFriCheck(true);
                            $criteriaReturn->setFriTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setFriMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['sat']) && $schedule['sat']) {
                            $criteriaReturn->setSatCheck(true);
                            $criteriaReturn->setsatTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setSatMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['sun']) && $schedule['sun']) {
                            $criteriaReturn->setSunCheck(true);
                            $criteriaReturn->setSunTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setSunMarginDuration($this->marginTime);
                        }
                    }
                }
            } else {
                // punctual
                $criteriaReturn->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                $criteriaReturn->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['returnDate']));
                $criteriaReturn->setFromTime(\DateTime::createFromFormat('H:i', $ad['returnTime']));
                $criteriaReturn->setMarginDuration($this->marginTime);
            }
        
            // Waypoints
            // We use the waypoints in reverse order if return trip
            // /!\ for now we assume that the return trip uses the same waypoints as the outward) /!\
            $reversedWaypoints = [];
            $nbWaypoints = count($proposal->getWaypoints());
            // we need to get the waypoints in reverse order
            // we will read the waypoints a first time to create an array with the position as index
            $aWaypoints = [];
            foreach ($proposal->getWaypoints() as $proposalWaypoint) {
                $aWaypoints[$proposalWaypoint->getPosition()] = $proposalWaypoint;
            }
            // we sort the array by key
            ksort($aWaypoints);
            // our array is ordered by position, we read it backwards
            $reversedWaypoints = array_reverse($aWaypoints);
            
            $proposalReturn->setCriteria($criteriaReturn);
            foreach ($reversedWaypoints as $pos=>$proposalWaypoint) {
                $waypoint = clone $proposalWaypoint;
                $waypoint->setPosition($pos);
                $waypoint->setDestination(false);
                // address
                $waypoint->setAddress(clone $proposalWaypoint->getAddress());
                if ($pos == ($nbWaypoints-1)) {
                    $waypoint->setDestination(true);
                }
                $proposalReturn->addWaypoint($waypoint);
            }

            // link
            $proposalReturn->setProposalLinked($proposalOutward->getIri());

            // creation of the return proposal
            $response = $this->dataProvider->post($proposalReturn);
            if ($response->getCode() != 201) {
                return $response->getValue();
            }
            
            // we set the linked proposal as the outward proposal was returned before the linked proposal was created...
            $proposalOutward->setProposalLinked($response->getValue()->getId());
        }
 
        return $proposalOutward;


        $ad->setComment("ok");
        return $ad;
    }
}
