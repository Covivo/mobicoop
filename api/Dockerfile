ARG PHP_VERSION=8.0
ARG NGINX_VERSION=1.21.3

# PHP stage
FROM php:${PHP_VERSION}-fpm-alpine AS mobicoop_platform_api_php

# persistent / runtime deps
RUN apk add --no-cache \
	acl \
	fcgi \
	file \
	gettext \
	git \
	gnu-libiconv \
	nodejs \
	npm \
	tree \
	vim \
	zsh \
	;

# install gnu-libiconv and set LD_PRELOAD env to make iconv work fully on Alpine image.
# see https://github.com/docker-library/php/issues/240#issuecomment-763112749
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

ARG APCU_VERSION=5.1.20
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
	$PHPIZE_DEPS \
	icu-dev \
	libpng-dev \
	freetype-dev \
	jpeg-dev \
	libjpeg-turbo-dev \
	libzip-dev \
	mysql-dev \
	zlib-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-configure gd --with-freetype --with-jpeg; \
	docker-php-ext-install -j$(nproc) \
	gd \
	intl \
	pdo_mysql \
	zip \
	; \
	pecl install \
	apcu-${APCU_VERSION} \
	xdebug \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
	apcu \
	opcache \
	xdebug \
	; \
	\
	runDeps="$( \
	scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
	| tr ',' '\n' \
	| sort -u \
	| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
	\
	apk del .build-deps; \
	sh -c "$(wget https://raw.github.com/robbyrussell/oh-my-zsh/master/tools/install.sh -O -)"

RUN npm install -g request-dispatcher

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/api/conf.d/mobicoop-platform-api.prod.ini $PHP_INI_DIR/conf.d/mobicoop-platform-api.ini
COPY docker/api/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

VOLUME /var/run/php

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/api

# build for production
ARG APP_ENV=prod

# prevent the reinstallation of vendors at every changes in the source code
# COPY composer.json composer.lock symfony.lock ./
COPY composer.json ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-scripts --no-progress; \
	composer clear-cache;

# copy only specifically what we need
COPY .env .env.*.local ./
COPY bin bin/
COPY config config/
# COPY migrations migrations/
COPY public public/
COPY src src/
COPY templates templates/

RUN set -eux; \
	mkdir -p var/cache var/log; \
	# composer dump-autoload --classmap-authoritative --no-dev; \
	# composer dump-env prod; \
	# composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync
VOLUME /srv/api/var

COPY docker/api/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY docker/api/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENV SYMFONY_PHPUNIT_VERSION=9

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

# NGINX stage
FROM nginx:${NGINX_VERSION} AS mobicoop_platform_api_nginx

WORKDIR /srv/api

COPY --from=mobicoop_platform_api_php /srv/api/public api/public/
COPY docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf
