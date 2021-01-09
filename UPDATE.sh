git reset --hard
git pull
yes | php composer.phar install --no-dev --optimize-autoloader
APP_ENV=prod
APP_DEBUG=0
php bin/console cache:clear
chown www-data:www-data -R ./var