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

use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use App\Geography\Entity\Zone;

/**
 * Zone management service.
 *
 * This service gets the zone and nearby zones for routes (list of addresses) and points (address).
 * The whole world map can be considered as a grid, the precision of the grid can be parametered (1° longitude at the equator represents 111km, 1° latitude is always 111km)
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ZoneManager
{
    // zones precisions (in degrees) to generate when adding a direction
    public const THINNESSES = [
        1,
        0.5,
        0.25,
        0.125,
        //0.0625
    ];

    /**
     * Create the zones for a direction.
     *
     * @param Direction $direction The direction.
     * @return Direction The direction with the associated zones.
     */
    public function createZonesForDirection(Direction $direction): Direction
    {
        $zones = [];
        foreach (self::THINNESSES as $thinness) {
            // $zones[$thinness] would be simpler and better... but we can't use a float as a key with php (transformed to string)
            // so we use an inner value for thinness
            $zones[] = [
                'thinness' => $thinness,
                'crossed' => $this->getZonesForAddresses($direction->getPoints(), $thinness, 0)
            ];
        }

        foreach ($zones as $crossed) {
            foreach ($crossed['crossed'] as $zoneCrossed) {
                $zone = new Zone();
                $zone->setZoneid($zoneCrossed);
                $zone->setThinness($crossed['thinness']);
                $direction->addZone($zone);
            }
        }
        return $direction;
    }

    /**
     * Get the zones for a list of addresses.
     *
     * @param array $addresses[]    The array of addresses
     * @param float $precision      The precision of the grid in degrees
     * @param int $deep             The deepness of near zones to retrieve (0 = only the zone, not the near zones)
     * @return array                The zones concerned by the addresses
     * @return array|NULL
     */
    public function getZonesForAddresses(array $addresses, float $precision, int $deep=0): array
    {
        $zones = [];
        foreach ($addresses as $address) {
            $zones = array_merge($zones, $this->getZonesForAddress($address, $precision, $deep));
        }
        return array_unique($zones, SORT_REGULAR);
    }

    /**
     * Get the zones for an address.
     *
     * @param Address $address  The address
     * @param float $precision  The precision of the grid in degrees
     * @param int $deep         The deepness of near zones to retrieve (0 = only the current zone, not the near zones)
     * @return array|NULL       The zones concerned by the address
     */
    public function getZonesForAddress(Address $address, float $precision, int $deep = 0): ?array
    {
        $zones[] = $this->getZoneForAddress($address, $precision);
        if ($deep == 0) {
            return $zones;
        }

        $nearbyZones = $this->getNear($address, $precision, $deep);
        $zones = array_unique(array_merge($zones, $nearbyZones), SORT_REGULAR);
        sort($zones);
        return $zones;
    }

    private function getZoneForAddress(Address $address, float $precision): int
    {
        // we transform longitude and latitude to keep calculation simple :
        // - longitude > 0 => no change
        // - longitude < 0 => we add 360 (-1 will become 359, -179 will become 181...)
        // - we add 90 to the latitude to keep positive values : new latitude now goes from 0 to 180 instead of -90 to 90
        $longitude = ((float)$address->getLongitude()<0) ? 360+(float)$address->getLongitude() : (float)$address->getLongitude();
        $latitude = 90+(float)$address->getLatitude();

        // we search the col and row for the gps point
        $col = (int)($longitude*(1/$precision)+1);
        $row = (int)($latitude*(1/$precision));

        // we search the zone
        $zone = $col+360*$row;

        return $zone;
    }

    /**
     * Get near zones of an address.
     *
     * @param Address $address  The address
     * @param float $precision  The precision of the grid in degrees
     * @param int $deep         The deepness of the search (1 = direct nearby zones, 2 = nearby zone and their nearby zones, etc...)
     * @return array|NULL       The list of nearby zones.
     */
    public function getNear(Address $address, float $precision, int $deep): ?array
    {
        if ($deep<0) {
            return null;
        }

        // we search for nearby zones
        // we nearby zones of the XX zone are defined like this :
        // X1 X2 X3
        // X4 XX X5
        // X6 X7 X8

        // we search the GPS point of each X? zone
        $x1 = new Address();
        $x2 = new Address();
        $x3 = new Address();
        $x4 = new Address();
        $x5 = new Address();
        $x6 = new Address();
        $x7 = new Address();
        $x8 = new Address();
        $x1->setLatitude((string)((float)($address->getLatitude())+$precision));
        $x1->setLongitude((string)((float)($address->getLongitude())-$precision));
        $x2->setLatitude((string)((float)($address->getLatitude())+$precision));
        $x2->setLongitude($address->getLongitude());
        $x3->setLatitude((string)((float)($address->getLatitude())+$precision));
        $x3->setLongitude((string)((float)($address->getLongitude())+$precision));
        $x4->setLatitude($address->getLatitude());
        $x4->setLongitude((string)((float)($address->getLongitude())-$precision));
        $x5->setLatitude($address->getLatitude());
        $x5->setLongitude((string)((float)($address->getLongitude())+$precision));
        $x6->setLatitude((string)((float)($address->getLatitude())-$precision));
        $x6->setLongitude((string)((float)($address->getLongitude())-$precision));
        $x7->setLatitude((string)((float)($address->getLatitude())-$precision));
        $x7->setLongitude($address->getLongitude());
        $x8->setLatitude((string)((float)($address->getLatitude())-$precision));
        $x8->setLongitude((string)((float)($address->getLongitude())+$precision));

        $nearX1 = $this->getZonesForAddress($x1, $precision, $deep-1);
        $nearX2 = $this->getZonesForAddress($x2, $precision, $deep-1);
        $nearX3 = $this->getZonesForAddress($x3, $precision, $deep-1);
        $nearX4 = $this->getZonesForAddress($x4, $precision, $deep-1);
        $nearX5 = $this->getZonesForAddress($x5, $precision, $deep-1);
        $nearX6 = $this->getZonesForAddress($x6, $precision, $deep-1);
        $nearX7 = $this->getZonesForAddress($x7, $precision, $deep-1);
        $nearX8 = $this->getZonesForAddress($x8, $precision, $deep-1);

        return array_merge($nearX1, $nearX2, $nearX3, $nearX4, $nearX5, $nearX6, $nearX7, $nearX8);
    }
}
