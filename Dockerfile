FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar el código al contenedor
COPY . /var/www/html/

# Exponer puerto
EXPOSE 80
