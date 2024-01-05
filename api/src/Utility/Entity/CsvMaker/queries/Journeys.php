<?php
/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Utility\Entity\CsvMaker\queries;

use App\Carpool\Entity\Ask;
use App\Utility\Interfaces\MultipleQueriesInterface;

class Journeys implements MultipleQueriesInterface
{
    private $_multipleQueries;

    public function __construct()
    {
        $this->_multipleQueries = [];

        $this->_multipleQueries[] = 'CREATE TEMPORARY TABLE journeys (
            journeyId int NOT NULL,
            adId int NOT NULL,
            userId int NOT NULL,
            role varchar(10) NOT NULL,
            origin varchar(100) NOT NULL,
            destination varchar(100) NOT NULL,
            end_validity_date DATETIME NOT NULL,
            journeytype varchar(10) NOT NULL,
            frequency varchar(10) NOT NULL,
            origin_lat decimal(10,6) NOT NULL,
            origin_lon decimal(10,6) NOT NULL,
            destination_lat decimal(10,6) NOT NULL,
            destination_lon decimal(10,6) NOT NULL,
            price decimal(10,6) NOT NULL,
            PRIMARY KEY(journeyId, adId, userId)
        );';

        $this->_multipleQueries[] = '
        INSERT IGNORE INTO journeys (journeyId, adId, userId, role, origin, destination, end_validity_date, journeytype, frequency, origin_lat, origin_lon, destination_lat, destination_lon, price)
        SELECT
            ask.id as "journeyId",
            pr.id as "adId",
            pr.user_id as "userId",
            "passenger" as "role",
            ao.address_locality AS "origin",
            ad.address_locality AS "destination",
            CASE
                c.frequency
                WHEN 1 THEN c.from_date
                WHEN 2 THEN c.to_date
            END AS "end_validity_date",
            CASE
                ask.type
                WHEN 1 THEN "oneway"
                WHEN 2 THEN "outward"
                WHEN 3 THEN "return"
            END AS "journeytype",
            CASE
                c.frequency
                WHEN 1 THEN "punctual"
                WHEN 2 THEN "regular"
            END AS "frequency",
            ao.latitude AS "origin_lat",
            ao.longitude AS "origin_lon",
            ad.latitude AS "destination_lat",
            ad.longitude AS "destination_lon",
            c.driver_computed_price as "price"
        FROM
            ask
            inner join matching m on ask.matching_id = m.id
            inner join waypoint wo on (
                wo.matching_id = m.id
                and wo.position = 0
            )
            inner join waypoint wd on (
                wd.matching_id = m.id
                and wd.destination = 1
            )
            inner join address ao on ao.id = wo.address_id
            inner join address ad on ad.id = wd.address_id
            inner join criteria c on c.id = ask.criteria_id
            inner join proposal pr on m.proposal_request_id = pr.id
        where
            ask.status in ('.Ask::STATUS_ACCEPTED_AS_DRIVER.', '.Ask::STATUS_ACCEPTED_AS_PASSENGER.')
            and COALESCE(c.to_date, c.from_date) >= NOW()';

        $this->_multipleQueries[] = '
            INSERT IGNORE INTO journeys (journeyId, adId, userId, role, origin, destination, end_validity_date, journeytype, frequency, origin_lat, origin_lon, destination_lat, destination_lon, price)
                SELECT
                ask.id as "journeyId",
                po.id as "adId",
                po.user_id as "userId",
                "driver" as "role",
                ao.address_locality AS "origin",
                ad.address_locality AS "destination",
                CASE
                    c.frequency
                    WHEN 1 THEN c.from_date
                    WHEN 2 THEN c.to_date
                END AS "end_validity_date",
                CASE
                    ask.type
                    WHEN 1 THEN "oneway"
                    WHEN 2 THEN "outward"
                    WHEN 3 THEN "return"
                END AS "journeytype",
                CASE
                    c.frequency
                    WHEN 1 THEN "punctual"
                    WHEN 2 THEN "regular"
                END AS "frequency",
                ao.latitude AS "origin_lat",
                ao.longitude AS "origin_lon",
                ad.latitude AS "destination_lat",
                ad.longitude AS "destination_lon",
                c.driver_computed_price as "price"
            FROM
                ask
                inner join matching m on ask.matching_id = m.id
                inner join waypoint wo on (
                    wo.matching_id = m.id
                    and wo.position = 0
                )
                inner join waypoint wd on (
                    wd.matching_id = m.id
                    and wd.destination = 1
                )
                inner join address ao on ao.id = wo.address_id
                inner join address ad on ad.id = wd.address_id
                inner join criteria c on c.id = ask.criteria_id
                inner join proposal po on m.proposal_offer_id = po.id
            where
                ask.status in ('.Ask::STATUS_ACCEPTED_AS_DRIVER.', '.Ask::STATUS_ACCEPTED_AS_PASSENGER.')
                and COALESCE(c.to_date, c.from_date) >= NOW()';

        $this->_multipleQueries[] = '
            select
            *
        from
            journeys
        order by journeyId asc;';
    }

    public function getMultipleQueries(): array
    {
        return $this->_multipleQueries;
    }
}
