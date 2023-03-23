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
    dev | test )
        ROOT+="/${VERSION}/${INSTANCE}"
        ;;
    * )
        ROOT+="/${INSTANCE}/${VERSION}"
        ;;
esac

# check json files
RDEX_CLIENTS_FILE=${ROOT}/mobicoop-platform/api/config/rdex/clients.json
RDEX_OPERATOR_FILE=${ROOT}/mobicoop-platform/api/config/rdex/operator.json
RDEX_PROVIDERS_FILE=${ROOT}/mobicoop-platform/api/config/rdex/providers.json
PT_PROVIDERS_FILE=${ROOT}/mobicoop-platform/api/config/publicTransport/providers.json
MODULES_FILE=${ROOT}/mobicoop-platform/api/config/params/modules.json
CONTACTS_FILE=${ROOT}/mobicoop-platform/api/config/params/contacts.json
ANALYTICS_FILE=${ROOT}/mobicoop-platform/api/config/params/analytics.json
GEOCOMPLETE_PALETTE_FILE=${ROOT}/config/geocomplete/palette.json
AUTOMATED_COMMANDS_FILE=${ROOT}/mobicoop-platform/api/config/params/commands.json
CSV_EXPORT_FILE=${ROOT}/mobicoop-platform/api/config/csvExport/csvExport.json

# if json file does not exist, copy it from .dist file
for json_file in "${RDEX_CLIENTS_FILE}" "${RDEX_OPERATOR_FILE}" "${RDEX_PROVIDERS_FILE}" "${PT_PROVIDERS_FILE}"\
                 "${MODULES_FILE}" "${CONTACTS_FILE}" "${ANALYTICS_FILE}" "${GEOCOMPLETE_PALETTE_FILE}"\
                 "${AUTOMATED_COMMANDS_FILE}" "${CSV_EXPORT_FILE}"
do
    [ -f "${json_file}" ] || cp "${json_file}.dist" "${json_file}"
done

DOMAINS_FILE=${ROOT}/mobicoop-platform/api/config/user/domains.json
SSO_FILE=${ROOT}/mobicoop-platform/api/config/user/sso.json

# if json file does not exist, create it with empty braces
for json_file in "${DOMAINS_FILE}" "${SSO_FILE}"
do
    [ -f "${json_file}" ] || echo "{}" >"${json_file}"
done

# check env files
python3 ${ROOT}/mobicoop-platform/scripts/checkClientEnv.py --path=${ROOT}/mobicoop-platform --env=${VERSION_MIGRATE}

# Migrations platform
cd ${ROOT}/mobicoop-platform/api;
php bin/console doctrine:migrations:migrate --env=${VERSION_MIGRATE} -n;

# SymLink custom email translations
[ -d "../../translations/email" ] && [ ! -f "translations_client" ] && ln -s ../../translations/email/ translations_client

# Symlink custom email templates
if [ -d "../../templates/bundles/MobicoopBundle/email" ] && [ ! -f "templates/email_client" ]
then
    ln -s  ../../../templates/bundles/MobicoopBundle/email templates/email_client
fi

# Migrations instance
cd ${ROOT}
php bin/console doctrine:migrations:migrate --env=${VERSION_MIGRATE} -n

# Crontab update
python3 ${ROOT}/scripts/updateCrontab.py --env=${VERSION_MIGRATE}

# External Cgu Mango
EXTERNAL_CGU_DIRECTORY=${ROOT}/public/externalCgu
[ -d "${EXTERNAL_CGU_DIRECTORY}" ] || mkdir -p "${EXTERNAL_CGU_DIRECTORY}"
cd "${EXTERNAL_CGU_DIRECTORY}"
wget -N https://www.mangopay.com/terms/PSP/PSP_MANGOPAY_FR.pdf

# Remove maintenance page
rm ${ROOT}/mobicoop-platform/api/public/maintenance.enable ${ROOT}/public/maintenance.enable

# Fixtures for test
if [ "${VERSION}" = "test" ]
then
    cd ${ROOT}/mobicoop-platform/api
    php bin/console doctrine:fixtures:load -n -v --append --group=basic --env=${VERSION_MIGRATE}
    php bin/console doctrine:fixtures:load -n -v --append --group=solidary --env=${VERSION_MIGRATE}
fi

