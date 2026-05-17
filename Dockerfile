FROM docker.io/tiredofit/freescout:latest

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
COPY vendor/ /www/html/vendor/
COPY artisan /www/html/artisan
COPY composer.json /www/html/composer.json
COPY composer.lock /www/html/composer.lock
COPY package.json /www/html/package.json
COPY server.php /www/html/server.php
COPY webpack.mix.js /www/html/webpack.mix.js
COPY .env.example /www/html/.env.example

COPY install/ /
RUN chmod +x /etc/cont-init.d/31-app-key-sync \
    && chown -R nginx:nginx /www/html \
    && chmod -R ug+rwX /www/html/bootstrap/cache /www/html/storage

EXPOSE 80
