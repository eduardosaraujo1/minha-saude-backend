#! /usr/bin/bash

npm i
composer install

npm run build

php artisan migrate --force


