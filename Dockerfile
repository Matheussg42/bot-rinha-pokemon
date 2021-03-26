FROM php:7.4-fpm


# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libmcrypt-dev \
    libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/* \
&& printf "\n" | pecl install imagick \
&& docker-php-ext-enable imagick \
&& docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& php /usr/local/bin/composer install

RUN php artisan migrate:fresh
RUN php artisan queue:work --queue=batalhas & php artisan twitter:listen-for-hash-tags
