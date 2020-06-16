#!/bin/bash

for i in "$@"
do
case $i in
    --version=*)
    VERSION="${i#*=}"
    shift # past argument=value
    ;;
    --version-migrate=*)
    VERSION_MIGRATE="${i#*=}"
    shift # past argument=value
    ;;
    --instance=*)
    INSTANCE="${i#*=}"
    shift # past argument=value
    ;;
esac
done

if [ $VERSION = "prod" || $VERSION = "staging" ]
then
    python3 /var/www/$INSTANCE/$VERSION/mobicoop-platform/scripts/checkClientEnv.py -path /var/www/$VERSION/$INSTANCE/mobicoop-platform -env $VERSION_MIGRATE
    #Migrations
    cd /var/www/$INSTANCE/$VERSION/mobicoop-platform/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    #Specific Edge and exotics browsers
    cd ../client;
    rm -Rf node_modules/;
    yarn install;
    yarn encore dev;
    cd ../../;
    rm -Rf node_modules/;
    yarn install;
    yarn encore dev;

    #Admin build
    cd /var/www/$INSTANCE/$VERSION/mobicoop-platform/admin;
    npm run build;
else
    python3 /var/www/$VERSION/$INSTANCE/mobicoop-platform/scripts/checkClientEnv.py -path /var/www/$VERSION/$INSTANCE/mobicoop-platform -env $VERSION_MIGRATE
    #Migrations
    cd /var/www/$VERSION/$INSTANCE/mobicoop-platform/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    #Specific Edge and exotics browsers
    cd ../client;
    rm -Rf node_modules/;
    yarn install;
    yarn encore dev;
    cd ../../;
    rm -Rf node_modules/;
    yarn install;
    yarn encore dev;

    #Admin build
    cd /var/www/$VERSION/$INSTANCE/mobicoop-platform/admin;
    npm run build;
fi