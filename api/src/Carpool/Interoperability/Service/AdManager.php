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
        $classicAd->setOutwardDate($ad->getOutwardDate());
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
}
