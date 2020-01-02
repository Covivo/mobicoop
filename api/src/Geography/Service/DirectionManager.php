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
 **************************/

namespace App\Geography\Service;

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
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, GeoRouter $geoRouter)
    {
        $this->entityManager = $entityManager;
        $this->geoRouter = $geoRouter;
    }

    /**
     * Update directions with geoJson data.
     *
     * @return void
     */
    public function updateDirectionsWithGeoJson()
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
            $pool++;
            if ($pool>=$batch) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $pool = 0;
            }
        }
        // final flush for pending persists
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
