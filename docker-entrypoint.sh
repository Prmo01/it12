#!/bin/sh
set -e

# Create nginx conf.d directory if it doesn't exist
mkdir -p /etc/nginx/conf.d

# Substitute PORT in nginx config
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
