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
use App\Geography\Entity\Zone;

/**
 * Zone management service.
 *
 * This service gets the zone and nearby zones for routes (list of addresses) and points (address).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ZoneManager
{
    /**
     * Get the zones for a list of addresses.
     *
     * @param array $addresses[]    The array of addresses
     * @param float $precision      The precision of the grid in degrees
     * @param int $deep             The deepness of near zones to retrieve (0 = only the zone, not the near zones)
     * @return array                The zones concerned by the addresses
     * @return array|NULL
     */
    public function getZonesForAddresses(array $addresses, float $precision, int $deep=0): ?array
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
     * @param int $deep         The deepness of near zones to retrieve (0 = only the zone, not the near zones)
     * @return array|NULL       The zones concerned by the address
     */
    public function getZonesForAddress(Address $address, float $precision, int $deep = 0): ?array
    {
        // we transform longitude and latitude to keep calculation simple : 
        // - longitude > 0 => no change
        // - longitude < 0 => we add 360 (-1 will become 359, -179 will become 181...)
        // - we add 90 to the latitude to keep positive values : new latitude now goes from 0 to 180 instead of -90 to 90
        $longitude = ((float)$address->getLongitude()<0) ? 360+(float)$address->getLongitude() : (float)$address->getLongitude();
        $latitude = 90+(float)$address->getLatitude();

        echo "longitude = $longitude<br />";
        echo "latitude = $latitude<br />";

        // we search the col and row for the gps point
        $col = (int)($longitude*(1/$precision)+1);
        $row = (int)($latitude*(1/$precision));

        echo "col = $col<br />";
        echo "row = $row<br />";
        
        // we search the zone
        $zone = $col+360*$row;

        $zones[] = $zone;
        if ($deep == 0) {
            return $zones;
        } else {
            $nzones = [];
            $nearbyZones = $this->getNear($col, $row, $nzones, $deep);
            $zones = array_unique(array_merge($zones, $nearbyZones),SORT_REGULAR);
            sort($zones);
            return $zones;
        }
    }

    /**
     * Get near zones.
     *
     * @param int $col      The col of the zone in the grid
     * @param int $row      The row of the zone in the grid
     * @param array $zones  The array that contains the nearby zones
     * @param int $deep     The deepness of the search (1 = direct nearby zones, 2 = nearby zone and their nearby zones, etc...)
     * @return array|NULL   The list of nearby zones.
     */
    public function getNear(int $col, int $row, array $zones, int $deep): ?array
    {
        if ($deep>0) {
            $nearZones = [
                $col-1+(360*($row+1)),  // X1
                $col+(360*($row+1)),    // X2
                $col+1+(360*($row+1)),  // X3
                $col-1+(360*$row),      // X4
                $col+1+(360*$row),      // X5
                $col-1+(360*($row-1)),  // X6
                $col+(360*($row-1)),    // X7
                $col+1+(360*($row-1)),  // X8
            ];
            $zones = array_unique(array_merge($zones,$nearZones),SORT_REGULAR);
            foreach ($nearZones as $nzone) {
                // completer pour trouver col et row
                //$zones = array_unique(array_merge($zones, $this->getNear($col,$row,$zones,$deep-1)),SORT_REGULAR);
            }
        }
        return $zones;
    }
    
}