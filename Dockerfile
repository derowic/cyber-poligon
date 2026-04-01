FROM php:7.4-apache
# Instalacja rozszerzenia mysqli i pdo_mysql (potrzebne do bazy danych)
RUN docker-php-ext-install mysqli pdo pdo_mysql