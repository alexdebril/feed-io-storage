FROM php:7.4-cli

RUN mkdir /var/cache/composer

RUN set -ex &&\
 apt-get update &&\
 apt-get install -y --no-install-recommends git unzip libzip-dev zlib1g-dev &&\
 docker-php-ext-install -j 8 zip &&\
 pecl install mongodb &&\
 docker-php-ext-enable mongodb &&\
 rm -rf /var/lib/apt/lists/* && rm -rf /tmp/*

WORKDIR /src

RUN set -ex &&\
 curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
