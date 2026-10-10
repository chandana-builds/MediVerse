#!/bin/sh
set -e

# Render, Railway, Fly.io dynamically assign the $PORT environment variable
TARGET_PORT="${PORT:-8080}"

echo "Starting MediVerse on port ${TARGET_PORT}..."

# Update Apache listening port to match dynamic container port
sed -i "s/Listen 80/Listen ${TARGET_PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/Listen [0-9]*/Listen ${TARGET_PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${TARGET_PORT}>/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Ensure sqlite database file & uploads directory exist and have write permissions
touch /var/www/html/htdocs/mediverse.sqlite 2>/dev/null || true
chmod 666 /var/www/html/htdocs/mediverse.sqlite 2>/dev/null || true
chmod 777 /var/www/html/htdocs/uploads 2>/dev/null || true
chmod 777 /var/www/html/htdocs 2>/dev/null || true

exec apache2-foreground
