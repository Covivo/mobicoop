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
use Psr\Log\LoggerInterface;

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
    private $batchScriptPath;
    private $batchTemp;
    private $geoTools;
    private $logger;
    // to limit the return to points, not full addresses
    // used if we only need latitudes/longitudes
    private $pointsOnly;
    private $avoidMotorway;

    /**
     * Constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri, string $batchScriptPath, string $batchTemp, GeoTools $geoTools, LoggerInterface $logger)
    {
        $this->uri = $uri;
        $this->collection = [];
        $this->batchScriptPath = $batchScriptPath;
        $this->batchTemp = $batchTemp;
        $this->geoTools = $geoTools;
        $this->logger = $logger;
        $this->pointsOnly = false;
        $this->avoidMotorway = false;
    }

    public function setPointsOnly(bool $pointsOnly)
    {
        $this->pointsOnly = $pointsOnly;
    }

    public function setAvoidMotorway(bool $avoidMotorway)
    {
        $this->avoidMotorway = $avoidMotorway;
    }

    /**
     * Get the routes alternative between two or more addresses.
     *
     * @param array $addresses[]        The array of addresses (representing one route)
     * @param boolean $detailDuration   Set to true to get the duration between 2 points
     * @return array                    The routes found
     */
    public function getRoutes(array $addresses, bool $detailDuration=false): ?array
    {
        $georouter = new GeoRouterProvider($this->uri, $detailDuration, $this->pointsOnly, $this->avoidMotorway, $this->geoTools, $this->logger);
        $params = [];
        $params['points'] = $addresses;
        $routes = $georouter->getCollection(Direction::class, '', $params);
        return $routes;
    }
    
    /**
     * Get all the routes alternative between two or more addresses, async.
     *
     * @param array $addresses[]        The array of addresses, indexed by owner id (representing all the routes to send by the async request)
     * @param boolean $detailDuration   Set to true to get the duration between 2 points
     * @return array                    The routes found
     */
    public function getAsyncRoutes(array $addresses, bool $detailDuration=false): ?array
    {
        $georouter = new GeoRouterProvider($this->uri, $detailDuration, $this->pointsOnly, $this->avoidMotorway, $this->geoTools, $this->logger);
        $params = [];
        $params['arrayPoints'] = $addresses;
        $params['async'] = true;
        $routes = $georouter->getCollection(Direction::class, '', $params);
        return $routes;
    }

    /**
     * Get multiple routes alternative between two or more addresses (async).
     * Different than getAsyncRoutes which represent the routes alternatives for a single direction,
     * here we search for multiple directions at once.
     *
     * @param array $addresses[]        The array of addresses, indexed by owner id (representing all the routes to send by the async request)
     * @param boolean $detailDuration   Set to true to get the duration between 2 points
     * @return array                    The routes found
     */
    public function getMultipleAsyncRoutes(array $addresses, bool $detailDuration=false): ?array
    {
        $georouter = new GeoRouterProvider($this->uri, $detailDuration, $this->pointsOnly, $this->avoidMotorway, $this->geoTools, $this->logger);
        $params = [];
        $params['arrayPoints'] = $addresses;
        $params['multipleAsync'] = true;
        $params['batchScriptPath'] = $this->batchScriptPath;
        $params['batchTemp'] = $this->batchTemp;
        $routes = $georouter->getCollection(Direction::class, '', $params);
        return $routes;
    }
}
