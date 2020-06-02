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
use App\Geography\Repository\TerritoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use Psr\Log\LoggerInterface;

/**
 * Territory management service.
 *
 * This service is used to determine wether particular points is within given territories.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class TerritoryManager
{
    private const BATCH_ADDRESSES = 100;

    private $entityManager;
    private $territoryRepository;
    private $logger;
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, TerritoryRepository $territoryRepository, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->territoryRepository = $territoryRepository;
        $this->logger = $logger;
    }

    /**
     * Get a territory
     *
     * @param integer $id       The id of the territory
     * @return Territory|null   The territory or null if not found
     */
    public function getTerritory(int $id)
    {
        return $this->territoryRepository->find($id);
    }

    /**
     * Create a new territory.
     *
     * @param Territory $territory  The territory to create
     * @return Territory The territory created
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

        // search and link all addresses and directions that belong to this new territory
        $conn = $this->entityManager->getConnection();
        
        $this->logger->info("TerritoryManager : treating territory : " . $territory->getId() . " for addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $sql = "INSERT INTO address_territory (address_id,territory_id)
            SELECT a.id, t.id
            FROM address a
            JOIN territory t
            WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
            AND t.id = " . $territory->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $this->logger->info("TerritoryManager : end treating addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        $this->logger->info("TerritoryManager : treating territory : " . $territory->getId() . " for directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $sql = "INSERT INTO direction_territory (direction_id,territory_id)
            SELECT d.id, t.id
            FROM direction d
            JOIN territory t
            WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
            AND t.id = " . $territory->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $this->logger->info("TerritoryManager : end treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        return $territory;
    }

    /**
     * Update a territory.
     *
     * @param Territory $territory  The territory data used to update the territory
     * @return Territory The territory updated
     */
    public function updateTerritory(Territory $territoryData)
    {
        // territories are special objects, they mainly rely on geojson data, so we need to know if the update concerns only the name or even the geo data
        $geoUpdated = false;

        // we first check if the geoJsonDetail is a string => if so geo data is posted, we assume the data has changed
        if (is_string($territoryData->getGeoJsonDetail())) {
            // geo data posted
            $polygon = new MultiPolygon(json_decode($territoryData->getGeoJsonDetail(), true));
            $territoryData->setGeoJsonDetail($polygon);
            $geoUpdated = true;
        }
        
        $this->entityManager->persist($territoryData);
        $this->entityManager->flush();

        if ($geoUpdated) {
            $conn = $this->entityManager->getConnection();
            // delete previously linked addresses and directions
            $this->logger->info("TerritoryManager : removing address-territory link for territory " . $territoryData->getId() . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $sql = "DELETE FROM address_territory WHERE territory_id = " . $territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $this->logger->info("TerritoryManager : removing direction-territory link for territory " . $territoryData->getId() . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $sql = "DELETE FROM direction_territory WHERE territory_id = " . $territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $this->logger->info("TerritoryManager : treating territory : " . $territoryData->getId() . " for addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $sql = "INSERT INTO address_territory (address_id,territory_id)
                SELECT a.id, t.id
                FROM address a
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
                AND t.id = " . $territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $this->logger->info("TerritoryManager : end treating addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

            $this->logger->info("TerritoryManager : treating territory : " . $territoryData->getId() . " for directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $sql = "INSERT INTO direction_territory (direction_id,territory_id)
                SELECT d.id, t.id
                FROM direction d
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
                AND t.id = " . $territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $this->logger->info("TerritoryManager : end treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        }
        
        return $territoryData;
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
        // we will iterate through territories as addresses are simple geometries, it is faster to use the territories as base for loops
        $sql = "SELECT id FROM territory";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }
        $this->logger->info("TerritoryManager : number of territories to treat : " . count($ids) . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // then we insert addresses for each territory
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
        // long process, we need to cut into batches
        // we will iterate through directions as they are complex geometries, it is faster to use the directions as base for loops
        $sql = "SELECT id FROM direction";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }
        $this->logger->info("TerritoryManager : number of directions to treat : " . count($ids) . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        // then we insert
        $this->logger->info("TerritoryManager : start treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        foreach ($ids as $id) {
            $this->logger->info("TerritoryManager : treating direction : $id | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $sql = "INSERT INTO direction_territory (direction_id,territory_id)
                SELECT d.id, t.id
                FROM direction d
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
                AND d.id = $id
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        $this->logger->info("TerritoryManager : end treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }

    /**
     * Affect addresses and directions that are not linked yet to their territories
     *
     * @return void
     */
    public function updateAddressesAndDirections()
    {
        $conn = $this->entityManager->getConnection();
        
        // find all addresses not linked yet
        $sql = "SELECT id FROM address a LEFT JOIN address_territory at ON a.id = at.address_id WHERE at.address_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $address_ids = [];
        foreach ($results as $result) {
            $address_ids[] = $result['id'];
        }
        $this->logger->info("TerritoryManager : updateAddressesAndDirections : number of addresses to treat : " . count($address_ids) . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // we will need an array of territory ids
        $ids= [];

        if (count($address_ids)>0) {
            // create address territory link
            // long process, we need to cut into batches
            // we will iterate through territories as addresses are simple geometries, it is faster to use the territories as base for loops
            $sql = "SELECT id FROM territory";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            foreach ($results as $result) {
                $ids[] = $result['id'];
            }
            $this->logger->info("TerritoryManager : updateAddressesAndDirections : number of territories to treat : " . count($ids) . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

            // then we insert addresses for each territory
            $this->logger->info("TerritoryManager : updateAddressesAndDirections : start treating addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            foreach ($ids as $id) {
                $this->logger->info("TerritoryManager : updateAddressesAndDirections : treating territory : $id for addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                // we use batches of addresses
                $forBatch = $address_ids;
                $continue = true;
                $start = 0;
                while ($continue) {
                    $batch = array_slice($forBatch, $start, self::BATCH_ADDRESSES);
                    if (count($batch)>0) {
                        $sql = "INSERT INTO address_territory (address_id,territory_id)
                            SELECT a.id, t.id
                            FROM address a
                            JOIN territory t
                            WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
                            AND t.id = $id AND a.id IN(" . implode(",", $batch) . ")
                        ";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $start += self::BATCH_ADDRESSES;
                    } else {
                        $continue = false;
                    }
                }
            }
        }
        $this->logger->info("TerritoryManager : updateAddressesAndDirections : end treating addresses | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // find all directions not linked yet
        $sql = "SELECT id FROM direction d LEFT JOIN direction_territory dt ON d.id = dt.direction_id WHERE dt.direction_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $direction_ids = [];
        foreach ($results as $result) {
            $direction_ids[] = $result['id'];
        }
        $this->logger->info("TerritoryManager : updateAddressesAndDirections : number of directions to treat : " . count($direction_ids) . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        if (count($direction_ids)>0) {
            // then we insert
            $this->logger->info("TerritoryManager : updateAddressesAndDirections : start treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            foreach ($direction_ids as $id) {
                $this->logger->info("TerritoryManager : updateAddressesAndDirections : treating direction : $id | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                $sql = "INSERT INTO direction_territory (direction_id,territory_id)
                    SELECT d.id, t.id
                    FROM direction d
                    JOIN territory t
                    WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
                    AND d.id = $id
                ";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            }
            $this->logger->info("TerritoryManager : updateAddressesAndDirections : end treating directions | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        }
    }
}
