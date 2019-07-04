<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Service;

use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Waypoint;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * Ad management service.
 */
class AdManager
{
    private $proposalManager;

    /**
     * Constructor.
     *
     * @param ProposalManager $proposalManager
     */
    public function __construct(ProposalManager $proposalManager, CommunityManager $communityManager)
    {
        $this->proposalManager = $proposalManager;
        $this->communityManager = $communityManager;
    }

    /**
     * Create a proposal from an ad.
     * If it's a return trip, 2 proposals will be created.
     */
    public function createProposalFromAd(Ad $ad)
    {

        // OUTWARD
        $proposal = new Proposal();
        $proposal->setType($ad->getType() == Ad::TYPE_ONE_WAY ? Proposal::TYPE_ONE_WAY : Proposal::TYPE_OUTWARD);
        $proposal->setComment($ad->getComment());
        $proposal->setUser($ad->getUser());
//        récupération des communités
        $community = $this->communityManager->getCommunity($ad->getCommunity());
        $proposal->addCommunity($community);

        // creation of the criteria
        $criteria = new Criteria();
        if ($ad->getRole() == Ad::ROLE_BOTH || $ad->getRole() == Ad::ROLE_DRIVER) {
            $criteria->setDriver(true);
        }
        if ($ad->getRole() == Ad::ROLE_BOTH || $ad->getRole() == Ad::ROLE_PASSENGER) {
            $criteria->setPassenger(true);
        }
        $criteria->setPriceKm($ad->getPrice());
        
        // For regular Trip : get time and margin for each day
        $ad->setOutwardMonTime($ad->getOutwardMonTime());
        $ad->setOutwardTueTime($ad->getOutwardTueTime());
        $ad->setOutwardWedTime($ad->getOutwardWedTime());
        $ad->setOutwardThuTime($ad->getOutwardThuTime());
        $ad->setOutwardFriTime($ad->getOutwardFriTime());
        $ad->setOutwardSatTime($ad->getOutwardSatTime());
        $ad->setOutwardSunTime($ad->getOutwardSunTime());
        $ad->setOutwardMonMargin($ad->getOutwardMonMargin());
        $ad->setOutwardTueMargin($ad->getOutwardTueMargin());
        $ad->setOutwardWedMargin($ad->getOutwardWedMargin());
        $ad->setOutwardThuMargin($ad->getOutwardThuMargin());
        $ad->setOutwardFriMargin($ad->getOutwardFriMargin());
        $ad->setOutwardSatMargin($ad->getOutwardSatMargin());
        $ad->setOutwardSunMargin($ad->getOutwardSunMargin());
        
        $criteria->setFrequency($ad->getFrequency());
        if ($ad->getFrequency() == Ad::FREQUENCY_PUNCTUAL) {
            $criteria->setFromDate($ad->getOutwardDate());
            $criteria->setFromTime(\DateTime::createFromFormat('H:i', $ad->getOutwardTime()));
            $criteria->setMarginDuration($ad->getOutwardMargin());
        } else {
            $criteria->setFromDate($ad->getFromDate());
            $criteria->setToDate($ad->getToDate());
            $criteria->setMonCheck($ad->getOutwardMonTime()<>null);
            if ($ad->getOutwardMonTime()) {
                $criteria->setMonTime(\DateTime::createFromFormat('H:i', $ad->getOutwardMonTime()));
                $criteria->setMonMarginDuration($ad->getOutwardMonMargin());
            }
            $criteria->setTueCheck($ad->getOutwardTueTime()<>null);
            if ($ad->getOutwardTueTime()) {
                $criteria->setTueTime(\DateTime::createFromFormat('H:i', $ad->getOutwardTueTime()));
                $criteria->setTueMarginDuration($ad->getOutwardTueMargin());
            }
            $criteria->setWedCheck($ad->getOutwardWedTime()<>null);
            if ($ad->getOutwardWedTime()) {
                $criteria->setWedTime(\DateTime::createFromFormat('H:i', $ad->getOutwardWedTime()));
                $criteria->setWedMarginDuration($ad->getOutwardWedMargin());
            }
            $criteria->setThuCheck($ad->getOutwardThuTime()<>null);
            if ($ad->getOutwardThuTime()) {
                $criteria->setThuTime(\DateTime::createFromFormat('H:i', $ad->getOutwardThuTime()));
                $criteria->setThuMarginDuration($ad->getOutwardThuMargin());
            }
            $criteria->setFriCheck($ad->getOutwardFriTime()<>null);
            if ($ad->getOutwardFriTime()) {
                $criteria->setFriTime(\DateTime::createFromFormat('H:i', $ad->getOutwardFriTime()));
                $criteria->setFriMarginDuration($ad->getOutwardFriMargin());
            }
            $criteria->setSatCheck($ad->getOutwardSatTime()<>null);
            if ($ad->getOutwardSatTime()) {
                $criteria->setSatTime(\DateTime::createFromFormat('H:i', $ad->getOutwardSatTime()));
                $criteria->setSatMarginDuration($ad->getOutwardSatMargin());
            }
            $criteria->setSunCheck($ad->getOutwardSunTime()<>null);
            if ($ad->getOutwardSunTime()) {
                $criteria->setSunTime(\DateTime::createFromFormat('H:i', $ad->getOutwardSunTime()));
                $criteria->setSunMarginDuration($ad->getOutwardSunMargin());
            }
        }
        
        $waypointOrigin = new Waypoint();
        $originAddress = new Address();
        $originAddress->setStreetAddress($ad->getOriginStreetAddress());
        $originAddress->setPostalCode($ad->getOriginPostalCode());
        $originAddress->setAddressLocality($ad->getOriginAddressLocality());
        $originAddress->setAddressCountry($ad->getOriginAddressCountry());
        $originAddress->setLatitude($ad->getOriginLatitude());
        $originAddress->setLongitude($ad->getOriginLongitude());
        $waypointOrigin->setAddress($originAddress);
        $waypointOrigin->setPosition(0);
        $waypointOrigin->setDestination(false);

        $waypointDestination = new Waypoint();
        $destinationAddress = new Address();
        $destinationAddress->setStreetAddress($ad->getDestinationStreetAddress());
        $destinationAddress->setPostalCode($ad->getDestinationPostalCode());
        $destinationAddress->setAddressLocality($ad->getDestinationAddressLocality());
        $destinationAddress->setAddressCountry($ad->getDestinationAddressCountry());
        $destinationAddress->setLatitude($ad->getDestinationLatitude());
        $destinationAddress->setLongitude($ad->getDestinationLongitude());
        $waypointDestination->setAddress($destinationAddress);
        $waypointDestination->setPosition(1);
        $waypointDestination->setDestination(true);

        $proposal->setCriteria($criteria);
        $proposal->addWaypoint($waypointOrigin);
        $proposal->addWaypoint($waypointDestination);

        // creation of the outward proposal
        if (!$proposalOutward = $this->proposalManager->createProposal($proposal)) {
            return false;
        }
        
        if ($ad->getType() == Ad::TYPE_RETURN_TRIP) {

            // Fro Regular Trips on return : get time and margin for each day
            $ad->setReturnMonTime($ad->getReturnMonTime());
            $ad->setReturnTueTime($ad->getReturnTueTime());
            $ad->setReturnWedTime($ad->getReturnWedTime());
            $ad->setReturnThuTime($ad->getReturnThuTime());
            $ad->setReturnFriTime($ad->getReturnFriTime());
            $ad->setReturnSatTime($ad->getReturnSatTime());
            $ad->setReturnSunTime($ad->getReturnSunTime());
            $ad->setReturnMonMargin($ad->getReturnMonMargin());
            $ad->setReturnTueMargin($ad->getReturnTueMargin());
            $ad->setReturnWedMargin($ad->getReturnWedMargin());
            $ad->setReturnThuMargin($ad->getReturnThuMargin());
            $ad->setReturnFriMargin($ad->getReturnFriMargin());
            $ad->setReturnSatMargin($ad->getReturnSatMargin());
            $ad->setReturnSunMargin($ad->getReturnSunMargin());
            
            // creation of the return trip
            $proposalReturn = clone $proposal;
            $criteriaReturn = clone $criteria;
            if ($ad->getFrequency() == Ad::FREQUENCY_PUNCTUAL) {
                $criteriaReturn->setFromDate($ad->getOutwardDate());
                $criteriaReturn->setFromTime(\DateTime::createFromFormat('H:i', $ad->getReturnTime()));
                $criteriaReturn->setMarginDuration($ad->getReturnMargin());
            } else {
                $criteriaReturn->setFromDate($ad->getFromDate());
                $criteriaReturn->setToDate($ad->getToDate());
                $criteriaReturn->setMonCheck($ad->getReturnMonTime()<>null);
                if ($ad->getReturnMonTime()) {
                    $criteriaReturn->setMonTime(\DateTime::createFromFormat('H:i', $ad->getReturnMonTime()));
                    $criteriaReturn->setMonMarginDuration($ad->getReturnMonMargin());
                }
                $criteriaReturn->setTueCheck($ad->getReturnTueTime()<>null);
                if ($ad->getReturnTueTime()) {
                    $criteriaReturn->setTueTime(\DateTime::createFromFormat('H:i', $ad->getReturnTueTime()));
                    $criteriaReturn->setTueMarginDuration($ad->getReturnTueMargin());
                }
                $criteriaReturn->setWedCheck($ad->getReturnWedTime()<>null);
                if ($ad->getReturnWedTime()) {
                    $criteriaReturn->setWedTime(\DateTime::createFromFormat('H:i', $ad->getReturnWedTime()));
                    $criteriaReturn->setWedMarginDuration($ad->getReturnWedMargin());
                }
                $criteriaReturn->setThuCheck($ad->getReturnThuTime()<>null);
                if ($ad->getReturnThuTime()) {
                    $criteriaReturn->setThuTime(\DateTime::createFromFormat('H:i', $ad->getReturnThuTime()));
                    $criteriaReturn->setThuMarginDuration($ad->getReturnThuMargin());
                }
                $criteriaReturn->setFriCheck($ad->getReturnFriTime()<>null);
                if ($ad->getReturnFriTime()) {
                    $criteriaReturn->setFriTime(\DateTime::createFromFormat('H:i', $ad->getReturnFriTime()));
                    $criteriaReturn->setFriMarginDuration($ad->getReturnFriMargin());
                }
                $criteriaReturn->setSatCheck($ad->getReturnSatTime()<>null);
                if ($ad->getReturnSatTime()) {
                    $criteriaReturn->setSatTime(\DateTime::createFromFormat('H:i', $ad->getReturnSatTime()));
                    $criteriaReturn->setSatMarginDuration($ad->getReturnSatMargin());
                }
                $criteriaReturn->setSunCheck($ad->getReturnSunTime()<>null);
                if ($ad->getReturnSunTime()) {
                    $criteriaReturn->setSunTime(\DateTime::createFromFormat('H:i', $ad->getReturnSunTime()));
                    $criteriaReturn->setSunMarginDuration($ad->getReturnSunMargin());
                }
            }

            $proposalReturn->setCriteria($criteriaReturn);

            // the waypoints in reverse order if return trip
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
            
            $proposalReturn->setType(Proposal::TYPE_RETURN);
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
            $proposalReturn = $this->proposalManager->createProposal($proposalReturn);
        }
        return $proposalOutward;
    }
}
