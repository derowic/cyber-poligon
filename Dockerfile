FROM php:8.3-apache

RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli

# Włącz mod_rewrite (na wszelki wypadek)
RUN a2enmod rewrite

# Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf