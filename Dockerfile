# Use an official PHP runtime as a parent image
FROM php:8.2-alpine

# Install system dependencies
RUN apk --update add \
    curl \
    bash \
    libzip-dev \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add Symfony install script and execute it
ADD install_symfony.sh /install_symfony.sh
RUN chmod +x /install_symfony.sh
RUN /install_symfony.sh

# Set working directory in the container
WORKDIR /var/www/html

# Copy the current directory contents into the container
COPY . /var/www/html

# Install dependencies with Composer
RUN composer install

# Change owner of project directory to www-data
RUN chown -R www-data:www-data /var/www/html/

# Change current user to www-data
USER www-data

# Define environment variable for Symfony server
ENV SYMFONY_SERVER_PORT=0.0.0.0:8000

# Start the server
CMD ["symfony", "serve", "--no-tls"]

# Expose port 8000 for Symfony
EXPOSE 8000
