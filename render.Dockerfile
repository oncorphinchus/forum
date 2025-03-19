FROM php:8.1-apache

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql pdo_pgsql \
    && a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create upload directories
RUN mkdir -p /var/www/html/uploads/avatars && \
    chmod -R 777 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html/

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Use PostgreSQL configuration for production
COPY config.pgsql.php /var/www/html/config.php

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# Apache configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Define environment variables that can be overridden
ENV IS_PRODUCTION=true
ENV MYSQL_HOST=${MYSQL_HOST}
ENV MYSQL_USER=${MYSQL_USER}
ENV MYSQL_PASSWORD=${MYSQL_PASSWORD}
ENV MYSQL_DATABASE=${MYSQL_DATABASE}
ENV DATABASE_PORT=${DATABASE_PORT:-5432}

# Create a script to replace database credentials at startup
RUN echo '#!/bin/bash\n\
apache2-foreground' > /usr/local/bin/docker-entrypoint.sh \
&& chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"] 