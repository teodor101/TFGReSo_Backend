# Imagen base de PHP
FROM php:8.2-cli

# Carpeta de trabajo dentro del contenedor
WORKDIR /var/www/html

# Instalar dependencias de sistema necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install pdo_pgsql bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar archivos de dependencias primero (mejor caching)
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Ahora copiar el resto del código
COPY . .

RUN php artisan storage:link || true

# Ejecutar scripts post-install de Composer
RUN composer dump-autoload --optimize

# Crear directorios si no existen y dar permisos
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Puerto donde Laravel va a escuchar
EXPOSE 8000

# Comando de arranque del contenedor
CMD php artisan migrate --force || true && \
    php artisan serve --host=0.0.0.0 --port=8000