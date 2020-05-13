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
 **************************/

namespace App\Geography\Service;

use App\Geography\Entity\Address;
use App\Geography\Repository\AddressRepository;
use App\Geography\Repository\TerritoryRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Address management service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AddressManager
{
    private $entityManager;
    private $territoryRepository;
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, TerritoryRepository $territoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->territoryRepository = $territoryRepository;
    }

    /**
     * Create or update territories for an Address.
     *
     * @param Address $address  The address
     * @param boolean $persist  Persit the address immediately
     * @return void             The address with its territories
     */
    public function createAddressTerritories(Address $address, bool $persist = false)
    {
        // first we remove all territories
        $address->removeTerritories();
        // then we search the territories
        if ($territories = $this->territoryRepository->findAddressTerritories($address)) {
            foreach ($territories as $territory) {
                $address->addTerritory($territory);
            }
        }
        if ($persist) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();
        }
        return $address;
    }

    /**
     * Create or update territories for an Address, only if the address is directly related to 'useful' entities :
     * - user (home)
     * - community
     * - event
     * - relay point
     * - proposal waypoint
     * - todo : add useful entities
     *
     * @param Address $address  The address
     * @param boolean $persist  Persit the address immediately
     * @return void             The address with its territories
     */
    public function createAddressTerritoriesForUsefulEntity(Address $address, bool $persist = false)
    {
        $createLink = false;
        if ($address->isHome()) {
            // home address
            $createLink = true;
        } elseif (!is_null($address->getCommunity())) {
            // community
            $createLink = true;
        } elseif (!is_null($address->getEvent())) {
            // event
            $createLink = true;
        } elseif (!is_null($address->getRelayPoint())) {
            // relay point
            $createLink = true;
        } elseif (!is_null($address->getWaypoint())) {
            // proposal waypoint
            if (!is_null($address->getWaypoint()->getProposal())) {
                $createLink = true;
            }
        }
        // todo : add any needed useful entity link
        if ($createLink) {
            return $this->createAddressTerritories($address, $persist);
        }
        if ($persist) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();
        }
        return $address;
    }
}
