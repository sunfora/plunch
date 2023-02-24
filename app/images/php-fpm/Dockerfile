FROM php:8.1.0-fpm

RUN apt update -y && apt install libicu-dev -y
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install intl 
