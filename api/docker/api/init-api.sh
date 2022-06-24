#!/bin/sh
cp -u /srv/api/config/params/analytics/analytics.json.dist /srv/api/config/params/analytics/analytics.json
cp -u /srv/api/config/params/contacts/contacts.json.dist /srv/api/config/params/contacts/contacts.json
cp -u /srv/api/config/params/modules/modules.json.dist /srv/api/config/params/modules/modules.json
cp -u /srv/api/config/params/transit/providers.json.dist /srv/api/config/params/transit/providers.json
cp -u /srv/api/config/params/rdex/clients.json.dist /srv/api/config/params/rdex/clients.json
cp -u /srv/api/config/params/rdex/operator.json.dist /srv/api/config/params/rdex/operator.json
cp -u /srv/api/config/params/rdex/providers.json.dist /srv/api/config/params/rdex/providers.json
cp -u /srv/api/config/params/user/domains.json.dist /srv/api/config/params/user/domains.json
cp -u /srv/api/config/params/user/sso.json.dist /srv/api/config/params/user/sso.json
php /srv/api/bin/console doctrine:database:create --if-not-exists -n
php /srv/api/bin/console doctrine:migrations:migrate -n
