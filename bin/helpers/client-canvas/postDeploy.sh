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
    if [ ! -f "$PT_PROVIDERS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/publicTransport/providers.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/publicTransport/providers.json
    fi

    # check env files
    python3 /var/www/$VERSION/$INSTANCE/mobicoop-platform/scripts/checkClientEnv.py -path /var/www/$VERSION/$INSTANCE/mobicoop-platform -env $VERSION_MIGRATE

    #Migrations
    cd /var/www/$VERSION/$INSTANCE/mobicoop-platform/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    #SymLink custom email translations
    if [ -d "../../translations/email" ]; then
        if [ ! -f "translations_client" ]; then
            ln -s ../../translations/email/ translations_client;
        fi
    fi
    #Symlink custom email templates
    if [ -d "../../templates/bundles/MobicoopBundle/email" ]; then
        if [ ! -f "templates/email_client" ]; then
            ln -s  ../../../templates/bundles/MobicoopBundle/email templates/email_client;
        fi
    fi

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

    # check env files
    python3 /var/www/$INSTANCE/$VERSION/mobicoop-platform/scripts/checkClientEnv.py -path /var/www/$INSTANCE/$VERSION/mobicoop-platform -env $VERSION_MIGRATE

    #Migrations
    cd /var/www/$INSTANCE/$VERSION/mobicoop-platform/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

        #SymLink custom email translations
    if [ -d "../../translations/email" ]; then
        if [ ! -f "translations_client" ]; then
            ln -s ../../translations/email/ translations_client;
        fi
    fi
    #Symlink custom email templates
    if [ -d "../../templates/bundles/MobicoopBundle/email" ]; then
        if [ ! -f "templates/email_client" ]; then
            ln -s  ../../../templates/bundles/MobicoopBundle/email templates/email_client;
        fi
    fi
    
    # Remove maintenance page
    rm /var/www/$INSTANCE/$VERSION/public/maintenance.enable

    #Admin build
    cd /var/www/$INSTANCE/$VERSION/mobicoop-platform/admin;
    rm -Rf node_modules;
    rm package-lock.json;
    npm install;
    npm run build;
fi