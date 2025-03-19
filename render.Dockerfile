FROM php:8.1-apache

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Create upload directories
RUN mkdir -p /var/www/html/uploads/avatars && \
    chmod -R 777 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html/

# Use production configuration
COPY config.production.php /var/www/html/config.php

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# Apache configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Define environment variables that can be overridden
ENV MYSQL_HOST=${MYSQL_HOST}
ENV MYSQL_USER=${MYSQL_USER}
ENV MYSQL_PASSWORD=${MYSQL_PASSWORD}
ENV MYSQL_DATABASE=${MYSQL_DATABASE}

# Create a script to replace database credentials at startup
RUN echo '#!/bin/bash\n\
sed -i "s/DB_HOST/'"${MYSQL_HOST}"'/g" /var/www/html/config.php\n\
sed -i "s/DB_USER/'"${MYSQL_USER}"'/g" /var/www/html/config.php\n\
sed -i "s/DB_PASS/'"${MYSQL_PASSWORD}"'/g" /var/www/html/config.php\n\
sed -i "s/DB_NAME/'"${MYSQL_DATABASE}"'/g" /var/www/html/config.php\n\
apache2-foreground' > /usr/local/bin/docker-entrypoint.sh \
&& chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"] 