# MediVerse Production Container
# Compatible with Render, Railway, Fly.io, and Docker environments
FROM php:8.3-apache

# Install required PHP extensions for MySQL and SQLite
RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache DocumentRoot to point to htdocs
ENV APACHE_DOCUMENT_ROOT /var/www/html/htdocs
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files
WORKDIR /var/www/html
COPY . /var/www/html/

# Copy custom php.ini if needed
RUN cp php.ini /usr/local/etc/php/conf.d/mediverse.ini 2>/dev/null || true

# Prepare directories and permissions for SQLite and file uploads
RUN mkdir -p /var/www/html/htdocs/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/htdocs/uploads \
    && chmod -R 775 /var/www/html/htdocs

# Configure dynamic entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Render dynamically passes $PORT (default fallback 8080 or 10000)
EXPOSE 8080 10000

ENTRYPOINT ["docker-entrypoint.sh"]
