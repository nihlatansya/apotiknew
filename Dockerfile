FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    git \
    libzip-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql zip

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
