# syntax=docker/dockerfile:1
FROM php:8.2-apache

# Install system dependencies required by mysqli and gd extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd mysqli pdo_mysql zip

# Enable Apache rewrite module for pretty URLs if needed
RUN a2enmod rewrite

# Copy application source into the container
COPY . /var/www/html

# Ensure the document root has the correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose the default Apache port
EXPOSE 80

# Use the default Apache start command provided by the base image
CMD ["apache2-foreground"]
