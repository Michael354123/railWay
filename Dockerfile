FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar el código al contenedor
COPY . /var/www/html/

# Configurar Apache para usar la variable PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf

# Exponer el puerto dinámico
EXPOSE ${PORT}
