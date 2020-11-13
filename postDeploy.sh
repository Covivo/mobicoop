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

if [ $VERSION == "dev" ] || [ $VERSION == "test" ] || [ $VERSION == "prod_test"]
then

    # check RDEX files
    RDEX_CLIENTS_FILE=/var/www/$VERSION/$INSTANCE/api/config/rdex/clients.json
    RDEX_OPERATOR_FILE=/var/www/$VERSION/$INSTANCE/api/config/rdex/operator.json
    RDEX_PROVIDERS_FILE=/var/www/$VERSION/$INSTANCE/api/config/rdex/providers.json
    if [ ! -f "$RDEX_CLIENTS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/api/config/rdex/clients.json.dist /var/www/$VERSION/$INSTANCE/api/config/rdex/clients.json
    fi
    if [ ! -f "$RDEX_OPERATOR_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/api/config/rdex/operator.json.dist /var/www/$VERSION/$INSTANCE/api/config/rdex/operator.json
    fi
    if [ ! -f "$RDEX_PROVIDERS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/api/config/rdex/providers.json.dist /var/www/$VERSION/$INSTANCE/api/config/rdex/providers.json
    fi

    # check PT files
    PT_PROVIDERS_FILE=/var/www/$VERSION/$INSTANCE/api/config/publicTransport/providers.json
    if [ ! -f "$PT_PROVIDERS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/api/config/publicTransport/providers.json.dist /var/www/$VERSION/$INSTANCE/api/config/publicTransport/providers.json
    fi

    # check Domains files
    DOMAINS_FILE=/var/www/$VERSION/$INSTANCE/api/config/user/domains.json
    if [ ! -f "$DOMAINS_FILE" ]; then
        echo "{}" >> /var/www/$VERSION/$INSTANCE/api/config/user/domains.json
    fi

    # check SSO files
    SSO_FILE=/var/www/$VERSION/$INSTANCE/api/config/user/sso.json
    if [ ! -f "$SSO_FILE" ]; then
        echo "{}" >> /var/www/$VERSION/$INSTANCE/api/config/user/sso.json
    fi
    
    # Migrations
    cd /var/www/$VERSION/$INSTANCE/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    # Crontab update
    python3 /var/www/$VERSION/$INSTANCE/scripts/updateCrontab.py -env $VERSION_MIGRATE

    #Admin build
    cd /var/www/$VERSION/$INSTANCE/admin;
    rm -Rf node_modules;
    rm package-lock.json;
    npm install;
    npm run build;

else

    # check RDEX files
    RDEX_CLIENTS_FILE=/var/www/$INSTANCE/$VERSION/api/config/rdex/clients.json
    RDEX_OPERATOR_FILE=/var/www/$INSTANCE/$VERSION/api/config/rdex/operator.json
    RDEX_PROVIDERS_FILE=/var/www/$INSTANCE/$VERSION/api/config/rdex/providers.json
    if [ ! -f "$RDEX_CLIENTS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/api/config/rdex/clients.json.dist /var/www/$INSTANCE/$VERSION/api/config/rdex/clients.json
    fi
    if [ ! -f "$RDEX_OPERATOR_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/api/config/rdex/operator.json.dist /var/www/$INSTANCE/$VERSION/api/config/rdex/operator.json
    fi
    if [ ! -f "$RDEX_PROVIDERS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/api/config/rdex/providers.json.dist /var/www/$INSTANCE/$VERSION/api/config/rdex/providers.json
    fi

    # check PT files
    PT_PROVIDERS_FILE=/var/www/$INSTANCE/$VERSION/api/config/publicTransport/providers.json
    if [ ! -f "$RDEX_CLIENTS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/api/config/publicTransport/providers.json.dist /var/www/$INSTANCE/$VERSION/api/config/publicTransport/providers.json
    fi

    # check Domains files
    DOMAINS_FILE=/var/www/$INSTANCE/$VERSION/api/config/user/domains.json
    if [ ! -f "$DOMAINS_FILE" ]; then
        echo "{}" >> /var/www/$INSTANCE/$VERSION/api/config/user/domains.json
    fi

    # SSO files
    SSO_FILE=/var/www/$INSTANCE/$VERSION/api/config/user/sso.json
    if [ ! -f "$SSO_FILE" ]; then
        echo "{}" >> /var/www/$INSTANCE/$VERSION/api/config/user/sso.json
    fi

    # Migrations
    cd /var/www/$INSTANCE/$VERSION/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    # Crontab update
    python3 /var/www/$INSTANCE/$VERSION/scripts/updateCrontab.py -env $VERSION_MIGRATE

    #Admin build
    cd /var/www/$INSTANCE/$VERSION/admin;
    rm -Rf node_modules;
    rm package-lock.json;
    npm install;
    npm run build;

fi