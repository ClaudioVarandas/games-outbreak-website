FROM composer:2 as composer
FROM php:8.1-fpm as base

ENV COMPOSER_MEMORY_LIMIT='-1'

ARG USER_UID=82
ARG USER_GID=82

# Set www-data to have UID 1000
RUN usermod -u ${USER_UID} www-data;

RUN apt-get update && \
    apt-get install -y --force-yes --no-install-recommends \
        libzip-dev \
        libz-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libfreetype6-dev \
        libssl-dev \
        libmagickwand-dev \
        libxml2-dev \
        libreadline-dev \
        libgmp-dev \
        cron \
        vim \
        unzip \
        wget

# Pickle - PHP Extension installer
# See https://wiki.php.net/rfc/deprecate-pear-include-composer
RUN wget https://github.com/FriendsOfPHP/pickle/releases/latest/download/pickle.phar && \
    mv pickle.phar /usr/local/sbin/pickle && \
    chmod +x /usr/local/sbin/pickle

RUN pickle -n install redis@5.3.7
RUN docker-php-ext-enable redis

RUN docker-php-ext-install exif && \
    docker-php-ext-install zip && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install intl

RUN pecl install imagick
RUN docker-php-ext-enable imagick

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Copy files
COPY ./php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./php/php.ini /usr/local/etc/php/conf.d/99-php.ini

RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
RUN ln -s /usr/local/bin/docker-entrypoint.sh /

ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 9000
CMD ["php-fpm"]
