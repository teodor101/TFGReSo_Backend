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
    && docker-php-ext-install pdo_pgsql bcmath

# Instalar Composer copiándolo de la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiamos primero composer.* para aprovechar cache de Docker
COPY composer.json composer.lock* ./

# Instalar dependencias de PHP (sin dev y optimizado para prod)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copiar el resto del código del proyecto
COPY . .

# Dar permisos a storage y bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Opcional: generar la cache de config/route/view en build
# RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Puerto donde Laravel va a escuchar
EXPOSE 8000

# Comando de arranque del contenedor
# (si quieres que migre solo al arrancar, descomenta la línea de migrate)
CMD php artisan migrate --force || true && \
    php artisan serve --host=0.0.0.0 --port=8000