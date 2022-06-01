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

use App\Geography\Interfaces\GeorouterInterface;
use App\Geography\RouterProvider\GraphhopperProvider;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The routing service.
 *
 * This service calls routing engines in order to get the route between places.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoRouter
{
    /**
     * Georouter provider
     *
     * @var GeorouterInterface
     */
    private $router;

    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri, string $type, string $batchScriptPath, string $batchScriptArgs, string $batchTemp, LoggerInterface $logger, TranslatorInterface $translator)
    {
        switch ($type) {
            case 'graphhopper':
                $this->router = new GraphhopperProvider($uri, $batchScriptPath, $batchScriptArgs, $batchTemp, $logger, $translator);
                break;
        }
    }

    /**
     * Get the routes alternative between two or more addresses.
     *
     * @param array $addresses[]        The array of addresses (representing one route)
     * @param boolean $detailDuration   Set to true to get the duration between 2 points
     * @param boolean $pointsOnly       Set to true to get the points only
     * @param boolean|null $returnType  Set the return type
     * @param boolean|null $avoidToll   Set to true to avoid toll
     * @return array                    The routes found
     */
    public function getRoutes(array $addresses, bool $detailDuration=false, bool $pointsOnly = false, ?int $returnType = null, ?bool $avoidToll = null): array
    {
        $this->router->setDetailDuration($detailDuration);
        $this->router->setPointsOnly($pointsOnly);
        if (!is_null($returnType)) {
            $this->router->setReturnType($returnType);
        }
        if (!is_null($avoidToll)) {
            $this->router->setAvoidToll($avoidToll);
        }
        return $this->router->getDirections($addresses, GeorouterInterface::MODE_SYNC);
    }

    /**
     * Get all the routes alternative between two or more addresses, async.
     *
     * @param array $addresses[]        The array of addresses, indexed by owner id (representing all the routes to send by the async request)
     * @param boolean $detailDuration   Set to true to get the duration between 2 points
     * @param boolean $pointsOnly       Set to true to get the points only
     * @param boolean|null $returnType  Set the return type
     * @param boolean|null $avoidToll   Set to true to avoid toll
     * @return array                    The routes found
     */
    public function getAsyncRoutes(array $addresses, bool $detailDuration=false, bool $pointsOnly = false, ?int $returnType = null, ?bool $avoidToll = null): array
    {
        $this->router->setDetailDuration($detailDuration);
        $this->router->setPointsOnly($pointsOnly);
        if (!is_null($returnType)) {
            $this->router->setReturnType($returnType);
        }
        if (!is_null($avoidToll)) {
            $this->router->setAvoidToll($avoidToll);
        }
        return $this->router->getDirections($addresses, GeorouterInterface::MODE_ASYNC);
    }

    /**
     * Get multiple routes alternative between two or more addresses (async).
     * Different than getAsyncRoutes which represent the routes alternatives for a single direction,
     * here we search for multiple directions at once.
     *
     * @param array $addresses          The array of addresses, indexed by owner id (representing all the routes to send by the async request)
     * @param boolean $detailDuration   Set to true to get the duration between 2 points
     * @param boolean $pointsOnly       Set to true to get the points only
     * @param boolean|null $returnType  Set the return type
     * @param boolean|null $avoidToll   Set to true to avoid toll
     * @return array                    The routes found
     */
    public function getMultipleAsyncRoutes(array $addresses, bool $detailDuration=false, bool $pointsOnly = false, ?int $returnType = null, ?bool $avoidToll = null): array
    {
        $this->router->setDetailDuration($detailDuration);
        $this->router->setPointsOnly($pointsOnly);
        if (!is_null($returnType)) {
            $this->router->setReturnType($returnType);
        }
        if (!is_null($avoidToll)) {
            $this->router->setAvoidToll($avoidToll);
        }
        return $this->router->getMultipleDirections($addresses, GeorouterInterface::MODE_MULTIPLE_ASYNC);
    }
}
