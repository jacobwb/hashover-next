FROM php:5-apache
RUN apt-get update && apt-get install -y libmcrypt-dev && docker-php-ext-install -j$(nproc) mcrypt 
COPY test.html /var/www/html/
COPY hashover /var/www/html/hashover/
RUN chown -R www-data /var/www/html/hashover
