FROM mobicoop/php-node-chromium
RUN mkdir -p /var/www/docker
RUN mkdir docker
COPY ./api_entrypoint.sh docker

RUN npm install
WORKDIR /var/www/
RUN npm install-api
RUN php bin/console doctrine:database:create --if-not-exists -n
RUN php bin/console doctrine:migrations:migrate -n

RUN api/ composer install

WORKDIR /var/www/