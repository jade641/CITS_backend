# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Generate optimized autoload files
RUN php artisan config:clear && \
    php artisan cache:clear

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache configuration to use public directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Configure Apache to listen on PORT environment variable
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/g' /etc/apache2/sites-available/000-default.conf

# Give write permissions to storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create a startup script that uses PORT variable
RUN echo '#!/bin/bash\n\
# Use PORT environment variable or default to 80\n\
export APACHE_PORT=${PORT:-80}\n\
# Update Apache ports\n\
sed -i "s/Listen .*/Listen ${APACHE_PORT}/" /etc/apache2/ports.conf\n\
sed -i "s/<VirtualHost .*>/<VirtualHost *:${APACHE_PORT}>/" /etc/apache2/sites-available/000-default.conf\n\
# Cache configs\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
# Start Apache\n\
apache2-foreground' > /usr/local/bin/start.sh && \
    chmod +x /usr/local/bin/start.sh

# Expose port (will be overridden by Render)
EXPOSE ${PORT:-80}

# Start Apache with custom script
CMD ["/usr/local/bin/start.sh"]
