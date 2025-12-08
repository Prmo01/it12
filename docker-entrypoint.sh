#!/bin/sh
set -e

# Create necessary directories
mkdir -p /etc/nginx/conf.d
mkdir -p /var/run

# Set default PORT if not set (Render sets this automatically)
export PORT=${PORT:-10000}

echo "Starting with PORT=$PORT"

# Substitute PORT in nginx config (sed handles ${PORT:-10000} syntax)
sed "s/\${PORT:-10000}/$PORT/g" /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Show the generated config for debugging
echo "Generated nginx config:"
cat /etc/nginx/conf.d/default.conf

# Remove default nginx config if it exists
rm -f /etc/nginx/sites-enabled/default

# Test nginx configuration
echo "Testing nginx configuration..."
nginx -t || {
    echo "ERROR: Nginx configuration test failed!"
    echo "Generated config:"
    cat /etc/nginx/conf.d/default.conf
    exit 1
}

echo "Nginx configuration is valid. Starting services..."

# Start supervisor (which will start both nginx and php-fpm)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
