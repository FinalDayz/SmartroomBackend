git reset --hard
git pull
php composer.phar install --no-dev --optimize-autoloader
APP_ENV=prod
APP_DEBUG=0
php bin/console cache:clear
