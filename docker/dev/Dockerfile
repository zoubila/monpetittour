FROM dunglas/frankenphp:1-php8.3

RUN install-php-extensions \
    intl \
    zip \
    pdo_pgsql \
    opcache \
    xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

ENV APP_ENV=dev
ENV XDEBUG_MODE=debug,develop

COPY Caddyfile /etc/frankenphp/Caddyfile
