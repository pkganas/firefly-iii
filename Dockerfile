# Use the official Firefly III Docker image as base
FROM fireflyiii/core:latest

# Copy our modified Firefly III code over the base image
COPY --chown=www-data:www-data . /var/www/html/

# Explicitly copy the routes directory to ensure it's updated
COPY --chown=www-data:www-data routes/ /var/www/html/routes/

# Install PHP dependencies (in case we have new ones)
# RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions for storage and cache directories
RUN chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# The base image already has the correct Apache configuration and exposes port 80
