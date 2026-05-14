FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libonig-dev \
    default-libmysqlclient-dev \
    && docker-php-ext-install mysqli mbstring \
    && docker-php-ext-enable mysqli mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/sites-available/*.conf

WORKDIR /var/www/html
