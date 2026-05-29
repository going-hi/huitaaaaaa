#!/bin/bash
set -e

STORAGE="/var/www/html/storage"
DOCS="${STORAGE}/documents"

mkdir -p "${DOCS}"
chown -R www-data:www-data "${STORAGE}" 2>/dev/null || true
chmod -R 775 "${STORAGE}" 2>/dev/null || true

exec apache2-foreground
