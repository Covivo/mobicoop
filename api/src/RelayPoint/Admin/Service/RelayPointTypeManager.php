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
use Doctrine\ORM\EntityManagerInterface;
use App\RelayPoint\Entity\RelayPointType;

/**
 * Relay point type manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class RelayPointTypeManager
{
    private $entityManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Add a relay point type.
     *
     * @param RelayPointType     $relayPointType    The relay point type to add
     * @return RelayPointType    The relay point type created
     */
    public function addRelayPointType(RelayPointType $relayPointType)
    {
        // persist the relay point type
        $this->entityManager->persist($relayPointType);
        $this->entityManager->flush();

        return $relayPointType;
    }

    /**
     * Patch a relay point type.
     *
     * @param RelayPointType $relayPointType    The relay point type to update
     * @param array $fields                     The updated fields
     * @return RelayPointType   The relay point type updated
     */
    public function patchRelayPointType(RelayPointType $relayPointType, array $fields)
    {
        // persist the relay point type
        $this->entityManager->persist($relayPointType);
        $this->entityManager->flush();
        
        // return the relay point type
        return $relayPointType;
    }

    /**
     * Delete a relay point type
     *
     * @param RelayPoint $relayPointType  The relay point type to delete
     * @return void
     */
    public function deleteRelayPointType(RelayPointType $relayPointType)
    {
        $this->entityManager->remove($relayPointType);
        $this->entityManager->flush();
    }
}
