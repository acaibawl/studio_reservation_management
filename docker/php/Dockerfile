FROM php:8.4.7-fpm

RUN apt-get update && \
    apt-get install -y \
    git \
    zip \
    unzip \
    vim \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libfontconfig1 \
    libxrender1 \
    libicu-dev

# php設定
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install pdo_mysql mysqli exif
RUN docker-php-ext-install intl
RUN cd /usr/bin && curl -s http://getcomposer.org/installer | php && ln -s /usr/bin/composer.phar /usr/bin/composer

# Redis
RUN git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis -b 6.2.0
RUN docker-php-ext-install redis

WORKDIR /var/www/backend
