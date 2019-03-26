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

namespace App\Geography\Service;

use App\DataProvider\Entity\GeoRouterProvider;
use App\Geography\Entity\Direction;

/**
 * The routing service.
 *
 * This service calls routing engines in order to get the route between places.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoRouter
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
        $this->collection = [];
    }  

    /**
     * Get the routes alternative between two or more addresses.
     *
     * @param array $addresses[]    The array of addresses
     * @return array                The routes found
     */
    public function getRoutes(array $addresses): ?array
    {
        $georouter = new GeoRouterProvider($this->uri);
        $params = [];
        $params['points'] = $addresses;
        $routes = $georouter->getCollection(Direction::class, '', $params);
        return $routes;
    }
}
