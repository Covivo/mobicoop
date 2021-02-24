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

namespace App\RelayPoint\Admin\Service;

use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Exception\RelayPointException;
use Doctrine\ORM\EntityManagerInterface;
use App\Geography\Entity\Address;
use App\User\Repository\UserRepository;

/**
 * Relay point manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class RelayPointManager
{
    private $entityManager;
    private $userRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * Add an relay point.
     *
     * @param RelayPoint     $relayPoint              The relay point to add
     * @return RelayPoint    The relay point created
     */
    public function addRelayPoint(RelayPoint $relayPoint)
    {
        if ($creator = $this->userRepository->find($relayPoint->getCreatorId())) {
            $relayPoint->setUser($creator);
        } else {
            throw new RelayPointException("creator not found");
        }

        // persist the relay point
        $this->entityManager->persist($relayPoint);
        $this->entityManager->flush();

        // check if the address was set
        if (!is_null($relayPoint->getAddress())) {
            $address = new Address();
            $address->setStreetAddress($relayPoint->getAddress()->getStreetAddress());
            $address->setPostalCode($relayPoint->getAddress()->getPostalCode());
            $address->setAddressLocality($relayPoint->getAddress()->getAddressLocality());
            $address->setAddressCountry($relayPoint->getAddress()->getAddressCountry());
            $address->setLatitude($relayPoint->getAddress()->getLatitude());
            $address->setLongitude($relayPoint->getAddress()->getLongitude());
            $address->setHouseNumber($relayPoint->getAddress()->getHouseNumber());
            $address->setStreetAddress($relayPoint->getAddress()->getStreetAddress());
            $address->setSubLocality($relayPoint->getAddress()->getSubLocality());
            $address->setLocalAdmin($relayPoint->getAddress()->getLocalAdmin());
            $address->setCounty($relayPoint->getAddress()->getCounty());
            $address->setMacroCounty($relayPoint->getAddress()->getMacroCounty());
            $address->setRegion($relayPoint->getAddress()->getRegion());
            $address->setMacroRegion($relayPoint->getAddress()->getMacroRegion());
            $address->setCountryCode($relayPoint->getAddress()->getCountryCode());
            $address->setRelayPoint($relayPoint);
            $this->entityManager->persist($address);
            $this->entityManager->flush();
        }

        return $relayPoint;
    }

    /**
     * Patch a relay point.
     *
     * @param RelayPoint $relayPoint    The relay point to update
     * @param array $fields             The updated fields
     * @return RelayPoint   The relay point updated
     */
    public function patchRelayPoint(RelayPoint $relayPoint, array $fields)
    {
        // check if creator has changed
        if (in_array('creatorId', array_keys($fields))) {
            if ($creator = $this->userRepository->find($fields['creatorId'])) {
                // set the new creator
                $relayPoint->setUser($creator);
            } else {
                throw new RelayPointException("Creator not found");
            }
        }

        // persist the relay point
        $this->entityManager->persist($relayPoint);
        $this->entityManager->flush();
        
        // return the relay point
        return $relayPoint;
    }

    /**
     * Delete a relay point
     *
     * @param RelayPoint $relayPoint  The relay point to delete
     * @return void
     */
    public function deleteRelayPoint(RelayPoint $relayPoint)
    {
        $this->entityManager->remove($relayPoint);
        $this->entityManager->flush();
    }
}
