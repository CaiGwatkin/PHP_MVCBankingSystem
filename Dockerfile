FROM php:7.1

MAINTAINER Andrew Gilman <a.gilman@massey.ac.nz>

RUN apt-get update && apt-get upgrade -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpq-dev \
    libmagickwand-dev \
    libmcrypt-dev \
    libmcrypt-dev \
    libpng12-dev \
    libmemcached-dev \
    libssl-dev \
    libssl-doc \
    libsasl2-dev \
    zlib1g-dev \
    && docker-php-ext-install -j$(nproc) bz2 iconv mcrypt mbstring pdo_mysql mysqli pgsql pdo_pgsql zip \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Install xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install Redis
RUN pecl install redis && docker-php-ext-enable redis

# bz2 gd iconv mbstring mcrypt mysqli mongodb pdo_mysql pdo_pgsql pgsql redis xdebug zip