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

    # check Domains files
    DOMAINS_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/user/domains.json
    if [ ! -f "$DOMAINS_FILE" ]; then
        echo "{}" >> /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/user/domains.json
    fi

    # check sso file
    SSO_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/user/sso.json
    if [ ! -f "$SSO_FILE" ]; then
        echo "{}" >> /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/user/sso.json
    fi

    # check Modules files
    MODULES_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/modules.json
    if [ ! -f "$MODULES_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/modules.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/modules.json
    fi

    # check Contacts files
    CONTACTS_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/contacts.json
    if [ ! -f "$CONTACTS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/contacts.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/contacts.json
    fi

	# check analytics files
    ANALYTICS_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/analytics.json
    if [ ! -f "$ANALYTICS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/analytics.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/analytics.json
    fi

	# check geocomplete palette file
    GEOCOMPLETE_PALETTE_FILE=/var/www/$VERSION/$INSTANCE/config/geocomplete/palette.json
    if [ ! -f "$GEOCOMPLETE_PALETTE_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/client/geocomplete/palette.json.dist /var/www/$VERSION/$INSTANCE/config/geocomplete/palette.json
    fi

	# check automated commands file
	AUTOMATED_COMMANDS_FILE=/var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/commands.json
	if [ ! -f "$AUTOMATED_COMMANDS_FILE" ]; then
        cp /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/commands.json.dist /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/config/params/commands.json
    fi

    # check env files
    python3 /var/www/$VERSION/$INSTANCE/mobicoop-platform/scripts/checkClientEnv.py -path /var/www/$VERSION/$INSTANCE/mobicoop-platform -env $VERSION_MIGRATE

    # Migrations platform
    cd /var/www/$VERSION/$INSTANCE/mobicoop-platform/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    # SymLink custom email translations
    if [ -d "../../translations/email" ]; then
        if [ ! -f "translations_client" ]; then
            ln -s ../../translations/email/ translations_client;
        fi
    fi

    # Symlink custom email templates
    if [ -d "../../templates/bundles/MobicoopBundle/email" ]; then
        if [ ! -f "templates/email_client" ]; then
            ln -s  ../../../templates/bundles/MobicoopBundle/email templates/email_client;
        fi
    fi

    # Migrations instance
    cd /var/www/$VERSION/$INSTANCE/;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    # Crontab update
    python3 /var/www/$VERSION/$INSTANCE/scripts/updateCrontab.py -env $VERSION_MIGRATE

    # External Cgu Mango
    EXTERNAL_CGU_DIRECTORY=/var/www/$VERSION/$INSTANCE/public/externalCgu
    if [ ! -d "$EXTERNAL_CGU_DIRECTORY" ]; then
        cd /var/www/$VERSION/$INSTANCE/public/;
        mkdir externalCgu;
    fi
    cd /var/www/$VERSION/$INSTANCE/public/externalCgu;
    wget https://www.mangopay.com/terms/PSP/PSP_MANGOPAY_FR.pdf

    # Remove maintenance page
    rm /var/www/$VERSION/$INSTANCE/mobicoop-platform/api/public/maintenance.enable
    rm /var/www/$VERSION/$INSTANCE/public/maintenance.enable

    # Fixtures for test
    if [ $VERSION == "test" ]
    then
        cd /var/www/$VERSION/$INSTANCE/mobicoop-platform/api;
        php bin/console doctrine:fixtures:load -n -v --append --group=basic --env=$VERSION_MIGRATE
        php bin/console doctrine:fixtures:load -n -v --append --group=solidary --env=$VERSION_MIGRATE
    fi

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
    if [ ! -f "$PT_PROVIDERS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/publicTransport/providers.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/publicTransport/providers.json
    fi

    # check Domains files
    DOMAINS_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/user/domains.json
    if [ ! -f "$DOMAINS_FILE" ]; then
        echo "{}" >> /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/user/domains.json
    fi

    # check sso file
    SSO_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/user/sso.json
    if [ ! -f "$SSO_FILE" ]; then
        echo "{}" >> /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/user/sso.json
    fi

    # check Modules files
    MODULES_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/modules.json
    if [ ! -f "$MODULES_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/modules.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/modules.json
    fi

    # check Contacts files
    CONTACTS_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/contacts.json
    if [ ! -f "$CONTACTS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/contacts.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/contacts.json
    fi

	# check analytics files
    ANALYTICS_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/analytics.json
    if [ ! -f "$ANALYTICS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/analytics.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/analytics.json
    fi

	# check geocomplete palette file
    GEOCOMPLETE_PALETTE_FILE=/var/www/$INSTANCE/$VERSION/config/geocomplete/palette.json
    if [ ! -f "$GEOCOMPLETE_PALETTE_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/config/geocomplete/palette.json.dist /var/www/$INSTANCE/$VERSION/config/geocomplete/palette.json
    fi

	# check automated commands file
	AUTOMATED_COMMANDS_FILE=/var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/commands.json
	if [ ! -f "$AUTOMATED_COMMANDS_FILE" ]; then
        cp /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/commands.json.dist /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/config/params/commands.json
    fi

    # check env files
    python3 /var/www/$INSTANCE/$VERSION/mobicoop-platform/scripts/checkClientEnv.py -path /var/www/$INSTANCE/$VERSION/mobicoop-platform -env $VERSION_MIGRATE

    # Migrations platform
    cd /var/www/$INSTANCE/$VERSION/mobicoop-platform/api;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    # SymLink custom email translations
    if [ -d "../../translations/email" ]; then
        if [ ! -f "translations_client" ]; then
            ln -s ../../translations/email/ translations_client;
        fi
    fi

    # Symlink custom email templates
    if [ -d "../../templates/bundles/MobicoopBundle/email" ]; then
        if [ ! -f "templates/email_client" ]; then
            ln -s  ../../../templates/bundles/MobicoopBundle/email templates/email_client;
        fi
    fi

    # Migrations instance
    cd /var/www/$INSTANCE/$VERSION/;
    php bin/console doctrine:migrations:migrate --env=$VERSION_MIGRATE -n;

    # Crontab update
    python3 /var/www/$INSTANCE/$VERSION/scripts/updateCrontab.py -env $VERSION_MIGRATE

    # External Cgu Mango
    EXTERNAL_CGU_DIRECTORY=/var/www/$INSTANCE/$VERSION/public/externalCgu
    if [ ! -d "$EXTERNAL_CGU_DIRECTORY" ]; then
        cd /var/www/$INSTANCE/$VERSION/public/;
        mkdir externalCgu;
    fi
    cd /var/www/$INSTANCE/$VERSION/public/externalCgu;
    wget https://www.mangopay.com/terms/PSP/PSP_MANGOPAY_FR.pdf

    # Remove maintenance page
    rm /var/www/$INSTANCE/$VERSION/mobicoop-platform/api/public/maintenance.enable
    rm /var/www/$INSTANCE/$VERSION/public/maintenance.enable
fi
