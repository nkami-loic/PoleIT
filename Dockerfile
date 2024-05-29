FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql intl zip opcache

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers Symfony
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installer les dépendances PHP avec Composer
RUN composer install --no-scripts --no-autoloader
# Générer l'autoloader
RUN composer dump-autoload --optimize --no-dev
# Donner les permissions nécessaires
RUN chown -R www-data:www-data /var/www/html

# Set environment variables for Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
