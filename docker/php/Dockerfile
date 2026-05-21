FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    curl \
    curl-dev \
    unzip \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    sqlite-dev \
    mysql-client

RUN docker-php-ext-install \
    curl \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/entrypoint.sh /usr/local/bin/cep-clientes-entrypoint
RUN chmod +x /usr/local/bin/cep-clientes-entrypoint

ENTRYPOINT ["cep-clientes-entrypoint"]
CMD ["php-fpm"]