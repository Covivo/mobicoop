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

use App\Address\Entity\Address;
use App\Geography\Entity\Zone;
use App\Geography\Entity\Near;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Zone management service.
 *
 * This service gets the zone and nearby zones for routes and points.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ZoneManager
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Get the zones for a list of addresses.
     *
     * @param array $addresses[]    The array of addresses
     * @return array                The zones concerned by the addresses
     * @param int $deep             The deepness of near zones to retrieve (0 = only the zone, not the near zones)
     * @return array|NULL
     */
    public function getZonesForAddresses(array $addresses, int $deep=0): ?array
    {
        $zones = [];
        $baseLatitude = -1000;  // we are sure that this value doesn't exist
        $baseLongitude = -1000; //
        foreach ($addresses as $address) {
            // we search for the zone only if the base latitude or longitude has changed
            // /!\ we assume that the addresses are ordered /!\
            $baseAddressLongitude = $this->getBase($address->getLongitude());
            $baseAddressLatitude = $this->getBase($address->getLatitude());
            if (($baseAddressLatitude <> $baseLatitude) || ($baseAddressLongitude <> $baseLongitude)) {
                $baseLongitude = $baseAddressLongitude;
                $baseLatitude = $baseAddressLatitude;
                $zones = $this->getZonesForAddress($address,$deep);
            }
        }
        $return = [];
        foreach ($zones as $zone) {
            $return[] = $zone->getId();
        }
        return array_unique($return);
    }
    
    /**
     * Get the zones for an address.
     * 
     * @param Address $address  The address
     * @param int $deep         The deepness of near zones to retrieve (0 = only the zone, not the near zones)
     * @return array|NULL       The zones concerned by the address
     */
    public function getZonesForAddress(Address $address, int $deep = 0): ?array
    {
        $zones = [];
        $zone = $this->entityManager->getRepository(Zone::class)->findOneByLatitudeLongitude($address->getLatitude(),$address->getLongitude());
        $zones[] = $zone;
        if ($deep == 0) {
            return $zones;
        } else {
            $nearbyZones = $this->getNear($zone->getId(), $deep);
            return array_merge($zones,$nearbyZones);
        }
    }
    
    /**
     * Get near zones.
     * 
     * @param int $id       The id of the zone
     * @param int $deep     The deepness of the search (1 = direct nearby zones, 2 = nearby zone and their nearby zones, etc...)
     * @return array|NULL   The list of nearby zones.
     */
    public function getNear(int $id, int $deep): ?array
    {
        if ($zone = $this->entityManager->getRepository(Zone::class)->find($id)) {
            $azones = [];
            $near = self::near($zone, $azones,$deep);
            ksort($near);
            return $near;
        }
        return null;
    }
    
    private function near(Zone $zone, array $azones, int $deep): array 
    {
        $azones[$zone->getId()] = $zone;
        if ($deep>0) {
            $nearZones = $this->entityManager->getRepository(Near::class)->findBy([
                'zone1' => $zone
            ]);
            foreach ($nearZones as $near) {
                $azones = self::near($near->getZone2(), $azones,$deep-1);
            }
        }
        return $azones;
    }
    
    // search for the base of a value, for a given step
    // the base is the nearest lower value for a given step
    // eg. for a 0.5 step, the base value of 48.123543 is 48.000000
    // eg. for a 0.5 step, the base value of 48.823543 is 48.500000
    // @todo : see how to "globalize" the step value, that is chosen when we populate the zones
    private function getBase($value,$step=0.5) {
        if ($step == 0.5) return floor($value * 2) / 2;
    }
    
    
}
