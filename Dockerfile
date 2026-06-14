FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

COPY . /var/www/social-app/

RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/social-app/public\n\
    <Directory /var/www/social-app/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

EXPOSE 80
