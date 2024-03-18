SELECT
    u.id as 'userId',
    given_name,
    family_name,
    gender,
    email,
    telephone,
    u.created_date,
    last_activity_date,
    news_subscription as 'optin',
    ssa.sso_provider as ssoProvider,
    ssa.sso_id as usr_external_id
FROM
    `user` u
    LEFT JOIN `sso_account` ssa on ssa.user_id = u.id
    AND ssa.id IN (
        SELECT
            ssa.id
        FROM
            `sso_account` ssa
        WHERE
            ssa.sso_provider IS NULL
            OR ssa.sso_provider <> 'mobConnect'
    )
WHERE
    u.status <> 4;