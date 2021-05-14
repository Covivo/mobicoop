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

namespace App\Carpool\Service;

use App\Carpool\Ressource\Ad;
use App\Geography\Entity\Address;
use App\Geography\Service\GeoRouter;

/**
 * Anti-Fraud system manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AntiFraudManager
{
    private $geoRouter;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        GeoRouter $geoRouter
    ) {
        $this->geoRouter = $geoRouter;
    }

    public function validAd(Ad $ad)
    {
        $addressesToValidate = [];
        foreach ($ad->getOutwardWaypoints() as $pointToValidate) {
            $waypointToValidate = new Address();
            $waypointToValidate->setLatitude($pointToValidate['latitude']);
            $waypointToValidate->setLongitude($pointToValidate['longitude']);
            $addressesToValidate[] = $waypointToValidate;
        }
        

        $route = $this->geoRouter->getRoutes($addressesToValidate, false, true);
        var_dump($route);
        die;
    }
}
