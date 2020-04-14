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
use Psr\Log\LoggerInterface;

/**
 * Territory management service.
 *
 * This service is used to determine wether particular points is within given territories.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class TerritoryManager
{
    const BATCH_ADDRESSES = 100;
    const BATCH_DIRECTIONS = 100;

    private $entityManager;
    private $logger;
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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

        // TODO : search all addresses and directions that belong to this new territory

        $this->entityManager->persist($territory);
        $this->entityManager->flush();

        return $territory;
    }

    /**
     * Reaffect all addresses and directions to their territories
     *
     * @return void
     */
    public function initAddressesAndDirections()
    {
        $conn = $this->entityManager->getConnection();

        // remove all addresses territories
        $this->logger->info("TerritoryManager : removing address-territory link | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $sql = "DELETE FROM address_territory";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        // remove all direction territories
        $this->logger->info("TerritoryManager : removing direction-territory link | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $sql = "DELETE FROM direction_territory";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // create address territory link
        // long process, we need to cut into batches
        // first we get the territory ids
        $sql = "SELECT id FROM territory";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }
        $this->logger->info("TerritoryManager : number of territories to treat : " . count($ids) . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // then we insert using batches
        $this->logger->info("TerritoryManager : start treating addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        foreach ($ids as $id) {
            $this->logger->info("TerritoryManager : treating territory : $id for addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $sql = "INSERT INTO address_territory (address_id,territory_id)
                SELECT a.id, t.id
                FROM address a
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
                AND t.id = $id
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        $this->logger->info("TerritoryManager : end treating addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // create direction territory link
        $this->logger->info("TerritoryManager : start treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        foreach ($ids as $id) {
            $this->logger->info("TerritoryManager : treating territory : $id | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $sql = "INSERT INTO direction_territory (direction_id,territory_id)
                SELECT d.id, t.id
                FROM direction d
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json)=1
                AND t.id = $id
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        $this->logger->info("TerritoryManager : end treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }
}
