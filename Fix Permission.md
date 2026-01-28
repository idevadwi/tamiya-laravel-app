# Change storage ownership to www-data
docker exec -u root tamiya-laravel-app chown -R www-data:www-data /var/www/html/storage
docker exec -u root tamiya-laravel-app chown -R www-data:www-data /var/www/html/bootstrap/cache

# Clear cached views
docker exec tamiya-laravel-app php artisan view:clear
docker exec tamiya-laravel-app php artisan cache:clear
