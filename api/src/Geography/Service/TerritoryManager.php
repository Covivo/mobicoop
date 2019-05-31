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

use App\Geography\Entity\Territory;
use Doctrine\ORM\EntityManagerInterface;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;

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
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $polygon = new MultiPolygon(json_decode($territory->getGeoJsonDetail(), true));
        $territory->setGeoJsonDetail($polygon);

        // todo : check if the territory already exists
        // note : we can't use a unique contraint to do so as the field is a blob...

        $this->entityManager->persist($territory);
        $this->entityManager->flush();

        return $territory;
    }
}
