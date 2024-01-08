SELECT
    id as 'userId',
    given_name,
    family_name,
    gender,
    email,
    telephone,
    created_date,
    last_activity_date,
    news_subscription as 'optin'
FROM
    `user`
WHERE
    user.status <> 4;