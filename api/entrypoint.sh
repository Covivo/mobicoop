openssl genrsa -aes256 -passout pass:ca4ffed31ee358cc7c7083af6e5773cd -out config/jwt/private.pem 4096 &&
openssl rsa -in config/jwt/private.pem -passin pass:ca4ffed31ee358cc7c7083af6e5773cd -pubout -out config/jwt/public.pem &&
chmod 777 config/jwt/* &&
cp config/rdex/clients.json.dist config/rdex/clients.json &&
cp config/rdex/operator.json.dist config/rdex/operator.json &&
cp config/rdex/providers.json.dist config/rdex/providers.json &&
cp config/user/domains.json.dist config/user/domains.json &&
cp config/user/sso.json.dist config/user/sso.json &&
cp config/csvExport/csvExport.json.dist config/csvExport/csvExport.json &&
cp config/params/commands.json.dist config/params/commands.json &&
cp config/params/modules.json.dist config/params/modules.json &&
cp config/params/eecService.json.dist config/params/eecService.json &&
cp config/params/contacts.json.dist config/params/contacts.json &&
cp config/params/reminders.json.dist config/params/reminders.json &&
php bin/console doctrine:database:create --if-not-exists -n &&
php bin/console doctrine:migrations:migrate -n
