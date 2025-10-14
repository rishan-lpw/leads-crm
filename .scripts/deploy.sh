export TERM=xterm
set -e

echo "Starting deployment..."

# Take the application down (ignore if already down)
(php artisan down) || true

# Completely discard local changes and reset to remote state
git fetch origin
git reset --hard origin/upgrade_filament4.0
git clean -fd

# Install/update dependencies
composer install --optimize-autoloader --no-dev --no-interaction

# Clear caches
php artisan cache:clear
php artisan config:clear

# Optimize application
php artisan optimize

# Run migrations
php artisan migrate --force

# Bring application back up
php artisan up

echo "Deployment completed successfully."