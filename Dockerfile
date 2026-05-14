FROM php:8.2-apache

RUN docker-php-ext-install mysqli mbstring \
    && docker-php-ext-enable mysqli mbstring

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/sites-available/*.conf

WORKDIR /var/www/html
