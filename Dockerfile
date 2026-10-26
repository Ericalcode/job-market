FROM php:8.2.35-apache
RUN docker-php-ext-install mysqli pdo_mysql
COPY . /var/www/html/
EXPOSE 80
