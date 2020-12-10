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
 **************************/

namespace App\Carpool\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Journey manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class JourneyManager
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
     * Hydrate journey
     *
     * @return void
     */
    public function hydrate()
    {
        set_time_limit(60);

        $conn = $this->entityManager->getConnection();

        // delete existing journeys
        $sql = "truncate journey;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // insert journeys
        $sql = "
        insert into journey (proposal_id, user_id, frequency, from_date, origin, latitude_origin, longitude_origin, destination, latitude_destination, longitude_destination, created_date) 
        select p.id, 0, 0, now(), ao.address_locality, ao.latitude, ao.longitude, ad.address_locality, ad.latitude, ad.longitude, now() from address ao 
        left join waypoint wo on wo.address_id = ao.id left join proposal p on wo.proposal_id = p.id left join waypoint wd on wd.proposal_id = p.id 
        left join address ad on wd.address_id = ad.id 
        where p.private <> 1 and ao.address_locality is not null and ad.address_locality is not null and wo.proposal_id is not null and wo.position = 0 and wd.destination = 1;
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // add date and time
        $sql = "
        update journey j inner join criteria c on c.id = j.proposal_id set j.frequency = c.frequency, j.from_date = c.from_date, j.to_date = c.to_date, j.time = c.from_time, 
        j.days = IF(c.frequency=2, 
        (
            CONCAT(
                '{',
                CONCAT('\"mon\":',IF(c.mon_check=1 AND c.mon_time IS NOT NULL,CONCAT('\"',c.mon_time,'\"'),'null'),','),
                CONCAT('\"tue\":',IF(c.tue_check=1 AND c.tue_time IS NOT NULL,CONCAT('\"',c.tue_time,'\"'),'null'),','),
                CONCAT('\"wed\":',IF(c.wed_check=1 AND c.wed_time IS NOT NULL,CONCAT('\"',c.wed_time,'\"'),'null'),','),
                CONCAT('\"thu\":',IF(c.thu_check=1 AND c.thu_time IS NOT NULL,CONCAT('\"',c.thu_time,'\"'),'null'),','),
                CONCAT('\"fri\":',IF(c.fri_check=1 AND c.fri_time IS NOT NULL,CONCAT('\"',c.fri_time,'\"'),'null'),','),
                CONCAT('\"sat\":',IF(c.sat_check=1 AND c.sat_time IS NOT NULL,CONCAT('\"',c.sat_time,'\"'),'null'),','),
                CONCAT('\"sun\":',IF(c.sun_check=1 AND c.sun_time IS NOT NULL,CONCAT('\"',c.sun_time,'\"'),'null')),
                '}'
            )
        ),
        null);
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // remove unwanted journeys
        $sql = "delete from journey where frequency=0;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // add user
        $sql = "
        update journey j inner join proposal p on p.id = j.proposal_id inner join user u on u.id = p.user_id set j.user_id = u.id, j.user_name = 
        CONCAT(
            UPPER(LEFT(TRIM(u.given_name),1)),
            LOWER(RIGHT(TRIM(u.given_name),CHAR_LENGTH(TRIM(u.given_name))-1)),
            ' ',
            UPPER(LEFT(TRIM(u.family_name),1)),
            '.'
        );
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}
