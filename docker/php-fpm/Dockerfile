# Stage 1: Build assets
FROM node:22-alpine AS node_build
WORKDIR /app
COPY ./www .
RUN npm install && npm run build

# Warning: container name in docker-compose.dev.yml must match the stage name below
# This is necessary so that nginx can reference the correct php-fpm container
FROM php:8.3-fpm-alpine AS app

# Useful PHP extension installer image, copy binary into your container
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install php extensions
# exit on errors, exit on unset variables, print every command as it is executed
RUN set -eux; \
    install-php-extensions pdo pdo_mysql;

# RUN docker-php-ext-install pdo pdo_mysql

# allow super user - set this if you use Composer as a
# super user at all times like in docker containers
ENV COMPOSER_ALLOW_SUPERUSER=1

# obtain composer using multi-stage build
# https://docs.docker.com/build/building/multi-stage/
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

#Here, we are copying only composer.json and composer.lock (instead of copying the entire source)
# right before doing composer install.
# This is enough to take advantage of docker cache and composer install will
# be executed only when composer.json or composer.lock have indeed changed!-
# https://medium.com/@softius/faster-docker-builds-with-composer-install-b4d2b15d0fff
COPY ./www/composer.* ./

# install
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# copy application files to the working directory
COPY ./www .

# Copy built assets from node_build stage
COPY --from=node_build /app/public/build ./public/build

# run composer dump-autoload --optimize
RUN composer dump-autoload --optimize
