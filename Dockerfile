FROM php:8.2-apache

RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mysqli curl \
    && rm -rf /var/lib/apt/lists/*

EXPOSE 80
