FROM baserepo.pishtazteb.com/repo/devops/php:8.2-fpm

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
    supervisor \
    && docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath gd intl sockets

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Set Composer memory limit
ENV COMPOSER_MEMORY_LIMIT=-1

# Copy php.ini configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy project files
COPY . /var/www
RUN rm -rf /var/www/.git

# Install dependencies with no scripts first, then run scripts manually
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Set permissions
RUN mkdir -p /var/www/storage/logs && \
    touch /var/www/storage/logs/worker.log && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage && \
    chown -R www-data:www-data /var/www


# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
