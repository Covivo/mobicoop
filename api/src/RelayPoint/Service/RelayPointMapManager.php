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

namespace App\RelayPoint\Service;

use App\Community\Service\CommunityManager;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Resource\RelayPointMap;
use App\User\Entity\User;

/**
 * RelayPointMap manager service.
 *
 * @author Céline Jacquet <celine.jacquet@mobicoop.org>
 */
class RelayPointMapManager
{
    private $communityManager;
    private $relayPointManager;
    private $dataPath;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CommunityManager $communityManager,
        RelayPointManager $relayPointManager,
        string $dataPath
    ) {
        $this->communityManager = $communityManager;
        $this->relayPointManager = $relayPointManager;
        $this->dataPath = $dataPath;
    }

    /**
     * Method to get all RelayPointsMap.
     */
    public function getRelayPointsMap(?User $user, array $context): array
    {
        $relayPointsMap = [];
        $relayPoints = $this->relayPointManager->getRelayPoints($user, '', $context);
        foreach ($relayPoints as $relayPoint) {
            if (!is_null($this->buildRelayPointMap($relayPoint))) {
                $relayPointsMap[] = $this->buildRelayPointMap($relayPoint);
            }
        }

        return $relayPointsMap;
    }

    /**
     * Method to get all RelayPointsMap for a community.
     *
     * @var int Id of the community
     */
    public function getRelayPointsMapCommunity(int $id): array
    {
        $relayPointsMap = [];

        // we get all RelayPoints of a community
        $community = $this->communityManager->getCommunity($id);

        foreach ($community->getRelayPoints() as $relayPoint) {
            $relayPointsMap[] = $this->buildRelayPointMap($relayPoint);
        }

        return $relayPointsMap;
    }

    /**
     * Build a RelayPointMap from a RelayPoint.
     *
     * @param RelayPoint $relayPoint The base RelayPoint
     *
     * @return RelayPointMap The builded RelayPointMap
     */
    private function buildRelayPointMap(RelayPoint $relayPoint): RelayPointMap
    {
        $relayPointMap = new RelayPointMap();
        $relayPointMap->setId($relayPoint->getId());
        $relayPointMap->setName($relayPoint->getName());
        $relayPointMap->setRelayPointType($relayPoint->getRelayPointType());
        $relayPointMap->setAddress($relayPoint->getAddress());
        $relayPointMap->setPrivate($relayPoint->isPrivate());
        $relayPointMap->setPlaces($relayPoint->getPlaces());
        $relayPointMap->setPlacesDisabled($relayPoint->getPlacesDisabled());
        $relayPointMap->setFree($relayPoint->isFree());
        $relayPointMap->setSecured($relayPoint->isSecured());
        $relayPointMap->setOfficial($relayPoint->isOfficial());

        if (!is_null($relayPoint->getImages()) && count($relayPoint->getImages()) > 0
            && file_exists('upload/'.RelayPointMap::IMAGE_PATH.'/'.RelayPointMap::IMAGE_VERSION.'-'.$relayPoint->getImages()[0]->getFileName())) {
            $relayPointMap->setImage($this->dataPath.RelayPointMap::IMAGE_PATH.'/'.RelayPointMap::IMAGE_VERSION.'-'.$relayPoint->getImages()[0]->getFileName());
        }

        return $relayPointMap;
    }
}
