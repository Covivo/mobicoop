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

namespace App\RelayPoint\Service;

use App\Community\Service\CommunityManager;
use App\RelayPoint\Resource\RelayPointMap;

/**
 * RelayPointMap manager service.
 *
 * @author Céline Jacquet <celine.jacquet@mobicoop.org>
 */
class RelayPointMapManager
{
    private $communityManager;
    


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CommunityManager $communityManager
    ) {
        $this->communityManager = $communityManager;
    }

    /**
     * Method to get all RelayPointsMap for a community
     * @var int $id Id of the community
     * @return array
     */
    public function getRelayPointsMapCommunity(int $id): array
    {
        $relayPointsMap = [];
        
        // we get all RelayPoints of a community
        $community = $this->communityManager->getCommunity($id);

        foreach ($community->getRelayPoints() as $relayPoint) {
            /**
             * @var RelayPoint $relayPoint
             */
            $relayPointMap = new RelayPointMap();
            $relayPointMap->setId($relayPoint->getId());
            $relayPointMap->setName($relayPoint->getName());
            $relayPointMap->setRelayPointType($relayPoint->getRelayPointType());
            $relayPointMap->setCommunity($relayPoint->getCommunity());
            $relayPointMap->setAddress($relayPoint->getAddress());

            $relayPointsMap[] = $relayPointMap;
        }
        return $relayPointsMap;
    }
}
