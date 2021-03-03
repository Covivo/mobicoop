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

use App\Image\Repository\IconRepository;
use App\RelayPoint\Entity\RelayPoint;
use Doctrine\ORM\EntityManagerInterface;
use App\RelayPoint\Entity\RelayPointType;
use App\RelayPoint\Exception\RelayPointTypeException;

/**
 * Relay point type manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class RelayPointTypeManager
{
    private $entityManager;
    private $iconRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        IconRepository $iconRepository
    ) {
        $this->entityManager = $entityManager;
        $this->iconRepository = $iconRepository;
    }

    /**
     * Add a relay point type.
     *
     * @param RelayPointType     $relayPointType    The relay point type to add
     * @return RelayPointType    The relay point type created
     */
    public function addRelayPointType(RelayPointType $relayPointType)
    {
        if ($relayPointType->getIconId()) {
            if ($icon = $this->iconRepository->find($relayPointType->getIconId())) {
                $relayPointType->setIcon($icon);
            } else {
                throw new RelayPointTypeException("Relay point type icon not found");
            }
        }

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
        // check if icon has changed
        if (in_array('iconId', array_keys($fields))) {
            if ($fields['iconId'] === null) {
                $relayPointType->setIcon(null);
            } elseif ($icon = $this->iconRepository->find($fields['iconId'])) {
                // set the new icon
                $relayPointType->setIcon($icon);
            } else {
                throw new RelayPointTypeException("Relay point type icon not found");
            }
        }

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
