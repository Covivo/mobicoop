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

use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use App\Geography\Entity\Territory;
use Doctrine\ORM\EntityManagerInterface;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use App\Geography\Repository\DirectionRepository;
use App\DataProvider\Entity\GeoRouterProvider;
use App\Geography\Repository\TerritoryRepository;

/**
 * Territory management service.
 *
 * This service is used to determine wether particular points is within given territories. 
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class TerritoryManager
{
    private $entityManager;
    private $directionRepository;
    private $territoryRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, DirectionRepository $directionRepository, TerritoryRepository $territoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->directionRepository = $directionRepository;
        $this->territoryRepository = $territoryRepository;
    }

    /**
     * Create a new territory.
     *
     * @param Territory $territory
     * @return Territory
     */
    public function createTerritory(Territory $territory)
    {
        // we create the Multipolygon object based on the data sent in the detail property
        // the data is a json string, we first decode it to make an array, then we pass the resulted array to the object constructor
        $polygon = new MultiPolygon(json_decode($territory->getDetail(),true));
        $territory->setDetail($polygon);

        // todo : check if the territory already exists !

        $this->entityManager->persist($territory);
        $this->entityManager->flush();
        // we can now launch the actions needed when a new territory is created
        $this->handleNewTerritory($territory);
        return $territory;
    }

    /**
     * Handle all the territory-related actions when creating a new territory.
     *
     * @param Territory $territory
     * @return void
     */
    private function handleNewTerritory(Territory $territory)
    {
        $this->associateDirectionsForTerritory($territory);
    }

    /**
     * Automatically associate a territory for all directions.
     * Useful when creating a new territory.
     *
     * @param Territory $territory
     * @return void
     */
    public function associateDirectionsForTerritory(Territory $territory)
    {
        // we have to search all the directions that are concerned by the territory
        // the points for directions are not stored individually in the database, they are stored in an encoded format
        // so we can't use geographical functions directly for points in the database
        // we can however limit the number of directions to test by looking at their bounding box 
        $directions = $this->directionRepository->findAllWithBoundingBoxInTerritory($territory);

        // now for each direction we check if a point in the path is in the territory
        foreach ($directions as $direction) {
            // we decode the points
            $direction->setPoints(GeoRouterProvider::deserializePoints($direction->getDetail(),true,false));
            if ($this->territoryRepository->directionIsInTerritory($direction,$territory)) {
                $direction->addTerritory($territory);
                $this->entityManager->persist($direction);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Handle all the territory-related actions when creating a new direction.
     *
     * @param Direction $direction
     * @return void
     */
    private function handleNewDirection(Direction $direction)
    {
        $this->associateTerritoriesForDirection($direction);
    }
    
    /**
     * Automatically associate territories for a given direction.
     * Useful when creating a new direction.
     *
     * @param Direction $direction
     * @return void
     */
    private function associateTerritoriesForDirection(Direction $direction)
    {

    }

}