# Budgie - Personal Finance App Dockerfile
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install xml

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create necessary directories
RUN mkdir -p /var/www/html/logs \
    && mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/public/uploads

# Configure Apache
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
