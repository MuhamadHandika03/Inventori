#!/bin/bash
set -e

# Wait for MySQL
echo "⏳ Waiting for MySQL..."
until mysqladmin ping -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" --silent 2>/dev/null; do
    sleep 2
done
echo "✅ MySQL ready"

# Seed users if empty
USER_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM users" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 Seeding users..."
    cd /var/www/html && php setup.php 2>/dev/null || echo "⚠ setup.php not found, continuing"
fi

# Start Apache
exec docker-php-entrypoint apache2-foreground
