# Use official PHP 8.2 with Apache as base image
FROM php:8.2-apache

# Add OCI labels to link the container image to the GitHub repository
LABEL org.opencontainers.image.source="https://github.com/LasseHSchmidt/SMSTools"
LABEL org.opencontainers.image.description="SMS Tools web application for GatewayAPI"

# Install mysqli extension and enable Apache mod_rewrite
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli \
    && a2enmod rewrite

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy database initialization script outside the web root
COPY db_init.php /usr/local/bin/db_init.php

# Copy application source files into the Apache document root
COPY . /var/www/html/

# Set proper ownership and permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80

# Sync database schema and default group from environment variables before starting Apache
CMD ["sh", "-c", "php /usr/local/bin/db_init.php && apache2-foreground"]
