FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    && docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath gd intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis
#RUN pecl channel-update pecl.php.net && pecl install redis && docker-php-ext-enable redis
#RUN pecl install redis-5.3.7 && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Copy php.ini configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage && \
    chmod -R 775 /var/www/bootstrap/cache
