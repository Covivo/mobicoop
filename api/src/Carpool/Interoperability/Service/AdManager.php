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

namespace App\Carpool\Interoperability\Service;

use App\Carpool\Exception\BadRequestInteroperabilityCarpoolException;
use App\Carpool\Interoperability\Ressource\Ad;
use App\Carpool\Ressource\Ad as ClassicAd;
use App\Carpool\Service\AdManager as ClassicAdManager;
use App\Geography\Service\GeoTools;
use Symfony\Component\Security\Core\Security;

/**
 * Interoperability Ad manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AdManager
{
    private $classicAdManager;
    private $security;
    private $geoTools;

    public function __construct(ClassicAdManager $classicAdManager, Security $security, GeoTools $geoTools)
    {
        $this->classicAdManager = $classicAdManager;
        $this->security = $security;
        $this->geoTools = $geoTools;
    }

    /**
     * Create an Ad
     *
     * @param Ad $ad    The interoperabily Ad to create
     * @return Ad The interoperabily Ad created
     */
    public function createAd(Ad $ad): Ad
    {
        $this->valid($ad); // Validity check

        $classicAd = $this->buildClassicAdFromAd($ad);
        $classicAd = $this->classicAdManager->createAd($classicAd, true, false, false);

        return $this->buildAdFromClassicAd($classicAd);
    }

    /**
     * Build an interoperability User from a classic User entity
     *
     * @param ClassicAd $classicAd    The classic Ad
     * @return Ad The interoperability Ad
     */
    private function buildAdFromClassicAd(ClassicAd $classicAd): Ad
    {
        $ad = new Ad($classicAd->getId());

        return $ad;
    }

    /**
     * Build a classic Ad from an interoperability Ad
     *
     * @param Ad $ad    The interoperability Ad
     * @return ClassicAd   The classic Ad
     */
    private function buildClassicAdFromAd(Ad $ad): ClassicAd
    {
        $classicAd = new ClassicAd();
        $classicAd->setSearch(false);
        $classicAd->setCreatedDate(new \DateTime('now'));
        $classicAd->setAppPosterId($this->security->getUser()->getId());
        $classicAd->setUserId($ad->getUserId());

        $classicAd->setRole($ad->getRole());
        $classicAd->setOneWay($ad->isOneWay());
        $classicAd->setFrequency($ad->getFrequency());
        $classicAd->setOutwardWaypoints($ad->getOutwardWaypoints());
        $classicAd->setReturnWaypoints($ad->getReturnWaypoints());
        $classicAd->setOutwardDate($ad->getOutwardDate());
        $classicAd->setOutwardLimitDate($ad->getOutwardLimitDate());
        $classicAd->setReturnDate($ad->getReturnDate());
        $classicAd->setReturnLimitDate($ad->getReturnLimitDate());
        $classicAd->setSchedule($ad->getSchedule());
        
        // Punctual
        $classicAd->setOutwardTime($ad->getOutwardTime());
        $classicAd->setReturnTime($ad->getReturnTime());

        $classicAd->setSeatsDriver($ad->getSeats());
        $classicAd->setOutwardDriverPrice($ad->getPrice()/100);
        
        // We need to compute the price by kilometers
        $waypoints = $ad->getOutwardWaypoints();
        $latitudeFrom = $waypoints[0]['latitude'];
        $longitudeFrom = $waypoints[0]['longitude'];
        $latitudeTo = $waypoints[count($waypoints)-1]['latitude'];
        $longitudeTo = $waypoints[count($waypoints)-1]['longitude'];
        
        $distance = round(($this->geoTools->haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) / 1000), 2);
        $classicAd->setPriceKm(round((((float)$ad->getPrice() / 100) / $distance), 2));

        return $classicAd;
    }

    /**
     * Make several validity check before trying to register this Ad
     * Throw exceptions so it does'nt return any boolean
     *
     * @param Ad $ad    The Interoperability Ad to check
     * @return void
     */
    private function valid(Ad $ad)
    {
        // A few general validity checks
        if (!in_array($ad->getFrequency(), Ad::FREQUENCIES)) {
            throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::INVALID_FREQUENCY);
        }
        if (!in_array($ad->getRole(), Ad::ROLES)) {
            throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::INVALID_ROLE);
        }
        if (is_null($ad->getOutwardWaypoints()) || (is_array($ad->getOutwardWaypoints()) && count($ad->getOutwardWaypoints())==0)) {
            throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::NO_OUTWARD_WAYPOINTS);
        } else {
            // Validity check of the waypoints
            if (count($ad->getOutwardWaypoints())<2) {
                throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::INVALID_NUMBER_OUTWARD_WAYPOINT);
            }
            foreach ($ad->getOutwardWaypoints() as $outwardWaypoint) {
                if (!isset($outwardWaypoint['latitude']) || $outwardWaypoint['latitude']=="" || !isset($outwardWaypoint['longitude']) || $outwardWaypoint['longitude']=="") {
                    throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::INVALID_OUTWARD_WAYPOINT);
                }
            }
        }

        // A few validity checks for regular journeys
        if ($ad->getFrequency()==Ad::FREQUENCY_REGULAR) {
            // Need to have a schedule
            if (is_null($ad->getSchedule()) || (is_array($ad->getSchedule()) && count($ad->getSchedule())==0)) {
                throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::NO_SCHEDULE_FOR_REGULAR);
            }
        }
        // A few validity checks for punctual journeys
        if ($ad->getFrequency()==Ad::FREQUENCY_PUNCTUAL) {
            if (is_null($ad->getOutwardTime())) {
                throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::NO_OUTWARDTIME_FOR_PUNTUAL);
            }
        }
        
        // A few validity checks for round journeys
        if (!$ad->isOneWay()) {
            if (is_null($ad->getReturnWaypoints()) || (is_array($ad->getReturnWaypoints()) && count($ad->getReturnWaypoints())==0)) {
                throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::NO_RETURN_WAYPOINTS);
            }
            if ($ad->getFrequency()==Ad::FREQUENCY_PUNCTUAL) {
                if (is_null($ad->getReturnTime())) {
                    throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::NO_RETURNTIME_FOR_PUNTUAL);
                }
            }
            if (is_null($ad->getReturnWaypoints()) || (is_array($ad->getReturnWaypoints()) && count($ad->getReturnWaypoints())==0)) {
                throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::NO_RETURN_WAYPOINTS);
            } else {
                // Validity check of the waypoints
                if (count($ad->getReturnWaypoints())<2) {
                    throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::INVALID_NUMBER_RETURN_WAYPOINT);
                }
                foreach ($ad->getReturnWaypoints() as $returnWaypoints) {
                    if (!isset($returnWaypoints['latitude']) || $returnWaypoints['latitude']=="" || !isset($returnWaypoints['longitude']) || $returnWaypoints['longitude']=="") {
                        throw new BadRequestInteroperabilityCarpoolException(BadRequestInteroperabilityCarpoolException::INVALID_RETURN_WAYPOINT);
                    }
                }
            }
        }
    }
}
