FROM php:7.4-fpm


# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libmcrypt-dev \
    libmagickwand-dev --no-install-recommends \
    ghostscript \
&& pecl install imagick \
&& docker-php-ext-enable imagick \
&& docker-php-ext-install pdo_mysql mbstring gd

COPY . .

RUN mkdir /tmp/img
RUN cp ./policy.xml /etc/ImageMagick-6

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& php /usr/local/bin/composer install

RUN php artisan migrate
CMD php artisan queue:work --queue=batalhas & php artisan twitter:listen-for-hash-tags
