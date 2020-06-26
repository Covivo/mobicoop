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
cd /var/www/$VERSION/$INSTANCE/api;
php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

#Admin build
cd /var/www/$VERSION/$INSTANCE/admin;
rm -Rf node_modules;
rm package-lock.json;
npm install;
npm run build;