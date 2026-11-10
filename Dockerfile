# YouthConnect – container image (Apache + PHP 8.3). Document root is /public, so source and config are never web-reachable.
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers deflate \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html
RUN mkdir -p storage/uploads storage/logs && chown -R www-data:www-data storage

# Config comes from environment variables (see config/config.php): DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_FORCE_HTTPS ...
EXPOSE 80
# Bind to the port some hosts inject as $PORT (Render, Railway, Fly).
CMD ["sh", "-c", "sed -ri \"s/Listen 80/Listen ${PORT:-80}/\" /etc/apache2/ports.conf && sed -ri \"s/:80>/:${PORT:-80}>/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
