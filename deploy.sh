
# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force
git pull origin main

# Clear and cache config
print_status "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

  
