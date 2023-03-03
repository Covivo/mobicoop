<?php

$multipleQueries = [];

$multipleQueries[] = 'CREATE TEMPORARY TABLE user_territory (
    user_id int NOT NULL,
    territory_id int NOT NULL,
    PRIMARY KEY(user_id, territory_id)
);';

$multipleQueries[] = '
INSERT
    IGNORE INTO user_territory (user_id, territory_id)
SELECT
    user.id,
    territory_id
FROM
    user
    inner join address as homeAddress on homeAddress.user_id = user.id
    inner join address_territory as homeAddressTerritory on homeAddress.id = homeAddressTerritory.address_id
    inner join territory as homeTerritory on homeTerritory.id = homeAddressTerritory.territory_id
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

$multipleQueries[] = '
INSERT
    IGNORE INTO user_territory (user_id, territory_id)
SELECT
    user.id,
    territory_id
FROM
    user
    inner join proposal ON user.id = proposal.user_id
    inner join criteria on criteria.id = proposal.criteria_id
    inner join waypoint as destination_waypoint ON destination_waypoint.proposal_id = proposal.id
    inner join address as destination_address on destination_waypoint.address_id = destination_address.id
    inner join address_territory as destination_address_territory on destination_address_territory.address_id = destination_address.id
    inner join territory as destination_territory on destination_address_territory.territory_id = destination_territory.id
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

$multipleQueries[] = '
INSERT
    IGNORE INTO user_territory (user_id, territory_id)
SELECT
    user.id,
    territory_id
FROM
    user
    inner join ask on ask.user_id = user.id
    inner join waypoint as ask_destination_waypoint ON ask_destination_waypoint.ask_id = ask.id
    inner join address as ask_address on ask_destination_waypoint.address_id = ask_address.id
    inner join address_territory as ask_destination_address_territory on ask_destination_address_territory.address_id = ask_address.id
    inner join territory as ask_destination_territory on ask_destination_address_territory.territory_id = ask_destination_territory.id
where
    ask_destination_waypoint.id in (
        select
            id
        from
            waypoint
        where
            destination = 1
            and ask_id is not null
    )
    and ask_destination_territory.id <> 0;';

$multipleQueries[] = '
    select
    *
from
    user_territory;';
