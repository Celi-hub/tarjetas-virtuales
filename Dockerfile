# Momentia — imagen de la aplicación (PHP 8.2 + Apache)
FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Subidas de archivos: límites razonables para fotos y comprobantes
RUN { \
      echo 'upload_max_filesize=10M'; \
      echo 'post_max_size=12M'; \
      echo 'date.timezone=America/Argentina/Cordoba'; \
    } > /usr/local/etc/php/conf.d/momentia.ini

WORKDIR /var/www/html
COPY . /var/www/html

# Carpetas donde la app escribe (fotos del book, hitos, comprobantes)
RUN mkdir -p uploads/book uploads/hitos uploads/comprobantes \
    && chown -R www-data:www-data uploads

EXPOSE 80
