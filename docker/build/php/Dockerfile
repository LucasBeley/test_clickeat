FROM php:7.4-apache

ADD test_clickeat.conf /etc/apache2/sites-available

RUN a2ensite test_clickeat.conf && a2dissite 000-default.conf