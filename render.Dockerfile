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

# Create upload directories
RUN mkdir -p /var/www/html/uploads/avatars && \
    chmod -R 777 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html/

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
ENV DATABASE_URL=${DATABASE_URL}
ENV DATABASE_USER=${DATABASE_USER}
ENV DATABASE_PASSWORD=${DATABASE_PASSWORD}
ENV DATABASE_NAME=${DATABASE_NAME}
ENV DATABASE_PORT=${DATABASE_PORT:-5432}

# Create a script to replace database credentials at startup
RUN echo '#!/bin/bash\n\
apache2-foreground' > /usr/local/bin/docker-entrypoint.sh \
&& chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"] 