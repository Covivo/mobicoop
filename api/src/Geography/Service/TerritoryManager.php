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
    public function getTerritory(int $id)
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

        $this->logger->info('TerritoryManager : treating territory : '.$territory->getId().' for addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $sql = 'INSERT INTO address_territory (address_id,territory_id)
            SELECT a.id, t.id
            FROM address a
            JOIN territory t
            WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
            AND t.id = '.$territory->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $this->logger->info('TerritoryManager : end treating addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $this->logger->info('TerritoryManager : treating territory : '.$territory->getId().' for directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $sql = 'INSERT INTO direction_territory (direction_id,territory_id)
            SELECT d.id, t.id
            FROM direction d
            JOIN territory t
            WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
            AND t.id = '.$territory->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
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
            $this->logger->info('TerritoryManager : removing address-territory link for territory '.$territoryData->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'DELETE FROM address_territory WHERE territory_id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $this->logger->info('TerritoryManager : removing direction-territory link for territory '.$territoryData->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'DELETE FROM direction_territory WHERE territory_id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $this->logger->info('TerritoryManager : treating territory : '.$territoryData->getId().' for addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'INSERT INTO address_territory (address_id,territory_id)
                SELECT a.id, t.id
                FROM address a
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,a.geo_json)=1
                AND t.id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $this->logger->info('TerritoryManager : end treating addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            $this->logger->info('TerritoryManager : treating territory : '.$territoryData->getId().' for directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $sql = 'INSERT INTO direction_territory (direction_id,territory_id)
                SELECT d.id, t.id
                FROM direction d
                JOIN territory t
                WHERE ST_INTERSECTS(t.geo_json_detail,d.geo_json_detail)=1
                AND t.id = '.$territoryData->getId();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
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
        $stmt->execute();
        // remove all direction territories
        $this->logger->info('TerritoryManager : removing direction-territory link | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $sql = 'DELETE FROM direction_territory';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // create address territory link
        // long process, we need to cut into batches
        // we will iterate through territories as addresses are simple geometries, it is faster to use the territories as base for loops
        $sql = 'SELECT id FROM territory';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
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
            $stmt->execute();
        }
        $this->logger->info('TerritoryManager : end treating addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // create direction territory link
        // long process, we need to cut into batches
        // we will iterate through directions as they are complex geometries, it is faster to use the directions as base for loops
        $sql = 'SELECT id FROM direction';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
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
            $stmt->execute();
        }
        $this->logger->info('TerritoryManager : end treating directions | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * Affect addresses and directions that are not linked yet to their territories.
     */
    public function updateAddressesAndDirections()
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        if (file_exists($this->batchTemp.self::CHECK_RUNNING_FILE)) {
            $this->logger->info('Link addresses with territories already running | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            return;
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
                );')->execute()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }

        $this->logger->info('INSERT INTO disaddress | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        // in the following, we will assume addresses with same exact geo coordinates are equals, so they have the same territories...
        if (!$result =
            $this->entityManager->getConnection()->prepare('
                INSERT INTO disaddress (lat,lon,geo)
                    (   SELECT DISTINCT a.latitude,a.longitude, a.geo_json 
                        FROM address a LEFT JOIN address_territory adt ON a.id = adt.address_id 
                        WHERE adt.address_id IS NULL AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL AND a.geo_json IS NOT NULL
                    )
                ;')->execute()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }
        $out = new \DateTime('UTC');
        $diff = $out->diff($in);
        $secs = ((($diff->format('%a') * 24) + $diff->format('%H')) * 60 + $diff->format('%i')) * 60 + $diff->format('%s');
        $this->logger->info('DURATION '.$secs.' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        if (!(
            $nbAddresses = $this->entityManager->getConnection()->fetchColumn('SELECT count(*) as cid from disaddress;')
        )) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }
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
                );')->execute()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }

        $sqlt = 'SELECT id, admin_level from territory order by admin_level desc, id asc;';
        $stmtt = $this->entityManager->getConnection()->prepare($sqlt);
        $stmtt->execute();
        $resultst = $stmtt->fetchAll();
        foreach ($resultst as $resultt) {
            $territories = [$resultt['id']];
            if (!$result =
                $this->entityManager->getConnection()->prepare('
                    DELETE FROM adter; 
                    INSERT INTO adter (aid,tid,geo,lat,lon) 
                        SELECT a.id, t.id, geo, lat, lon FROM disaddress a 
                        JOIN territory t ON t.id = '.$resultt['id'].'
                        LEFT JOIN address_territory at ON at.address_id = a.id AND at.territory_id = '.$resultt['id'].'
                        WHERE ST_DISTANCE(geo, Polygon(ST_ExteriorRing(ST_ConvexHull(geo_json_detail))))=0 AND at.address_id IS NULL
                    ;')->execute()) {
                return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
            }
            if (!$result =
                $this->entityManager->getConnection()->prepare('DELETE adter FROM adter INNER JOIN territory t ON t.id = '.$resultt['id'].' WHERE ST_DISTANCE(geo, geo_json_detail)>0;')->execute()) {
                return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
            }

            // search for parent territories
            $sqlp = '
                SELECT parent.id from territory parent
                JOIN territory child ON child.id = '.$resultt['id'].'
                WHERE parent.admin_level < '.$resultt['admin_level'].' 
                AND ST_CONTAINS(parent.geo_json_detail,child.geo_json_detail)=1;
            ';
            $stmtp = $this->entityManager->getConnection()->prepare($sqlp);
            $stmtp->execute();
            $resultsp = $stmtp->fetchAll();
            foreach ($resultsp as $resultp) {
                $territories[] = $resultp['id'];
            }
            $stmtp->closeCursor();

            $sql = 'SELECT SQL_NO_CACHE aid,tid,lat,lon FROM adter';
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            foreach ($results as $result) {
                foreach ($territories as $territory) {
                    $sqli = 'INSERT IGNORE INTO address_territory (address_id, territory_id) SELECT id, '.$territory.' from address WHERE latitude='.$result['lat'].' and longitude='.$result['lon'];
                    $stmti = $this->entityManager->getConnection()->prepare($sqli);
                    $stmti->execute();
                }
            }
            $stmt->closeCursor();
        }
        $stmtt->closeCursor();

        $sql = 'DROP TABLE disaddress;DROP TABLE adter;';
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();
        $this->logger->info('Insert into address_territory finished | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $this->closeRunningFile() && $this->dropGeoJsonTerritoryIndex() && $result;
    }

    public function linkAddressesWithTerritories()
    {
        if (file_exists($this->batchTemp.self::CHECK_RUNNING_FILE)) {
            $this->logger->info('Link addresses with territories already running | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            return;
        }

        $this->logger->info('Start linking addresses with territories | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $this->createRunningFile();

        if (!$this->addGeoJsonTerritoryIndex()) {
            return false;
        }

        $this->logger->info('Get min and max levels | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        if (!(
            $minLevel = $this->entityManager->getConnection()->fetchColumn('SELECT min(admin_level) as level from territory')
                && $maxLevel = $this->entityManager->getConnection()->fetchColumn('SELECT max(admin_level) as level from territory')
        )) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }

        $this->logger->info('Phase 1 | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        if (!$result =
            $this->entityManager->getConnection()->prepare('start transaction;')->execute()
            && $this->entityManager->getConnection()->prepare("
                insert into address_territory (address_id, territory_id)
                select a.id, t.id 
                from address a 
                    inner join territory t on a.latitude between t.min_latitude and t.max_latitude and a.longitude between t.min_longitude and t.max_longitude
                where a.id not in (select address_id from address_territory at inner join territory t on t.id = at.territory_id where t.admin_level = {$maxLevel}) 
                    and t.admin_level = {$maxLevel} 
                    and st_contains(t.geo_json_detail, a.geo_json)=1;")->execute()
            && $this->entityManager->getConnection()->prepare('commit;')->execute()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }

        $this->logger->info('Phase 2 | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        for ($i = ($maxLevel - 1); $i >= $minLevel; --$i) {
            if (!$result =
                $this->entityManager->getConnection()->prepare('start transaction;')->execute()
                && $this->entityManager->getConnection()->prepare('
                    insert into address_territory (address_id, territory_id) 
                    select a.id, tt.parent_id
                    from address a 
                        inner join address_territory at3 on at3.address_id = a.id
                        inner join territory t3 on t3.id = at3.territory_id and t3.admin_level = '.(1 + $i)." and t3.id in (select tt.child_id from territory_parent as tt where 1 group by child_id having count(*)=1)
                        inner join territory_parent tt on tt.child_id = t3.id
                    where a.id not in (select at2.address_id from address_territory at2 inner join territory t2 on t2.id = at2.territory_id and t2.admin_level = {$i});")->execute()
                && $this->entityManager->getConnection()->prepare('commit;')->execute()) {
                return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
            }
            if (!$result =
                $this->entityManager->getConnection()->prepare('start transaction;')->execute()
                && $this->entityManager->getConnection()->prepare('
                    insert into address_territory (address_id, territory_id) 
                    select a.id, t2.id
                    from address a 
                        inner join address_territory at3 on at3.address_id = a.id
                        inner join territory t3 on t3.id = at3.territory_id and t3.admin_level = '.(1 + $i)." and t3.id in (select tt.child_id from territory_parent as tt where 1 group by child_id having count(*)>1)
                        inner join territory t2 on t2.admin_level = {$i} and a.latitude between t2.min_latitude and t2.max_latitude and a.longitude between t2.min_longitude and t2.max_longitude
                    where a.id not in (select at2.address_id from address_territory at2 inner join territory t2 on t2.id = at2.territory_id and t2.admin_level = {$i})
                        and st_contains(t2.geo_json_detail, a.geo_json)=1;")->execute()
                && $this->entityManager->getConnection()->prepare('commit;')->execute()) {
                return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
            }
        }

        $this->logger->info('Phase 3 | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        if (!$result =
            $this->entityManager->getConnection()->prepare('start transaction;')->execute()
            && $this->entityManager->getConnection()->prepare('
                insert into address_territory (address_id, territory_id) 
                select a.id, t.id 
                from address a 
                    inner join territory t on a.latitude between t.min_latitude and t.max_latitude and a.longitude between t.min_longitude and t.max_longitude 
                where a.id not in (select at.address_id from address_territory at) and st_contains(t.geo_json_detail, a.geo_json)=1;')->execute()
            && $this->entityManager->getConnection()->prepare('commit;')->execute()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }

        $this->logger->info('Phase 4 | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        if (!$result =
            $this->entityManager->getConnection()->prepare('start transaction;')->execute()
            && $this->entityManager->getConnection()->prepare('
                insert into address_territory (address_id, territory_id) 
                select a.id, t.id 
                from address a 
                    inner join territory t on a.latitude between t.min_latitude and t.max_latitude and a.longitude between t.min_longitude and t.max_longitude 
                where a.id not in (select at.address_id from address_territory at where at.territory_id = t.id) and st_contains(t.geo_json_detail, a.geo_json)=1;')->execute()
            && $this->entityManager->getConnection()->prepare('commit;')->execute()) {
            return $this->dropGeoJsonTerritoryIndex() && $this->closeRunningFile() && false;
        }

        $this->logger->info('End linking addresses with territories | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $this->closeRunningFile() && $this->dropGeoJsonTerritoryIndex() && $result;
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
        $result = $this->entityManager->getConnection()->prepare('CREATE SPATIAL INDEX IF NOT EXISTS IDX_GEOJSON_DETAIL ON territory (geo_json_detail);')->execute();
        $this->logger->info('End add spatial index to territory | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $result;
    }

    private function dropGeoJsonTerritoryIndex()
    {
        $this->logger->info('Drop spatial index to territory | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $result = $this->entityManager->getConnection()->prepare('DROP INDEX IDX_GEOJSON_DETAIL ON territory;')->execute();
        $this->logger->info('End drop spatial index to territory | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $result;
    }
}
