SELECT
    id,
    name,
    admin_level,
    min_latitude,
    max_latitude,
    min_longitude,
    max_longitude
FROM
    territory
ORDER BY
    admin_level,
    id