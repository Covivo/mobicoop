#!/bin/sh
php /srv/api/bin/console doctrine:database:create --if-not-exists -n
php /srv/api/bin/console doctrine:migrations:migrate -n
