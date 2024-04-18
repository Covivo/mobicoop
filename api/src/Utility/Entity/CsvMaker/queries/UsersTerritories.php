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

use App\Utility\Interfaces\MultipleQueriesInterface;

class UsersTerritories implements MultipleQueriesInterface
{
    private $_multipleQueries;

    public function __construct()
    {
        $this->_multipleQueries = [];

        $this->_multipleQueries[] = 'CREATE TEMPORARY TABLE export_csv_user_territory (
            user_id int NOT NULL,
            territory_id int NOT NULL,
            territory_name varchar(100) NOT NULL,
            admin_level int(11) NOT NULL,
            ssoProvider varchar(255) NOT NULL,
            usr_external_id varchar(255) NOT NULL,
            PRIMARY KEY(user_id, territory_id)
        );';

        $this->_multipleQueries[] = '
        INSERT
            IGNORE INTO export_csv_user_territory (user_id, territory_id, territory_name, admin_level, ssoProvider, usr_external_id)
        SELECT
            user.id,
            territory_id,
            homeTerritory.name,
            homeTerritory.admin_level,
            ssa.sso_provider as ssoProvider,
            ssa.sso_id as usr_external_id
        FROM
            user
            inner join address as homeAddress on homeAddress.user_id = user.id
            inner join address_territory as homeAddressTerritory on homeAddress.id = homeAddressTerritory.address_id
            inner join territory as homeTerritory on homeTerritory.id = homeAddressTerritory.territory_id
            LEFT JOIN `sso_account` ssa on ssa.user_id = user.id
            AND ssa.id IN (
                SELECT
                    ssa.id
                FROM
                    `sso_account` ssa
                WHERE
                    ssa.sso_provider IS NULL
                    OR ssa.sso_provider <> "mobConnect"
            )
        WHERE
            homeAddress.id in (
                SELECT
                    id
                FROM
                    `address`
                where
                    user_id is not null
            )
            and homeAddress.home = 1
            and homeTerritory.id <> 0;';

        $this->_multipleQueries[] = '
        INSERT
            IGNORE INTO export_csv_user_territory (user_id, territory_id, territory_name, admin_level, ssoProvider, usr_external_id)
        SELECT
            user.id,
            territory_id,
            destination_territory.name,
            destination_territory.admin_level,
            ssa.sso_provider as ssoProvider,
            ssa.sso_id as usr_external_id
        FROM
            user
            inner join proposal ON user.id = proposal.user_id
            inner join criteria on criteria.id = proposal.criteria_id
            inner join waypoint as destination_waypoint ON destination_waypoint.proposal_id = proposal.id
            inner join address as destination_address on destination_waypoint.address_id = destination_address.id
            inner join address_territory as destination_address_territory on destination_address_territory.address_id = destination_address.id
            inner join territory as destination_territory on destination_address_territory.territory_id = destination_territory.id
            LEFT JOIN `sso_account` ssa on ssa.user_id = user.id
            AND ssa.id IN (
                SELECT
                    ssa.id
                FROM
                    `sso_account` ssa
                WHERE
                    ssa.sso_provider IS NULL
                    OR ssa.sso_provider <> "mobConnect"
            )
        WHERE
            destination_waypoint.id in (
                select
                    id
                from
                    waypoint
                where
                    destination = 1
                    and proposal_id is not null
            )
            and proposal.private = 0
            and proposal.user_id is not null
            and destination_territory.id <> 0;';

        $this->_multipleQueries[] = '
        INSERT
            IGNORE INTO export_csv_user_territory (
                user_id,
                territory_id,
                territory_name,
                admin_level,
                ssoProvider,
                usr_external_id
            )
            SELECT
                user.id,
                territory_id,
                origin_territory.name,
                origin_territory.admin_level,
                ssa.sso_provider as ssoProvider,
                ssa.sso_id as usr_external_id
            FROM
                user
                inner join proposal ON user.id = proposal.user_id
                inner join criteria on criteria.id = proposal.criteria_id
                inner join waypoint as origin_waypoint ON origin_waypoint.proposal_id = proposal.id
                inner join address as origin_address on origin_waypoint.address_id = origin_address.id
                inner join address_territory as origin_address_territory on origin_address_territory.address_id = origin_address.id
                inner join territory as origin_territory on origin_address_territory.territory_id = origin_territory.id
                LEFT JOIN `sso_account` ssa on ssa.user_id = user.id
                AND ssa.id IN (
                    SELECT
                        ssa.id
                    FROM
                        `sso_account` ssa
                    WHERE
                        ssa.sso_provider IS NULL
                        OR ssa.sso_provider <> "mobConnect"
                )
            WHERE
                origin_waypoint.id in (
                    select
                        id
                    from
                        waypoint
                    where
                        position = 0
                        and proposal_id is not null
                )
                and proposal.private = 0
                and proposal.user_id is not null
                and origin_territory.id <> 0';

        $this->_multipleQueries[] = '
            select
            *
        from
            export_csv_user_territory;';
    }

    public function getMultipleQueries(): array
    {
        return $this->_multipleQueries;
    }
}
