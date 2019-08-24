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

use App\Carpool\Entity\Criteria;
use App\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;

/**
 * Proposal management service.
 */
class ProposalManager
{
    private $dataProvider;
    private $userManager;
    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider, UserManager $userManager)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Proposal::class);
        $this->userManager = $userManager;
    }
    
    /**
     * Create a proposal
     *
     * @param Proposal $proposal The proposal to create
     *
     * @return Proposal|null The proposal created or null if error.
     */
    public function createProposal(Proposal $proposal)
    {
        $response = $this->dataProvider->post($proposal);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Get all proposals for a user
     *
     * @return array|null The proposals found or null if not found.
     */
    public function getProposals(User $user)
    {
        // we will make the request on the User instead of the Proposal
        $this->dataProvider->setClass(User::class);
        $response = $this->dataProvider->getSubCollection($user->getId(), Proposal::class);
        return $response->getValue();
    }
    
    /**
     * Get a proposal for a user
     *
     * @param int $id
     * @return Proposal|null The proposal found or null if not found.
     */
    public function getProposal(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        return $response->getValue();
    }
    
    /**
     * Get all matchings for a user proposal
     *
     * @return array|null The matchings found or null if not found.
     */
    public function getMatchings(Proposal $proposal)
    {
        // we will make the request on the Matching instead of the Proposal
        if ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER) {
            $response = $this->dataProvider->getSubCollection($proposal->getId(), Matching::class, "matching_requests");
        } else {
            $response = $this->dataProvider->getSubCollection($proposal->getId(), Matching::class, "matching_offers");
        }
        return $response->getValue();
    }

    /**
     * Get all matchings for a search.
     *
     * @param float $origin_latitude        The origin latitude
     * @param float $origin_longitude       The origin longitude
     * @param float $destination_latitude   The destination latitude
     * @param float $destination_longitude  The destination longitude
     * @param \Datetime $date               The date and time in a Datetime object
     * @return array|null The matchings found or null if not found.
     */
    public function getMatchingsForSearch(float $origin_latitude, float $origin_longitude, float $destination_latitude, float $destination_longitude, \Datetime $date)
    {
        // we set the params
        $params = [
            "origin_latitude" => $origin_latitude,
            "origin_longitude" => $origin_longitude,
            "destination_latitude" => $destination_latitude,
            "destination_longitude" => $destination_longitude,
            "date" => $date->format('Y-m-d\TH:i:s\Z')
        ];
        // we call the special collection operation "search"
        $response = $this->dataProvider->getSpecialCollection("search", $params);
        return $response->getValue();
    }
    
    /**
     * Delete a proposal
     *
     * @param int $id The id of the proposal to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteProposal(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }

    /**
     * Create a proposal from an ad
     *
     * @param array $ad The data posted by the user
     * @param User $poster The poster of the ad
     * @return void
     */
    public function createProposalFromAd(array $ad, User $poster)
    {
        var_dump($ad);
        exit;
        $proposal = new Proposal();
        $criteria = new Criteria();

        if (isset($ad['user'])) {
            $user = $this->userManager->getUser($ad['user']);
            $proposal->setUser($user);
            $proposal->setUserDelegate($poster);
        } else {
            $proposal->setUser($poster);
        }
        $proposal->setType(Ad::TYPE_ONE_WAY);
        if ($ad['regular'] === "true") {
            // regular
            $proposal->setFrequency(Ad::FREQUENCY_REGULAR);
        } else {
            // punctual
            $proposal->setFrequency(Ad::FREQUENCY_PUNCTUAL);
            
            if ($ad['returnDate'] != '' && $ad['returnTime'] != '') {
                $proposal->setType(Ad::TYPE_RETURN_TRIP);
            }
        }

        /*            {
                "data":{
                "regular":false,
                "driver":true,
                "passenger":false,
                "origin":{
                "@id":"\/addresses\/999999999999",
                "@type":"Address",
                "id":999999999999,
                "houseNumber":null,
                "street":null,
                "streetAddress":null,
                "postalCode":null,
                "subLocality":null,
                "addressLocality":"Nancy",
                "localAdmin":"Nancy",
                "county":"Nancy",
                "macroCounty":"arrondissement de Nancy",
                "region":"Meurthe-et-Moselle",
                "macroRegion":"Grand Est",
                "addressCountry":"France",
                "countryCode":"FRA",
                "latitude":"48.690303",
                "longitude":"6.178289",
                "elevation":null,
                "geoJson":null,
                "name":null,
                "home":null,
                "displayLabel":"Nancy",
                "relayPoint":null,
                "icon":"mdi-map-marker",
                "displayedLabel":"Nancy",
                "selectedDisplayedLabel":"Nancy"
                },
                "destination":{
                "@id":"\/addresses\/999999999999",
                "@type":"Address",
                "id":999999999999,
                "houseNumber":null,
                "street":null,
                "streetAddress":null,
                "postalCode":null,
                "subLocality":null,
                "addressLocality":"Metz",
                "localAdmin":"Metz",
                "county":"Metz",
                "macroCounty":null,
                "region":"Moselle",
                "macroRegion":"Grand Est",
                "addressCountry":"France",
                "countryCode":"FRA",
                "latitude":"49.115164",
                "longitude":"6.17963",
                "elevation":null,
                "geoJson":null,
                "name":null,
                "home":null,
                "displayLabel":"Metz",
                "relayPoint":null,
                "icon":"mdi-map-marker",
                "displayedLabel":"Metz",
                "selectedDisplayedLabel":"Metz"
                },
                "waypoints":[
                {
                "visible":true,
                "address":{
                "@id":"\/addresses\/999999999999",
                "@type":"Address",
                "id":999999999999,
                "houseNumber":null,
                "street":null,
                "streetAddress":null,
                "postalCode":null,
                "subLocality":null,
                "addressLocality":"Pont-\u00e0-Mousson",
                "localAdmin":"Pont-\u00e0-Mousson",
                "county":"Pont-\u00e0-Mousson",
                "macroCounty":"arrondissement de Nancy",
                "region":"Meurthe-et-Moselle",
                "macroRegion":"Grand Est",
                "addressCountry":"France",
                "countryCode":"FRA",
                "latitude":"48.903005",
                "longitude":"6.053885",
                "elevation":null,
                "geoJson":null,
                "name":null,
                "home":null,
                "displayLabel":"Pont-\u00e0-Mousson",
                "relayPoint":null,
                "icon":"mdi-map-marker",
                "displayedLabel":"Pont-\u00e0-Mousson",
                "selectedDisplayedLabel":"Pont-\u00e0-Mousson"
                }
                }
                ],
                "outwardDate":"2019-08-24",
                "outwardTime":"12:00",
                "returnDate":"2019-08-25",
                "returnTime":"11:55",
                "seats":1,
                "luggage":true,
                "bike":true,
                "backSeats":true,
                "price":3.68,
                "message":"test message"
                }
            }*/

// original code from admanager
// OUTWARD
// $proposal = new Proposal();
// $proposal->setType($ad->getType() == Ad::TYPE_ONE_WAY ? Proposal::TYPE_ONE_WAY : Proposal::TYPE_OUTWARD);
// $proposal->setComment($ad->getComment());
// $proposal->setUser($ad->getUser());

// //        récupération des communautés
// if ($ad->getCommunity() !== null) {
//     $community = $this->communityManager->getCommunity($ad->getCommunity());
//     $proposal->addCommunity($community);
// }
// // creation of the criteria
// $criteria = new Criteria();
// if ($ad->getRole() == Ad::ROLE_BOTH || $ad->getRole() == Ad::ROLE_DRIVER) {
//     $criteria->setDriver(true);
// }
// if ($ad->getRole() == Ad::ROLE_BOTH || $ad->getRole() == Ad::ROLE_PASSENGER) {
//     $criteria->setPassenger(true);
// }
// $criteria->setPriceKm($ad->getPrice());

// // For regular Trip : get time and margin for each day
// $ad->setOutwardMonTime($ad->getOutwardMonTime());
// $ad->setOutwardTueTime($ad->getOutwardTueTime());
// $ad->setOutwardWedTime($ad->getOutwardWedTime());
// $ad->setOutwardThuTime($ad->getOutwardThuTime());
// $ad->setOutwardFriTime($ad->getOutwardFriTime());
// $ad->setOutwardSatTime($ad->getOutwardSatTime());
// $ad->setOutwardSunTime($ad->getOutwardSunTime());
// $ad->setOutwardMonMargin($ad->getOutwardMonMargin());
// $ad->setOutwardTueMargin($ad->getOutwardTueMargin());
// $ad->setOutwardWedMargin($ad->getOutwardWedMargin());
// $ad->setOutwardThuMargin($ad->getOutwardThuMargin());
// $ad->setOutwardFriMargin($ad->getOutwardFriMargin());
// $ad->setOutwardSatMargin($ad->getOutwardSatMargin());
// $ad->setOutwardSunMargin($ad->getOutwardSunMargin());

// $criteria->setFrequency($ad->getFrequency());
// if ($ad->getFrequency() == Ad::FREQUENCY_PUNCTUAL) {
//     $criteria->setFromDate($ad->getOutwardDate());
//     $criteria->setFromTime(\DateTime::createFromFormat('H:i', $ad->getOutwardTime()));
//     $criteria->setMarginDuration($ad->getOutwardMargin());
// } else {
//     $criteria->setFromDate($ad->getFromDate());
//     $criteria->setToDate($ad->getToDate());
//     $criteria->setMonCheck($ad->getOutwardMonTime()<>null);
//     if ($ad->getOutwardMonTime()) {
//         $criteria->setMonTime(\DateTime::createFromFormat('H:i', $ad->getOutwardMonTime()));
//         $criteria->setMonMarginDuration($ad->getOutwardMonMargin());
//     }
//     $criteria->setTueCheck($ad->getOutwardTueTime()<>null);
//     if ($ad->getOutwardTueTime()) {
//         $criteria->setTueTime(\DateTime::createFromFormat('H:i', $ad->getOutwardTueTime()));
//         $criteria->setTueMarginDuration($ad->getOutwardTueMargin());
//     }
//     $criteria->setWedCheck($ad->getOutwardWedTime()<>null);
//     if ($ad->getOutwardWedTime()) {
//         $criteria->setWedTime(\DateTime::createFromFormat('H:i', $ad->getOutwardWedTime()));
//         $criteria->setWedMarginDuration($ad->getOutwardWedMargin());
//     }
//     $criteria->setThuCheck($ad->getOutwardThuTime()<>null);
//     if ($ad->getOutwardThuTime()) {
//         $criteria->setThuTime(\DateTime::createFromFormat('H:i', $ad->getOutwardThuTime()));
//         $criteria->setThuMarginDuration($ad->getOutwardThuMargin());
//     }
//     $criteria->setFriCheck($ad->getOutwardFriTime()<>null);
//     if ($ad->getOutwardFriTime()) {
//         $criteria->setFriTime(\DateTime::createFromFormat('H:i', $ad->getOutwardFriTime()));
//         $criteria->setFriMarginDuration($ad->getOutwardFriMargin());
//     }
//     $criteria->setSatCheck($ad->getOutwardSatTime()<>null);
//     if ($ad->getOutwardSatTime()) {
//         $criteria->setSatTime(\DateTime::createFromFormat('H:i', $ad->getOutwardSatTime()));
//         $criteria->setSatMarginDuration($ad->getOutwardSatMargin());
//     }
//     $criteria->setSunCheck($ad->getOutwardSunTime()<>null);
//     if ($ad->getOutwardSunTime()) {
//         $criteria->setSunTime(\DateTime::createFromFormat('H:i', $ad->getOutwardSunTime()));
//         $criteria->setSunMarginDuration($ad->getOutwardSunMargin());
//     }
// }

// $waypointOrigin = new Waypoint();
// $originAddress = new Address();
// $originAddress->setStreetAddress($ad->getOriginStreetAddress());
// $originAddress->setPostalCode($ad->getOriginPostalCode());
// $originAddress->setAddressLocality($ad->getOriginAddressLocality());
// $originAddress->setAddressCountry($ad->getOriginAddressCountry());
// $originAddress->setLatitude($ad->getOriginLatitude());
// $originAddress->setLongitude($ad->getOriginLongitude());
// $waypointOrigin->setAddress($originAddress);
// $waypointOrigin->setPosition(0);
// $waypointOrigin->setDestination(false);

// $waypointDestination = new Waypoint();
// $destinationAddress = new Address();
// $destinationAddress->setStreetAddress($ad->getDestinationStreetAddress());
// $destinationAddress->setPostalCode($ad->getDestinationPostalCode());
// $destinationAddress->setAddressLocality($ad->getDestinationAddressLocality());
// $destinationAddress->setAddressCountry($ad->getDestinationAddressCountry());
// $destinationAddress->setLatitude($ad->getDestinationLatitude());
// $destinationAddress->setLongitude($ad->getDestinationLongitude());
// $waypointDestination->setAddress($destinationAddress);
// $waypointDestination->setPosition(1);
// $waypointDestination->setDestination(true);

// $proposal->setCriteria($criteria);
// $proposal->addWaypoint($waypointOrigin);
// $proposal->addWaypoint($waypointDestination);

// // creation of the outward proposal
// if (!$proposalOutward = $this->proposalManager->createProposal($proposal)) {
//     return false;
// }

// if ($ad->getType() == Ad::TYPE_RETURN_TRIP) {

//     // Fro Regular Trips on return : get time and margin for each day
//     $ad->setReturnMonTime($ad->getReturnMonTime());
//     $ad->setReturnTueTime($ad->getReturnTueTime());
//     $ad->setReturnWedTime($ad->getReturnWedTime());
//     $ad->setReturnThuTime($ad->getReturnThuTime());
//     $ad->setReturnFriTime($ad->getReturnFriTime());
//     $ad->setReturnSatTime($ad->getReturnSatTime());
//     $ad->setReturnSunTime($ad->getReturnSunTime());
//     $ad->setReturnMonMargin($ad->getReturnMonMargin());
//     $ad->setReturnTueMargin($ad->getReturnTueMargin());
//     $ad->setReturnWedMargin($ad->getReturnWedMargin());
//     $ad->setReturnThuMargin($ad->getReturnThuMargin());
//     $ad->setReturnFriMargin($ad->getReturnFriMargin());
//     $ad->setReturnSatMargin($ad->getReturnSatMargin());
//     $ad->setReturnSunMargin($ad->getReturnSunMargin());
    
//     // creation of the return trip
//     $proposalReturn = clone $proposal;
//     $criteriaReturn = clone $criteria;
//     if ($ad->getFrequency() == Ad::FREQUENCY_PUNCTUAL) {
//         $criteriaReturn->setFromDate($ad->getOutwardDate());
//         $criteriaReturn->setFromTime(\DateTime::createFromFormat('H:i', $ad->getReturnTime()));
//         $criteriaReturn->setMarginDuration($ad->getReturnMargin());
//     } else {
//         $criteriaReturn->setFromDate($ad->getFromDate());
//         $criteriaReturn->setToDate($ad->getToDate());
//         $criteriaReturn->setMonCheck($ad->getReturnMonTime()<>null);
//         if ($ad->getReturnMonTime()) {
//             $criteriaReturn->setMonTime(\DateTime::createFromFormat('H:i', $ad->getReturnMonTime()));
//             $criteriaReturn->setMonMarginDuration($ad->getReturnMonMargin());
//         }
//         $criteriaReturn->setTueCheck($ad->getReturnTueTime()<>null);
//         if ($ad->getReturnTueTime()) {
//             $criteriaReturn->setTueTime(\DateTime::createFromFormat('H:i', $ad->getReturnTueTime()));
//             $criteriaReturn->setTueMarginDuration($ad->getReturnTueMargin());
//         }
//         $criteriaReturn->setWedCheck($ad->getReturnWedTime()<>null);
//         if ($ad->getReturnWedTime()) {
//             $criteriaReturn->setWedTime(\DateTime::createFromFormat('H:i', $ad->getReturnWedTime()));
//             $criteriaReturn->setWedMarginDuration($ad->getReturnWedMargin());
//         }
//         $criteriaReturn->setThuCheck($ad->getReturnThuTime()<>null);
//         if ($ad->getReturnThuTime()) {
//             $criteriaReturn->setThuTime(\DateTime::createFromFormat('H:i', $ad->getReturnThuTime()));
//             $criteriaReturn->setThuMarginDuration($ad->getReturnThuMargin());
//         }
//         $criteriaReturn->setFriCheck($ad->getReturnFriTime()<>null);
//         if ($ad->getReturnFriTime()) {
//             $criteriaReturn->setFriTime(\DateTime::createFromFormat('H:i', $ad->getReturnFriTime()));
//             $criteriaReturn->setFriMarginDuration($ad->getReturnFriMargin());
//         }
//         $criteriaReturn->setSatCheck($ad->getReturnSatTime()<>null);
//         if ($ad->getReturnSatTime()) {
//             $criteriaReturn->setSatTime(\DateTime::createFromFormat('H:i', $ad->getReturnSatTime()));
//             $criteriaReturn->setSatMarginDuration($ad->getReturnSatMargin());
//         }
//         $criteriaReturn->setSunCheck($ad->getReturnSunTime()<>null);
//         if ($ad->getReturnSunTime()) {
//             $criteriaReturn->setSunTime(\DateTime::createFromFormat('H:i', $ad->getReturnSunTime()));
//             $criteriaReturn->setSunMarginDuration($ad->getReturnSunMargin());
//         }
//     }

//     $proposalReturn->setCriteria($criteriaReturn);

//     // the waypoints in reverse order if return trip
//     // /!\ for now we assume that the return trip uses the same waypoints as the outward) /!\
//     $reversedWaypoints = [];
//     $nbWaypoints = count($proposal->getWaypoints());
//     // we need to get the waypoints in reverse order
//     // we will read the waypoints a first time to create an array with the position as index
//     $aWaypoints = [];
//     foreach ($proposal->getWaypoints() as $proposalWaypoint) {
//         $aWaypoints[$proposalWaypoint->getPosition()] = $proposalWaypoint;
//     }
//     // we sort the array by key
//     ksort($aWaypoints);
//     // our array is ordered by position, we read it backwards
//     $reversedWaypoints = array_reverse($aWaypoints);
    
//     $proposalReturn->setType(Proposal::TYPE_RETURN);
//     $proposalReturn->setCriteria($criteriaReturn);
//     foreach ($reversedWaypoints as $pos=>$proposalWaypoint) {
//         $waypoint = clone $proposalWaypoint;
//         $waypoint->setPosition($pos);
//         $waypoint->setDestination(false);
//         // address
//         $waypoint->setAddress(clone $proposalWaypoint->getAddress());
//         if ($pos == ($nbWaypoints-1)) {
//             $waypoint->setDestination(true);
//         }
//         $proposalReturn->addWaypoint($waypoint);
//     }

//     // link
//     $proposalReturn->setProposalLinked($proposalOutward->getIri());


//     // creation of the return proposal
//     $proposalReturn = $this->proposalManager->createProposal($proposalReturn);
// }
// return $proposalOutward;
    }
}
