FROM php:8.2-apache

# Instalar dependencias del sistema y bibliotecas para PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libzip-dev

# Limpiar cache de apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP requeridas por Laravel y PostgreSQL
RUN docker-php-ext-install pdo_pgsql pgsql zip bcmath gd opcache

# Habilitar mod_rewrite de Apache para rutas limpias de Laravel
RUN a2enmod rewrite

# Configurar el DocumentRoot de Apache apuntando a /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalar Composer para gestionar dependencias PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar Node.js y NPM para compilar assets de Vite
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar el código del proyecto
COPY . .

# Configurar permisos para directorios de almacenamiento de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto estándar HTTP
EXPOSE 80

# Comando por defecto para arrancar Apache
CMD ["apache2-foreground"]
