FROM dunglas/frankenphp:1-php8.3 AS app

RUN install-php-extensions \
    intl \
    zip \
    pdo_pgsql \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

COPY . .

RUN composer dump-autoload --classmap-authoritative --no-dev \
    && php bin/console cache:clear --env=prod \
    && chown -R www-data:www-data var

COPY Caddyfile /etc/frankenphp/Caddyfile

ENV APP_ENV=prod

EXPOSE 80
