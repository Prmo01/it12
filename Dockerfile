# Stage 1 - Build Frontend (Vite)
FROM node:18 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2 - Backend (Laravel + PHP + Composer)
FROM php:8.2-fpm AS backend

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev zip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies (this layer will be cached if composer files don't change)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy app files
COPY . .

# Copy built frontend from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Ensure storage directories exist and set proper permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Run composer scripts and Laravel setup (skip if .env doesn't exist)
RUN composer dump-autoload --optimize || true \
    && php artisan config:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true \
    && php artisan storage:link || true

# Stage 3 - Final (Nginx + PHP-FPM)
FROM php:8.2-fpm

# Install system dependencies and Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    gettext-base \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure PHP-FPM to listen on TCP (simpler than Unix socket for Docker)
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/user = www-data/user = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = www-data/' /usr/local/etc/php-fpm.d/www.conf

# Create nginx templates directory and copy configuration
RUN mkdir -p /etc/nginx/templates
COPY nginx.conf /etc/nginx/templates/default.conf.template

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Copy application from backend stage
COPY --from=backend --chown=www-data:www-data /var/www /var/www

WORKDIR /var/www

# Expose port (Render will set PORT env var)
EXPOSE 10000

ENTRYPOINT ["/docker-entrypoint.sh"]