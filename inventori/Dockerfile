FROM php:8.2-apache

RUN a2enmod rewrite

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy app
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/logs

EXPOSE 80
