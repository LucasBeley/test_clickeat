FROM php:7.4-apache-buster

RUN apt update && apt install -y git unzip zip \
&&curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& pecl install mongodb \
&& docker-php-ext-enable mongodb

ADD test_clickeat.conf /etc/apache2/sites-available

RUN a2ensite test_clickeat.conf && a2dissite 000-default.conf && a2enmod rewrite

COPY ${APP_FOLDER} /var/www/html

RUN composer install