<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Geography\Service;

use App\Geography\Entity\Direction;
use App\Geography\Repository\TerritoryRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Direction management service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class DirectionManager
{
    private $entityManager;
    private $geoRouter;
    private $territoryRepository;

    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, GeoRouter $geoRouter, TerritoryRepository $territoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->geoRouter = $geoRouter;
        $this->territoryRepository = $territoryRepository;
    }

    /**
     * Update directions with geoJson data.
     */
    public function updateDirectionsWithGeoJson(): void
    {
        $batch = 50;
        $pool = 0;
        $qCriteria = $this->entityManager->createQuery('SELECT d FROM App\Geography\Entity\Direction d WHERE d.geoJsonDetail IS NULL');
        $iterableResult = $qCriteria->iterate();
        foreach ($iterableResult as $row) {
            $direction = $row[0];
            $direction->setPoints($this->geoRouter->getRouter()->deserializePoints($direction->getDetail()));
            $direction->setSaveGeoJsonDetail(true);
            $this->entityManager->persist($direction);
            // batch
            ++$pool;
            if ($pool >= $batch) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $pool = 0;
            }
        }
        // final flush for pending persists
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * Create or update territories for a direction.
     *
     * @param Direction $direction The direction
     * @param bool      $persist   Persit the address immediately
     *
     * @return Direction The direction with its territories
     */
    public function createDirectionTerritories(Direction $direction, bool $persist = false): Direction
    {
        // first we remove all territories
        $direction->removeTerritories();
        // then we search the territories
        if ($territories = $this->territoryRepository->findDirectionTerritories($direction)) {
            foreach ($territories as $territory) {
                $direction->addTerritory($territory);
            }
        }
        if ($persist) {
            $this->entityManager->persist($direction);
            $this->entityManager->flush();
        }

        return $direction;
    }

    /**
     * Create or update territories for a Direction, only if the direction is directly related to 'useful' entities :
     * - proposal.
     *
     * @param Direction $direction The direction
     * @param bool      $persist   Persit the address immediately
     */
    public function createDirectionTerritoriesForUsefulEntity(Direction $direction, bool $persist = false)
    {
        $createLink = false;
        if (count($direction->getCriteriaDrivers()) > 0) {
            foreach ($direction->getCriteriaDrivers() as $criteriaDriver) {
                if (!is_null($criteriaDriver->getProposal())) {
                    $createLink = true;

                    break;
                }
            }
        } elseif (count($direction->getCriteriaPassengers()) > 0) {
            foreach ($direction->getCriteriaPassengers() as $criteriaPassenger) {
                if (!is_null($criteriaPassenger->getProposal())) {
                    $createLink = true;

                    break;
                }
            }
        }
        if ($createLink) {
            return $this->createDirectionTerritories($direction, $persist);
        }

        return $direction;
    }
}
