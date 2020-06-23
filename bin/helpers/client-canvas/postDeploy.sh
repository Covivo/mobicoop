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

if [ $VERSION == "dev" ] || [ $VERSION == "test" ]
then
    # check RDEX files
    RDEX_CLIENTS_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/clients.json
    RDEX_OPERATOR_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/operator.json
    RDEX_PROVIDERS_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/providers.json
    if [ ! -f "$RDEX_CLIENTS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/clients.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/clients.json
    fi
    if [ ! -f "$RDEX_OPERATOR_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/operator.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/operator.json
    fi
    if [ ! -f "$RDEX_PROVIDERS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/providers.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/rdex/providers.json
    fi

    # check PT files
    PT_PROVIDERS_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/publicTransport/providers.json
    if [ ! -f "$RDEX_CLIENTS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/publicTransport/providers.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/publicTransport/providers.json
    fi
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
    rm -Rf node_modules;
    rm package-lock.json;
    npm install;
    npm run build;
else
    # check RDEX files
    RDEX_CLIENTS_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/clients.json
    RDEX_OPERATOR_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/operator.json
    RDEX_PROVIDERS_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/providers.json
    if [ ! -f "$RDEX_CLIENTS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/clients.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/clients.json
    fi
    if [ ! -f "$RDEX_OPERATOR_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/operator.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/operator.json
    fi
    if [ ! -f "$RDEX_PROVIDERS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/providers.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/rdex/providers.json
    fi

    # check PT files
    PT_PROVIDERS_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/publicTransport/providers.json
    if [ ! -f "$RDEX_CLIENTS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/publicTransport/providers.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/publicTransport/providers.json
    fi
    python3 /var/www/$INSTANCE/$VERSION/mobicoop-platform/scripts/checkClientEnv.py -path /var/www/$INSTANCE/$VERSION/mobicoop-platform -env $VERSION_MIGRATE
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
    rm -Rf node_modules;
    rm package-lock.json;
    npm install;
    npm run build;
fi