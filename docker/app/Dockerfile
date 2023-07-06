FROM php:8.1-fpm-alpine3.18

RUN apk add --update --no-cache \
    curl \
    nano \
    git \
    # for usermod and groupmod
    shadow \
    # for envsubst command
    gettext \
    # to run build as www-data user
    sudo \
    supervisor \
    # for amqp php extension
    rabbitmq-c \
    # for zip php extension
    libzip \
    # for gd php extension \
    freetype \
    # front-end
    nodejs \
    npm \
    && npm install --global yarn

RUN apk add --virtual .build-deps \
    g++ make \
    # pecl install requires autoconf
    autoconf \
    # for amqp php extension
    rabbitmq-c-dev \
    # for zip php extension
    libzip-dev \
    # to build intl php extension
    icu-dev \
    # for gd php extension
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype \
    && docker-php-ext-install -j$(nproc) pdo_mysql sysvshm opcache intl zip gd \
    && pecl install apcu-5.1.22 \
    && pecl install amqp-1.11.0 \
    && apk del --purge .build-deps \
    && docker-php-ext-enable apcu amqp \
    && rm -rf /var/cache/apk/*

ARG PHP_OPCACHE_ENABLE="1"
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini.template
RUN export PHP_OPCACHE_ENABLE=$PHP_OPCACHE_ENABLE; \
    envsubst < /usr/local/etc/php/conf.d/opcache.ini.template > /usr/local/etc/php/conf.d/40-opcache.ini
RUN rm -f /usr/local/etc/php/conf.d/opcache.ini.template

# install gnu-libiconv and set LD_PRELOAD env to make iconv work fully on Alpine image.
# see https://github.com/docker-library/php/issues/240#issuecomment-763112749
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

# Enable php8-xdebug if $PHP_XDEBUG_MODE is not empty
ARG PHP_XDEBUG_MODE=off
ARG PHP_XDEBUG_CLIENT_PORT=5902
ARG PHP_XDEBUG_CLIENT_HOST=host.docker.internal
COPY xdebug.ini  /etc/php8/conf.d/xdebug.ini.template
RUN if [[ "$PHP_XDEBUG_MODE" != "" ]]; then \
        apk add --no-cache php8-pecl-xdebug; \
        export PHP_XDEBUG_MODE=$PHP_XDEBUG_MODE; \
        export PHP_XDEBUG_CLIENT_PORT=$PHP_XDEBUG_CLIENT_PORT; \
        export PHP_XDEBUG_CLIENT_HOST=$PHP_XDEBUG_CLIENT_HOST; \
        envsubst < /etc/php8/conf.d/xdebug.ini.template > /etc/php8/conf.d/xdebug.ini; \
    fi
RUN rm -f /etc/php8/conf.d/xdebug.ini.template

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG SYMFONY_CLI_VERSION
RUN wget https://github.com/symfony/cli/releases/download/v$SYMFONY_CLI_VERSION/symfony_linux_amd64.gz; \
    gzip -d symfony_linux_amd64.gz; \
    chmod +x symfony_linux_amd64; \
    mv symfony_linux_amd64 /usr/local/bin/symfony

ARG UID=82
ARG GID=82
RUN set -x; \
    usermod -u $UID www-data; \
    groupmod -g $GID www-data; \
    mkdir -p /root/.config/yarn; \
    chmod -R 777 /root

COPY php.ini /usr/local/etc/php/php.ini
COPY supervisor.conf /etc/supervisor/supervisor.conf

ARG APP_ENV=prod
ENV APP_ENV $APP_ENV
ARG COMPOSER_NO_DEV
ENV COMPOSER_NO_DEV $COMPOSER_NO_DEV

WORKDIR /var/www

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod u+x /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]
