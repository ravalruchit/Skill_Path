# Use PHP 8.2 with Apache web server
FROM php:8.2-apache

# Enable Apache Mod_Rewrite (important for many PHP apps)
RUN a2enmod rewrite

# Copy all your project files into the server
COPY . /var/www/html/

# Set permissions so the server can read your files
RUN chown -R www-data:www-data /var/www/html