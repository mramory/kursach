FROM php:8.2-apache

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_mysql

# Включение mod_rewrite
RUN a2enmod rewrite

# Копирование конфигурации Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

