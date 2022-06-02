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

use App\Geography\Entity\Territory;
use App\Geography\Repository\TerritoryRepository;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use Doctrine\ORM\EntityManagerInterface;
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
    private const CHECK_RUNNING_FILE = 'updateAddressesAndDirections.txt';

    private $entityManager;
    private $territoryRepository;
    private $logger;
    private $batchTemp;

    private $filePointer;

    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, TerritoryRepository $territoryRepository, LoggerInterface $logger, string $batchTemp)
    {
        $this->entityManager = $entityManager;
        $this->territoryRepository = $territoryRepository;
        $this->logger = $logger;
        $this->batchTemp = $batchTemp;
        $this->filePointer = null;
    }

    /**
     * Get a territory.
     *
     * @param int $id The id of the territory
     *
     * @return null|Territory The territory or null if not found
     */
    public function getTerritory(int $id): ?Territory
    {
        return $this->territoryRepository->find($id);
    }

    /**
     * Create a new territory.
     *
     * @param Territory $territory The territory to create
     *
     * @return Territory The territory created
     */
    public function createTerritory(Territory $territory): Territory
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

        $this->logger->info('TerritoryManager : treating territory : '.$territory->getId().' for addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $sql = 'INSERT INTO address_territory (address_id,territory_id)
            SELECT a.id, t.id
            FROM address a
            JOIN territory t
            WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
            AND t.id = '.$territory->getId();
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $this->logger->info('TerritoryManager : end treating addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $this->logger->info('TerritoryManager : treating territory : '.$territory->getId().' for directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $sql = 'INSERT INTO direction_territory (direction_id,territory_id)
            SELECT d.id, t.id
            FROM direction d
            JOIN territory t
            WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
            AND t.id = '.$territory->getId();
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $this->logger->info('TerritoryManager : end treating directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $territory;
    }

    /**
     * Update a territory.
     *
     * @param Territory $territory The territory data used to update the territory
     *
     * @return Territory The territory updated
     */
    public function updateTerritory(Territory $territoryData): Territory
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
            $this->logger->info('TerritoryManager : removing address-territory link for territory '.$territoryData->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'DELETE FROM address_territory WHERE territory_id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
            $this->logger->info('TerritoryManager : removing direction-territory link for territory '.$territoryData->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'DELETE FROM direction_territory WHERE territory_id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();

            $this->logger->info('TerritoryManager : treating territory : '.$territoryData->getId().' for addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'INSERT INTO address_territory (address_id,territory_id)
                SELECT a.id, t.id
                FROM address a
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
                AND t.id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
            $this->logger->info('TerritoryManager : end treating addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            $this->logger->info('TerritoryManager : treating territory : '.$territoryData->getId().' for directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'INSERT INTO direction_territory (direction_id,territory_id)
                SELECT d.id, t.id
                FROM direction d
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
                AND t.id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
            $this->logger->info('TerritoryManager : end treating directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }

        return $territoryData;
    }

    /**
     * Reaffect all addresses and directions to their territories.
     */
    public function initAddressesAndDirections()
    {
        $conn = $this->entityManager->getConnection();

        // remove all addresses territories
        $this->logger->info('TerritoryManager : removing address-territory link | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $sql = 'DELETE FROM address_territory';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        // remove all direction territories
        $this->logger->info('TerritoryManager : removing direction-territory link | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $sql = 'DELETE FROM direction_territory';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        // create address territory link
        // long process, we need to cut into batches
        // we will iterate through territories as addresses are simple geometries, it is faster to use the territories as base for loops
        $sql = 'SELECT id FROM territory';
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery()->fetchAllAssociative();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }
        $this->logger->info('TerritoryManager : number of territories to treat : '.count($ids).' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // then we insert addresses for each territory
        $this->logger->info('TerritoryManager : start treating addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        foreach ($ids as $id) {
            $this->logger->info("TerritoryManager : treating territory : {$id} for addresses | ".(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = "INSERT INTO address_territory (address_id,territory_id)
                SELECT a.id, t.id
                FROM address a
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
                AND t.id = {$id}
            ";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
        }
        $this->logger->info('TerritoryManager : end treating addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // create direction territory link
        // long process, we need to cut into batches
        // we will iterate through directions as they are complex geometries, it is faster to use the directions as base for loops
        $sql = 'SELECT id FROM direction';
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery()->fetchAllAssociative();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }
        $this->logger->info('TerritoryManager : number of directions to treat : '.count($ids).' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // then we insert
        $this->logger->info('TerritoryManager : start treating directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        foreach ($ids as $id) {
            $this->logger->info("TerritoryManager : treating direction : {$id} | ".(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = "INSERT INTO direction_territory (direction_id,territory_id)
                SELECT d.id, t.id
                FROM direction d
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
                AND d.id = {$id}
            ";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
        }
        $this->logger->info('TerritoryManager : end treating directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * Affect new addresses that are not linked yet to their territories.
     */
    public function linkNewAddressesWithTerritories()
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        if (file_exists($this->batchTemp.self::CHECK_RUNNING_FILE)) {
            $this->logger->info('Link addresses with territories already running | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            return false;
        }

        $this->logger->info('Start linking addresses with territories | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $this->createRunningFile();

        if (!$this->addGeoJsonTerritoryIndex()) {
            return false;
        }

        // ADDRESSES
        $in = new \DateTime('UTC');
        if (!$result =
            $this->entityManager->getConnection()->prepare('
                CREATE TEMPORARY TABLE disaddress (
                    id int AUTO_INCREMENT NOT NULL,
                    lat decimal(10,6) NOT NULL,
                    lon decimal(10,6) NOT NULL,
                    geo POINT NOT NULL,
                    SPATIAL INDEX(geo),
                    PRIMARY KEY(id)
                );')->executeQuery()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }
        $result->free();

        $this->logger->info('INSERT INTO disaddress | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        // in the following, we will assume addresses with same exact geo coordinates are equals, so they have the same territories...
        if (!$result =
            $this->entityManager->getConnection()->prepare('
                INSERT INTO disaddress (lat,lon,geo)
                    (   SELECT DISTINCT a.latitude,a.longitude, a.geo_json
                        FROM address a LEFT JOIN address_territory adt ON a.id = adt.address_id
                        WHERE adt.address_id IS NULL AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL AND a.geo_json IS NOT NULL
                    )
                ;')->executeQuery()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }
        $result->free();

        $out = new \DateTime('UTC');
        $diff = $out->diff($in);
        $secs = ((($diff->format('%a') * 24) + $diff->format('%H')) * 60 + $diff->format('%i')) * 60 + $diff->format('%s');
        $this->logger->info('DURATION '.$secs.' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        if (!(
            $nbAddresses = $this->entityManager->getConnection()->fetchOne('SELECT count(*) as cid from disaddress;')
        )) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }
        $result->free();

        $this->logger->info('NB address '.$nbAddresses.' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        if (!$result =
            $this->entityManager->getConnection()->prepare('
                CREATE TEMPORARY TABLE adter (
                    aid int NOT NULL,
                    tid int NOT NULL,
                    geo POINT NOT NULL,
                    lat decimal(10,6) NOT NULL,
                    lon decimal(10,6) NOT NULL,
                    SPATIAL INDEX(geo),
                    PRIMARY KEY(aid)
                );')->executeQuery()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }
        $result->free();

        $sql_territory = 'SELECT id, admin_level from territory order by admin_level desc, id asc;';
        $stmt_territory = $this->entityManager->getConnection()->prepare($sql_territory);
        $result_territory = $stmt_territory->executeQuery();
        $results_territory = $result_territory->fetchAllAssociative();
        $result_territory->free();
        foreach ($results_territory as $territoryAsArray) {
            $territories = [$territoryAsArray['id']];
            if (!$result =
                $this->entityManager->getConnection()->prepare('
                    DELETE FROM adter;
                    INSERT INTO adter (aid,tid,geo,lat,lon)
                        SELECT a.id, t.id, geo, lat, lon FROM disaddress a
                        JOIN territory t ON t.id = '.$territoryAsArray['id'].'
                        LEFT JOIN address_territory at ON at.address_id = a.id AND at.territory_id = '.$territoryAsArray['id'].'
                        WHERE ST_DISTANCE(geo, Polygon(ST_ExteriorRing(ST_ConvexHull(geo_json_detail))))=0 AND at.address_id IS NULL
                    ;')->executeQuery()) {
                return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
            }
            $result->free();

            if (!$result =
                $this->entityManager->getConnection()->prepare('DELETE adter FROM adter INNER JOIN territory t ON t.id = '.$territoryAsArray['id'].' WHERE ST_DISTANCE(geo, geo_json_detail)>0;')->executeQuery()) {
                return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
            }
            $result->free();

            // search for parent territories
            $sql_parent = '
                SELECT parent.id from territory parent
                JOIN territory child ON child.id = '.$territoryAsArray['id'].'
                WHERE parent.admin_level < '.$territoryAsArray['admin_level'].'
                AND ST_CONTAINS(parent.geo_json_detail,child.geo_json_detail)=1;
            ';
            $stmt_parent = $this->entityManager->getConnection()->prepare($sql_parent);
            $result_parent = $stmt_parent->executeQuery();
            $results_parent = $result_parent->fetchAllAssociative();
            $result_parent->free();
            foreach ($results_parent as $parent) {
                $territories[] = $parent['id'];
            }

            $sql = 'SELECT SQL_NO_CACHE aid,tid,lat,lon FROM adter';
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $result = $stmt->executeQuery();
            $results = $result->fetchAllAssociative();
            $result->free();
            $this->entityManager->getConnection()->prepare('start transaction;')->executeQuery();
            foreach ($results as $result) {
                foreach ($territories as $territory) {
                    $sqli = 'INSERT IGNORE INTO address_territory (address_id, territory_id) SELECT id, '.$territory.' from address WHERE latitude='.$result['lat'].' and longitude='.$result['lon'];
                    $stmti = $this->entityManager->getConnection()->prepare($sqli);
                    $result = $stmti->executeQuery();
                    $result->free();
                }
            }
            $this->entityManager->getConnection()->prepare('commit;')->executeQuery();
        }

        $sql = 'DROP TABLE disaddress;DROP TABLE adter;';
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $result = $stmt->executeQuery();
        $result->free();

        $this->logger->info('Insert into address_territory finished | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $this->closeRunningFile() && $this->dropGeoJsonTerritoryIndex();
    }

    private function createRunningFile()
    {
        $this->filePointer = fopen($this->batchTemp.self::CHECK_RUNNING_FILE, 'w');
        fwrite($this->filePointer, '+');
    }

    private function closeRunningFile()
    {
        return fclose($this->filePointer) && unlink($this->batchTemp.self::CHECK_RUNNING_FILE);
    }

    private function addGeoJsonTerritoryIndex()
    {
        $this->logger->info('Add spatial index to territory | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $result = $this->entityManager->getConnection()->prepare('CREATE SPATIAL INDEX IF NOT EXISTS IDX_GEOJSON_DETAIL ON territory (geo_json_detail);')->executeQuery();
        $result->free();

        $this->logger->info('End add spatial index to territory | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $result;
    }

    private function dropGeoJsonTerritoryIndex()
    {
        $this->logger->info('Drop spatial index to territory | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $result = $this->entityManager->getConnection()->prepare('DROP INDEX IDX_GEOJSON_DETAIL ON territory;')->executeQuery();
        $result->free();

        $this->logger->info('End drop spatial index to territory | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $result;
    }
}
