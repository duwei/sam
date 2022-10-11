FROM php:8.1-fpm-alpine

ADD ./php/www.conf /usr/local/etc/php-fpm.d/

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

RUN mkdir -p /var/www/html

RUN chown laravel:laravel /var/www/html

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql bcmath

RUN apk add --no-cache \
    git \
    yarn \
    autoconf \
    g++ \
    make \
    openssl-dev

RUN pecl install -o -f redis &&  rm -rf /tmp/pear &&  docker-php-ext-enable redis

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN { \
  echo 'short_open_tag = On'; \
  echo 'fastcgi.logging = 1'; \
  echo 'opcache.enable=1'; \
  echo 'opcache.optimization_level=0x7FFFBBFF' ; \
  echo 'opcache.revalidate_freq=0'; \
  echo 'opcache.validate_timestamps=1'; \
  echo 'opcache.memory_consumption=128'; \
  echo 'opcache.interned_strings_buffer=8'; \
  echo 'opcache.max_accelerated_files=4000'; \
  echo 'opcache.revalidate_freq=60'; \
  echo 'opcache.fast_shutdown=1'; \
  echo 'upload_max_filesize=32M'; \
  echo 'post_max_size=32M'; \
} > /usr/local/etc/php/conf.d/overrides.ini
