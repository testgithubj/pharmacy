# Base: PHP-FPM (Alpine) + Composer + Nginx + Supervisor
FROM php:8.2-fpm-alpine

# System deps for PHP extensions & nginx & supervisor
RUN apk add --no-cache \
    nginx supervisor curl git bash icu-dev libzip-dev oniguruma-dev \
    libpng libpng-dev libjpeg-turbo-dev libwebp-dev zlib zlib-dev \
    libxml2-dev

# Common php extensions (Laravel-friendly)
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) pdo_mysql mbstring bcmath opcache gd intl zip

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Workdir & copy code
WORKDIR /var/www/html
COPY . /var/www/html

# Install PHP deps (no-dev, optimize autoload)
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; fi

# Laravel permissions (থাকলে প্রযোজ্য)
RUN mkdir -p storage bootstrap/cache || true \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R ug+rwx storage bootstrap/cache || true

# Nginx & Supervisor config
COPY nginx/default.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

# php-fpm listen 9000 (default)
EXPOSE 8080

# Health endpoint (optional tiny check file)
RUN echo -e "<?php http_response_code(200); echo 'ok';" > /var/www/html/public/health.php || true

CMD ["/usr/bin/supervisord","-c","/etc/supervisord.conf"]
