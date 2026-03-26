FROM oven/bun:latest AS frontend
WORKDIR /app
COPY package.json bun.lock ./
RUN bun install --frozen-lockfile
COPY . .
RUN bun run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-ansi --optimize-autoloader

FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    yt-dlp \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    unzip \
    git \
    ca-certificates \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" intl pdo_mysql pdo_sqlite zip gd bcmath opcache pcntl \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app
COPY --from=vendor /app/vendor /app/vendor
COPY --from=frontend /app/public/build /app/public/build

RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf \
  && install -d -m 0755 /etc/supervisor/conf.d /var/log \
  && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
  && chmod -R 775 /app/storage /app/bootstrap/cache

COPY docker/start.sh /usr/local/bin/start.sh
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/worker-nginx.conf /etc/supervisor/conf.d/worker-nginx.conf
COPY docker/worker-phpfpm.conf /etc/supervisor/conf.d/worker-phpfpm.conf
COPY docker/worker-laravel.conf /etc/supervisor/conf.d/worker-laravel.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zzz-app.conf
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]
