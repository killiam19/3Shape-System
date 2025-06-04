# Usar PHP con Apache
FROM php:8.2-apache

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar archivos del proyecto al contenedor
COPY . /var/www/html/

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]