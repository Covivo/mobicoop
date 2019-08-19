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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Controller;

use Mobicoop\Bundle\MobicoopBundle\Geography\Service\GeoRouterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * GeoRouter controller
 */

class GeoRouterController extends AbstractController
{
    /**
     * Retrieve all direction results of an input
     */
    public function geoRoute(GeoRouterManager $geoRouterManager, Request $request)
    {
        $points = ['points'=>$request->query->get('points')];
        if ($results = $geoRouterManager->getGeoRoutes($points)) {
            return $this->json($results);
        }
        return $this->json("error");
    }
}
