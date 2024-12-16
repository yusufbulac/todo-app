FROM php:8.1-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    zlib1g-dev \
    libbz2-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip bz2 intl pdo_mysql opcache bcmath \
    && a2enmod rewrite \
    && rm -rf /tmp/pear

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

COPY . .

ENV TZ Europe/Istanbul

ENTRYPOINT ["sh", "entrypoint.sh"]
