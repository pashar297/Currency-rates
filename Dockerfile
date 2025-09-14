FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    cron \
    procps \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

COPY config/crontab/crontab /etc/cron.d/app-cron

RUN chmod 0644 /etc/cron.d/app-cron && \
    crontab /etc/cron.d/app-cron && \
    touch /var/log/cron.log

RUN echo 'DATABASE_URL="mysql://symfony_user:symfony_password@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4"' >> /etc/environment

CMD cron && php-fpm
