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
 **************************/

namespace App\RdexPlus\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Geography\Entity\Address;
use App\Geography\Service\GeoTools;
use App\RdexPlus\Entity\Geopoint;
use App\RdexPlus\Entity\Price;
use App\RdexPlus\Entity\Waypoint;
use App\RdexPlus\Exception\RdexPlusException;
use App\RdexPlus\Resource\Journey;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Symfony\Component\Security\Core\Security;

/**
 * RDEX+ : Journey manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class JourneyManager
{
    private $adManager;
    private $userManager;
    private $geoTools;

    public function __construct(AdManager $adManager, UserManager $userManager, Security $security, GeoTools $geoTools)
    {
        $this->adManager = $adManager;
        $this->userManager = $userManager;
        $this->security = $security;
        $this->geoTools = $geoTools;
    }
    
    /**
     * Post an Ad from a RDEX+ Journey
     *
     * @param Journey $journey
     * @return Journey
     */
    public function createJourney(Journey $journey): Journey
    {
        // check is journey is valid
        $this->checkJourney($journey);

        $ad = $this->buildAdFromJourney($journey);
        
        //create ad
        $ad = $this->adManager->createAd($ad, true, false, false);

        // We set the id of the createdAd
        $journey->setId($ad->getId());
        return $journey;
    }


    /**
     * Check if a RDEX+ Journey is valid
     *
     * @param Journey $journey
     * @return boolean
     */
    public function checkJourney(Journey $journey): bool
    {
        // The User
        if (is_null($journey->getUser()) || is_null($journey->getUser()->getId())) {
            throw new RdexPlusException(RdexPlusException::USER_ID_REQUIRED);
        } else {
            if (is_null($this->user = $this->userManager->getUser((int)$journey->getUser()->getId()))) {
                throw new RdexPlusException(RdexPlusException::USER_UNKNOWN);
            }
        }

        // The frequency
        if (is_null($journey->getFrequency()) || !in_array($journey->getFrequency(), Journey::VALID_FREQUENCIES) || $journey->getFrequency()==Journey::FREQUENCY_BOTH) {
            throw new RdexPlusException(RdexPlusException::INVALID_FREQUENCY);
        }

        // The carpooler type (role)
        if (is_null($journey->getCarpoolerType()) || !in_array($journey->getCarpoolerType(), Journey::VALID_CARPOOLER_TYPES)) {
            throw new RdexPlusException(RdexPlusException::INVALID_CARPOOLER_TYPE);
        }

        // The price
        if (is_null($journey->getPrice()->getType()) || !in_array($journey->getPrice()->getType(), Price::VALID_TYPES)) {
            throw new RdexPlusException(RdexPlusException::INVALID_PRICE_TYPE);
        }

        // from
        if (is_null($journey->getFrom()->getLatitude()) || is_null($journey->getFrom()->getLongitude())) {
            throw new RdexPlusException(RdexPlusException::FROM_LATITUDE_LONGITUDE_REQUIRED);
        }

        // from
        if (is_null($journey->getTo()->getLatitude()) || is_null($journey->getTo()->getLongitude())) {
            throw new RdexPlusException(RdexPlusException::TO_LATITUDE_LONGITUDE_REQUIRED);
        }

        return true;
    }

    /**
     * Build an Ad from a RDEX+ Journey
     *
     * @param Journey $journey
     * @return Ad
     */
    public function buildAdFromJourney(Journey $journey): Ad
    {
        $ad = new Ad();
        $ad->setSearch(false);
        $ad->setCreatedDate(new \DateTime('now'));
        if ($this->security->getUser() instanceof User) {
            $ad->setPosterId($this->security->getUser()->getId());
        } else {
            $ad->setAppPosterId($this->security->getUser()->getId());
        }
        $ad->setUserId($journey->getUser()->getId());

        // Driver by default
        $ad->setRole(Ad::ROLE_DRIVER);
        if ($journey->getCarpoolerType() == Journey::CARPOOLER_TYPE_DRIVER) {
            $ad->setRole(Ad::ROLE_DRIVER);
        } elseif ($journey->getCarpoolerType() == Journey::CARPOOLER_TYPE_PASSENGER) {
            $ad->setRole(Ad::ROLE_PASSENGER);
        } elseif ($journey->getCarpoolerType() == Journey::CARPOOLER_TYPE_BOTH) {
            $ad->setRole(Ad::ROLE_DRIVER_OR_PASSENGER);
        }

        $ad->setOneWay(!$journey->getIsRoundTrip());

        // Punctual by default
        $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
        if ($journey->getFrequency() == Journey::FREQUENCY_PUNCTUAL) {
            $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
        } elseif ($journey->getFrequency() == Journey::FREQUENCY_REGULAR) {
            $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
        }

        // Price : We always use the price per kilometers. If the journey contains a fixed price, we need to compute it.
        if ($journey->getPrice()->getType() == Price::TYPE_VARIABLE) {
            $ad->setPriceKm($journey->getPrice()->getAmount());
        } elseif ($journey->getPrice()->getType() == Price::TYPE_FIXED) {
            // We compute the price per kilometers
            $distance = round(($this->geoTools->haversineGreatCircleDistance($journey->getFrom()->getLatitude(), $journey->getFrom()->getLongitude(), $journey->getTo()->getLatitude(), $journey->getTo()->getLongitude()) / 1000), 2);
            $ad->setPriceKm(round((((float)$journey->getPrice()->getAmount()) / $distance), 2));
        } elseif ($journey->getPrice()->getType() == Price::TYPE_FREE) {
            $ad->setPriceKm(0);
        }
        
        // Build the waypoints (Address)
        $outwardWaypoints = [];

        $outwardWaypoints[] = $this->buildAddressFromGeopoint($journey->getFrom());

        //echo $journey->getNumberOfWaypoints();die;
        if ($journey->getNumberOfWaypoints()>0) {
            foreach ($journey->getWaypoints() as $waypoint) {
                $outwardWaypoints[] = $this->buildAddressFromWaypoint($waypoint);
            }
        }

        $outwardWaypoints[] = $this->buildAddressFromGeopoint($journey->getTo());

        $ad->setOutwardWaypoints($outwardWaypoints);

        // Outward date
        $ourwardDate = new \DateTime("now");
        $ourwardDate->setTimestamp($journey->getOutward()->getDepartureDate());
        $ad->setOutwardDate($ourwardDate);

        // Return's Waypoints
        if (!$ad->isOneWay()) {
            $returnWaypoints[] = $this->buildAddressFromGeopoint($journey->getTo());
    
            // TO DO : treat waypoints
            if ($journey->getNumberOfWaypoints()>0) {
                for ($i = (count($journey->getWaypoints())) ; $i>0 ; $i--) {
                    $returnWaypoints[] = $this->buildAddressFromWaypoint($journey->getWaypoints()[$i-1]);
                }
            }
            
            $returnWaypoints[] = $this->buildAddressFromGeopoint($journey->getFrom());

            $ad->setReturnWaypoints($returnWaypoints);

            // Outward date
            $returnDate = new \DateTime("now");
            $returnDate->setTimestamp($journey->getReturn()->getDepartureDate());
            $ad->setReturnDate($returnDate);
        }


        // If punctual we set the outward time
        if ($ad->getFrequency()==Criteria::FREQUENCY_PUNCTUAL) {
            $ad->setOutwardTime($ourwardDate->format("H:i"));

            // If there is a return, we set the time
            if (!$ad->isOneWay()) {
                $ad->setReturnTime($returnDate->format("H:i"));
            }
        } else {
            // Regular, we build the schedules
        }

        return $ad;
    }

    /**
     * Build an Address from a Geopoint
     *
     * @param Geopoint $geopoint
     * @return Address
     */
    private function buildAddressFromGeopoint(Geopoint $geopoint): Address
    {
        $address = new Address();
        $address->setLatitude($geopoint->getLatitude());
        $address->setLongitude($geopoint->getLongitude());
        $address->setStreet($geopoint->getAddress());
        $address->setAddressLocality($geopoint->getCity());
        $address->setPostalCode($geopoint->getPostalCode());
        $address->setAddressCountry($geopoint->getCountry());
        $address->setName($geopoint->getPoiName());

        return $address;
    }

    /**
     * Build an Address from a RDEX+ Waypoint
     *
     * @param Waypoint $waypoint
     * @return Address
     */
    private function buildAddressFromWaypoint(Waypoint $waypoint): Address
    {
        $address = new Address();
        $address->setLatitude($waypoint->getLatitude());
        $address->setLongitude($waypoint->getLongitude());
        $address->setStreet($waypoint->getAddress());
        $address->setAddressLocality($waypoint->getCity());
        $address->setPostalCode($waypoint->getPostalCode());
        $address->setAddressCountry($waypoint->getCountry());
        $address->setName($waypoint->getPoiName());

        return $address;
    }
}
