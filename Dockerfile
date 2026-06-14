FROM php:8.2-apache

RUN apt-get update && apt-get install -y unzip curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

WORKDIR /var/www/social-app
COPY . .
RUN composer install --no-dev --optimize-autoloader

RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/social-app/public\n\
    <Directory /var/www/social-app/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

EXPOSE 80
