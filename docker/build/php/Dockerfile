FROM php:7.4-apache

RUN pecl install mongodb \
&& docker-php-ext-enable mongodb

ADD test_clickeat.conf /etc/apache2/sites-available

RUN a2ensite test_clickeat.conf && a2dissite 000-default.conf && a2enmod rewrite