# Rebuilt on the patched PHP base (nfrastack/laravel 1.0.9 = PHP 8.3.36+, fixes CVE-2026-109618/109619).
# nfrastack/freescout:2.1.5-php8.3 was built in July and still ships the vulnerable PHP, and nfrastack no longer
# publishes 8.3 freescout images. nfrastack-base/ is their 2.1.5 freescout layer (MIT) minus the upstream
# FreeScout download, because this fork provides the whole application source below.
FROM docker.io/nfrastack/laravel:1.0.9-php8.3-alpine_3.24

LABEL org.opencontainers.image.source="https://github.com/SamBlackband/freescout"

ENV IMAGE_NAME="nfrastack/freescout" \
    IMAGE_REPO_URL="https://github.com/nfrastack/container-freescout/"

COPY nfrastack-base/LICENSE /usr/src/container/LICENSE
COPY nfrastack-base/rootfs/ /

RUN echo "" && \
    BUILD_ENV=" \
                10-nginx/NGINX_SITE_ENABLED=freescout \
                20-php-fpm/PHP_MODULE_ENABLE_GNUPG=TRUE \
                20-php-fpm/PHP_MODULE_ENABLE_ICONV=TRUE \
                20-php-fpm/PHP_MODULE_ENABLE_IGBINARY=TRUE \
                20-php-fpm/PHP_MODULE_ENABLE_PCNTL=TRUE \
                20-php-fpm/PHP_MODULE_ENABLE_PDO_PGSQL=TRUE \
                30-laravel/APP_NAME=FreeScout \
                30-laravel/DB_TYPE=mysql \
                30-laravel/ENABLE_LARAVEL_WORKER=FALSE \
                30-laravel/LARAVEL_COMPOSER_SETUP=FALSE \
                30-laravel/LARAVEL_CONFIGURE_APP_KEY=TRUE \
                30-laravel/LARAVEL_CONFIGURE_DB=TRUE \
                30-laravel/LARAVEL_COPY_ENV_EXAMPLE=FALSE \
                30-laravel/LARAVEL_ENV_PREFIX=FREESCOUT_ \
                30-laravel/LARAVEL_IMAGE_MODE=production \
                30-laravel/LARAVEL_INSTALL_DATA_PATH=/container/data/freescout \
                30-laravel/LARAVEL_MIGRATE_ON_FIRST_INSTALL=FALSE \
                30-laravel/LARAVEL_NGINX_ENABLE_STATIC_PATHS=FALSE \
                30-laravel/LARAVEL_NPM_SETUP=FALSE \
              " \
              && \
    FREESCOUT_BUILD_DEPS_ALPINE=" \
                                " \
                                && \
    FREESCOUT_RUN_DEPS_ALPINE=" \
                                    expect \
                                    git \
                              " \
                              && \
    FREESCOUT_BUILD_DEPS_DEBIAN=" \
                                " \
                                && \
    FREESCOUT_RUN_DEPS_DEBIAN=" \
                                    expect \
                                    git \
                              " \
                              && \
    source /container/base/functions/container/build && \
    container_build_log image && \
    package update && \
    package upgrade && \
    package install \
                    FREESCOUT_BUILD_DEPS \
                    FREESCOUT_RUN_DEPS \
                    && \
    php-ext prepare && \
    php-ext reset && \
    php-ext enable core && \
    container_build_log add "FreeScout 1.8.219-handled" "https://github.com/SamBlackband/freescout" && \
    package remove FREESCOUT_BUILD_DEPS && \
    package cleanup

ENV ENABLE_AUTO_UPDATE=FALSE

WORKDIR /www/html

COPY app/ /www/html/app/
COPY bootstrap/ /www/html/bootstrap/
COPY config/ /www/html/config/
COPY database/ /www/html/database/
COPY Modules/ /www/html/Modules/
COPY overrides/ /www/html/overrides/
COPY public/ /www/html/public/
COPY resources/ /www/html/resources/
COPY routes/ /www/html/routes/
COPY storage/ /www/html/storage/
COPY vendor/ /www/html/vendor/
COPY artisan /www/html/artisan
COPY composer.json /www/html/composer.json
COPY composer.lock /www/html/composer.lock
COPY package.json /www/html/package.json
COPY server.php /www/html/server.php
COPY webpack.mix.js /www/html/webpack.mix.js
COPY .env.example /www/html/.env.example

COPY install/ /
RUN find /container -type f \( -path '*/init/*' -o -path '*/functions/*' -o -path '*/defaults/*' -o -path '*/run/*' -o -path '*/nginx/*' \) -exec sed -i 's/\r$//' {} + \
    && chmod +x /container/init/init.d/40-freescout /container/init/init.d/50-handled-freescout /container/run/available/40-freescout-worker/run /container/run/available/41-freescout-scheduler/run \
    && mkdir -p /www/html/storage/logs /www/html/storage/app/public /www/html/storage/framework/cache/data /www/html/storage/framework/sessions /www/html/storage/framework/views

EXPOSE 80
