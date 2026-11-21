# Use official PHP 8.5 with Apache as base image
FROM php:8.5-apache

# Add OCI labels to link the container image to the GitHub repository
LABEL org.opencontainers.image.source="https://github.com/LasseHSchmidt/SMSTools"
LABEL org.opencontainers.image.description="SMS Tools web application for GatewayAPI"

# Install mysqli extension, enable Apache mod_rewrite, and hide server version headers
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli \
    && a2enmod rewrite \
    && echo "ServerTokens Prod\nServerSignature Off" > /etc/apache2/conf-available/security-hardening.conf \
    && a2enconf security-hardening

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy database initialization script outside the web root
COPY db_init.php /usr/local/bin/db_init.php

# Copy application source files into the Apache document root
COPY . /var/www/html/

# Remove CLI-only scripts, config templates, and build files from the public web root
RUN rm -f /var/www/html/db_init.php \
          /var/www/html/php.ini \
          /var/www/html/Dockerfile \
          /var/www/html/docker-compose.yml \
          /var/www/html/.env* \
          /var/www/html/.gitignore \
          /var/www/html/.dockerignore \
    && chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80

# Sync database schema and default group from environment variables before starting Apache
CMD ["sh", "-c", "php /usr/local/bin/db_init.php && apache2-foreground"]
