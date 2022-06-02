#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX o:rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX o:rwX var
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX o:rwX public/upload
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX o:rwX public/upload

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction

		echo "Making sure public / private keys for JWT exist..."
		php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
		setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
		setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
	fi
fi

exec docker-php-entrypoint "$@"
