#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

# copy required param files if needed
cp -u /srv/api/config/params/analytics/analytics.json.dist /srv/api/config/params/analytics/analytics.json
cp -u /srv/api/config/params/contacts/contacts.json.dist /srv/api/config/params/contacts/contacts.json
cp -u /srv/api/config/params/modules/modules.json.dist /srv/api/config/params/modules/modules.json
cp -u /srv/api/config/params/transit/providers.json.dist /srv/api/config/params/transit/providers.json
cp -u /srv/api/config/params/rdex/clients.json.dist /srv/api/config/params/rdex/clients.json
cp -u /srv/api/config/params/rdex/operator.json.dist /srv/api/config/params/rdex/operator.json
cp -u /srv/api/config/params/rdex/providers.json.dist /srv/api/config/params/rdex/providers.json
cp -u /srv/api/config/params/user/domains.json.dist /srv/api/config/params/user/domains.json
cp -u /srv/api/config/params/user/sso.json.dist /srv/api/config/params/user/sso.json

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX -m o:rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX -m o:rwX var
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX -m o:rwX public/upload
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX -m o:rwX public/upload

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction

		echo "Making sure public / private keys for JWT exist..."
		php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
		setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
		setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
	fi
fi

exec docker-php-entrypoint "$@"
