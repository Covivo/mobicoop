SELECT
    child_territory.id as child_id,
    child_territory.admin_level as child_admin_level,
    parent_territory.id as parent_id,
    parent_territory.admin_level as parent_admin_level
FROM
    `territory_parent`
    inner join territory as child_territory on child_territory.id = territory_parent.child_id
    inner join territory as parent_territory on parent_territory.id = territory_parent.parent_id;