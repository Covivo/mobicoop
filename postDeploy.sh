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

#Migrations
cd /var/www/$INSTANCE/$VERSION/api;
php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

#Specific Edge and exotics browsers
cd ../client;
rm -Rf node_modules/;
yarn install;
yarn encore dev;

#Admin build
cd /var/www/$INSTANCE/$VERSION/admin;
npm run build;