npm install
php bin/console doctrine:database:create --if-not-exists -n &&
php bin/console doctrine:migrations:migrate -n &&
echo "will run php fpm" && 
php-fpm -F
