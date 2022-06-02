#!/bin/sh
cp -u /srv/api/config/params/analytics.json.dist /srv/api/config/params/analytics.json
cp -u /srv/api/config/params/contacts.json.dist /srv/api/config/params/contacts.json
cp -u /srv/api/config/params/modules.json.dist /srv/api/config/params/modules.json
cp -u /srv/api/config/publicTransport/providers.json.dist /srv/api/config/publicTransport/providers.json
cp -u /srv/api/config/rdex/clients.json.dist /srv/api/config/rdex/clients.json
cp -u /srv/api/config/rdex/operator.json.dist /srv/api/config/rdex/operator.json
cp -u /srv/api/config/rdex/providers.json.dist /srv/api/config/rdex/providers.json
cp -u /srv/api/config/user/domains.json.dist /srv/api/config/user/domains.json
cp -u /srv/api/config/user/sso.json.dist /srv/api/config/user/sso.json
php /srv/api/bin/console doctrine:database:create --if-not-exists -n
php /srv/api/bin/console doctrine:migrations:migrate -n
