# syntax=docker/dockerfile:1
FROM php:8.2-fpm

# Install system dependencies & libraries required for PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    sqlite3 \
    libsqlite3-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20 for Vite asset compilation
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install official Composer 2 binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP configuration
COPY orderflow_app/docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Copy entrypoint script and make it executable
COPY orderflow_app/docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy application source code from orderflow_app
COPY orderflow_app /var/www/html

# Install Composer dependencies and build frontend assets
RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && npm install \
    && npm run build \
    && rm -rf node_modules

# Set directory ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Expose web port
EXPOSE 8000 9000 10000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
