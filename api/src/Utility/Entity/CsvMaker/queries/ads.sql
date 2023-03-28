SELECT
    p.id,
    p.user_id as 'userId',
    ad.address_locality AS 'origin',
    aa.address_locality AS 'destination',
    CASE
        c.frequency
        WHEN 1 THEN c.from_date
        WHEN 2 THEN c.to_date
    END AS 'end_validity_date',
    CASE
        p.type
        WHEN 1 THEN 'onway'
        WHEN 2 THEN 'outward'
        WHEN 3 THEN 'return'
    END AS 'journeytype',
    CASE
        WHEN c.driver = 1
        and c.passenger = 1 THEN 'both'
        WHEN (
            c.driver = 0
            or c.driver is null
        )
        and c.passenger = 1 THEN 'passenger'
        WHEN c.driver = 1
        and (
            c.passenger = 0
            or c.passenger is null
        ) THEN 'driver'
        ELSE 'unknown'
    END AS 'type',
    ad.latitude AS "origin_lat",
    ad.longitude AS "origin_lon",
    aa.latitude AS "destination_lat",
    aa.longitude AS "destination_lon"
FROM
    proposal p
    INNER JOIN criteria c ON c.id = p.criteria_id
    INNER JOIN waypoint wd ON (
        wd.proposal_id = p.id
        and wd.position = 0
    )
    INNER JOIN waypoint wa ON (
        wa.proposal_id = p.id
        and wa.destination = 1
    )
    INNER JOIN address ad ON ad.id = wd.address_id
    INNER JOIN address aa ON aa.id = wa.address_id
WHERE
    p.private = 0
    AND (
        p.dynamic != 1
        OR p.dynamic IS NULL
    )
    AND COALESCE(c.to_date, c.from_date) >= NOW();