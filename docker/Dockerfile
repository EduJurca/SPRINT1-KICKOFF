# Carsharing Platform - Web Server Container
# Base: PHP 8.2 with Apache
# Date: October 2025

FROM php:8.2-apache

LABEL maintainer="Carsharing Platform Team"
LABEL description="Production-ready web server for carsharing platform"
LABEL version="1.0.0"

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libssl-dev \
    pkg-config \
    netcat-openbsd \
    wget \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip

# Install MongoDB PHP extension via PECL
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install MongoDB PHP Library via Composer
RUN composer require mongodb/mongodb --working-dir=/tmp \
    && mkdir -p /var/www/html/vendor \
    && cp -r /tmp/vendor/* /var/www/html/vendor/ \
    && rm -rf /tmp/vendor /tmp/composer.json /tmp/composer.lock

# Install Tailwind CSS standalone CLI
RUN wget -O /usr/local/bin/tailwindcss https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64 \
    && chmod +x /usr/local/bin/tailwindcss

# Enable Apache modules
RUN a2enmod rewrite headers ssl

# Copy Apache configuration files
COPY docker/config/apache2.conf /etc/apache2/apache2.conf
COPY docker/config/security.conf /etc/apache2/conf-available/security.conf
COPY docker/config/carsharing.conf /etc/apache2/sites-available/carsharing.conf
COPY docker/config/php.ini /usr/local/etc/php/php.ini

# Enable security configuration and carsharing site
RUN a2enconf security \
    && a2dissite 000-default.conf \
    && a2ensite carsharing.conf

# Create necessary directories
RUN mkdir -p /var/www/html/public \
    /var/www/html/api \
    /var/www/html/js \
    /var/www/html/css \
    /var/www/html/lang \
    /var/log/apache2

# Copy application files
COPY web /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/public \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Copy entrypoint script
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Use custom entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start Apache in foreground
CMD ["apache2-foreground"]
