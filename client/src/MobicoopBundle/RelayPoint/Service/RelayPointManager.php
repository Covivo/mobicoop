<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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
 */

namespace Mobicoop\Bundle\MobicoopBundle\RelayPoint\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPoint;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPointMap;

/**
 * Relay point management service.
 */
class RelayPointManager
{
    public const DEFAULT_MIN_LON = -180;
    public const DEFAULT_MIN_LAT = -90;
    public const DEFAULT_MAX_LON = 180;
    public const DEFAULT_MAX_LAT = 90;
    private $dataProvider;
    private $bbox_min_lon;
    private $bbox_min_lat;
    private $bbox_max_lon;
    private $bbox_max_lat;

    /**
     * Constructor.
     */
    public function __construct(DataProvider $dataProvider, array $bbox)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(RelayPoint::class);
        if (count($bbox) < 4) {
            $this->bbox_min_lon = self::DEFAULT_MIN_LON;
            $this->bbox_min_lat = self::DEFAULT_MIN_LAT;
            $this->bbox_max_lon = self::DEFAULT_MAX_LON;
            $this->bbox_max_lat = self::DEFAULT_MAX_LAT;
        } else {
            $this->bbox_min_lon = $bbox['min_lon'];
            $this->bbox_min_lat = $bbox['min_lat'];
            $this->bbox_max_lon = $bbox['max_lon'];
            $this->bbox_max_lat = $bbox['max_lat'];
        }
    }

    /**
     * Get relay points.
     *
     * @param bool       $official Get official relay points only
     * @param null|array $bbox     The bbox in which we want the relay points
     *
     * @return array the relay points found
     */
    public function getRelayPoints(bool $official = true, ?array $bbox = null)
    {
        if (is_array($bbox)) {
            list($this->bbox_min_lon, $this->bbox_min_lat, $this->bbox_max_lon, $this->bbox_max_lat) = $bbox;
        }

        $params = [
            'official' => $official,
            'address.latitude[between]' => $this->bbox_min_lat.'..'.$this->bbox_max_lat,
            'address.longitude[between]' => $this->bbox_min_lon.'..'.$this->bbox_max_lon,
            'perPage' => 999999,
            'relayPointType.id' => 1,
        ];
        $this->dataProvider->setClass(RelayPointMap::class);
        $response = $this->dataProvider->getCollection($params);
        if ($response->getCode() >= 200 && $response->getCode() <= 300) {
            return $response->getValue()->getMember();
        }

        return [];
    }
}
