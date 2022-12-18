#!/bin/bash

for arg
do
    case "${arg}" in
        --version=* )
            VERSION="${arg#*=}"
            ;;
        --version-migrate=* )
            VERSION_MIGRATE="${arg#*=}"
            ;;
        --instance=* )
            INSTANCE="${arg#*=}"
            ;;
    esac
done


ROOT=/var/www
case "${VERSION}" in
    prod_test )
        VERSION=prod
        ;&
    dev | test )
        ROOT+="/${VERSION}/${INSTANCE}"
        ;;
    * )
        ROOT+="/${INSTANCE}/${VERSION}"
        ;;
esac

# check json files
RDEX_CLIENTS_FILE="${ROOT}/api/config/rdex/clients.json"
RDEX_OPERATOR_FILE="${ROOT}/api/config/rdex/operator.json"
RDEX_PROVIDERS_FILE="${ROOT}/api/config/rdex/providers.json"
PT_PROVIDERS_FILE="${ROOT}/api/config/publicTransport/providers.json"
MODULES_FILE="${ROOT}/api/config/params/modules.json"
CONTACTS_FILE="${ROOT}/api/config/params/contacts.json"
ANALYTICS_FILE="${ROOT}/api/config/params/analytics.json"
GEOCOMPLETE_PALETTE_FILE="${ROOT}/client/config/geocomplete/palette.json"
AUTOMATED_COMMANDS_FILE="${ROOT}/api/config/params/commands.json"

# if json file does not exist, copy it from .dist file
for json_file in "${RDEX_CLIENTS_FILE}" "${RDEX_OPERATOR_FILE}" "${RDEX_PROVIDERS_FILE}" "${PT_PROVIDERS_FILE}"\
                 "${MODULES_FILE}" "${CONTACTS_FILE}" "${ANALYTICS_FILE}" "${GEOCOMPLETE_PALETTE_FILE}"\
                 "${AUTOMATED_COMMANDS_FILE}"
do
    [ -f "${json_file}" ] || cp "${json_file}.dist" "${json_file}"
done

DOMAINS_FILE="${ROOT}/api/config/user/domains.json"
SSO_FILE="${ROOT}/api/config/user/sso.json"

# if json file does not exist, create it with empty braces
for json_file in "${DOMAINS_FILE}" "${SSO_FILE}"
do
    [ -f "${json_file}" ] || echo "{}" >"${json_file}"
done

# Migrations
cd ${ROOT}/api
php bin/console doctrine:migrations:migrate --env=${VERSION_MIGRATE} -n

# Migrations instance
cd ${ROOT}/client
php bin/console doctrine:migrations:migrate --env=${VERSION_MIGRATE} -n

# Crontab update
python3 ${ROOT}/scripts/updateCrontab.py --env=${VERSION_MIGRATE}

# External Cgu Mango
EXTERNAL_CGU_DIRECTORY=${ROOT}/client/public/externalCgu
[ -d "${EXTERNAL_CGU_DIRECTORY}" ] || mkdir -p "${EXTERNAL_CGU_DIRECTORY}"
cd "${EXTERNAL_CGU_DIRECTORY}"
wget -N https://www.mangopay.com/terms/PSP/PSP_MANGOPAY_FR.pdf

# clear cache
cd ${ROOT}/api
php bin/console cache:clear --env=${VERSION_MIGRATE}
cd ${ROOT}/client
php bin/console cache:clear --env=${VERSION_MIGRATE}

# Remove maintenance page
rm ${ROOT}/api/public/maintenance.enable ${ROOT}/client/public/maintenance.enable

# Fixtures for test
if [ "${VERSION}" == "test" ]
then
    cd ${ROOT}/api
    php bin/console doctrine:fixtures:load -n -v --append --group=basic --env=${VERSION_MIGRATE}
    php bin/console doctrine:fixtures:load -n -v --append --group=solidary --env=${VERSION_MIGRATE}
fi

