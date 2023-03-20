# FROM php:7.4-apache
# # RUN apt-get update && apt-get install git libicu-dev  g++ zlib1g-dev libmcrypt-dev -y
# # RUN docker-php-source extract
# # # RUN docker-php-ext-install zip mcrypt
# # RUN a2enmod rewrite
# # RUN service apache2 restart


# COPY ./public/ /var/www/html/
# COPY ./composer.json /var/www/

# RUN usermod -u 1000 www-data
# RUN chown -R www-data:www-data /var/www/

FROM php:7.4-apache

RUN apt-get update && apt-get install git libicu-dev  g++ zlib1g-dev libzip-dev -y
RUN docker-php-source extract
RUN docker-php-ext-install zip
RUN a2enmod rewrite
RUN service apache2 restart
RUN a2enmod rewrite
# RUN mkdir -p /etc/apache2/ssl
# RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# COPY ./ssl/*.pem /etc/apache2/ssl/
COPY ./ /var/www/html/
COPY ./composer.json /var/www/
COPY ./docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN usermod -u 1000 www-data
RUN chown -R www-data:www-data /var/www/

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --filename=composer --install-dir=/usr/local/bin
RUN php -r "unlink('composer-setup.php');"
RUN cd /var/www/ && composer install

EXPOSE 80
