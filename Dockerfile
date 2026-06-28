FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql && \
    a2enmod rewrite && \
    docker-php-ext-install mysqli

# Custom php.ini
RUN echo "upload_max_filesize=32M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "post_max_size=32M" >> /usr/local/etc/php/conf.d/custom.ini

# Entrypoint: auto-seed, then start Apache
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
