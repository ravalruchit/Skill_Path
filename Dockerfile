FROM php:8.2-apache
RUN a2enmod rewrite
COPY ./Skill-platform/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html
