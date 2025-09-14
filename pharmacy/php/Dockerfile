FROM php:8.3-fpm
WORKDIR /var/www/html

# প্রয়োজনীয় এক্সটেনশন (আপনার অ্যাপ অনুযায়ী বাড়াতে পারেন)
RUN docker-php-ext-install pdo pdo_mysql

# Composer (থাকলে ডিপেন্ডেন্সি ইন্সটল করতে সুবিধা)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# কোড কন্টেইনারে কপি (volume দিয়েও আসবে; তাই এটা optional)
COPY . .

# composer.json থাকলে ইন্সটল, না থাকলে স্কিপ
RUN [ -f composer.json ] && composer install --no-dev --optimize-autoloader || true
