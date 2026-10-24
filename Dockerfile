FROM php:8.3-fpm-alpine
RUN docker-php-ext-install pdo_mysql
WORKDIR /var/www/html
COPY app/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 9000
