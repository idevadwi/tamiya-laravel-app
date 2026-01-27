# Use pre-built base image with PHP extensions already compiled
# To build the base image (one-time, takes ~18 min):
#   docker build -f Dockerfile.base -t yourusername/php-laravel-base:8.3 .
#   docker push yourusername/php-laravel-base:8.3
# Then replace 'yourusername' below with your Docker Hub username
FROM deva1212/php-laravel-base:8.3

# Set working directory
WORKDIR /var/www/html

# Copy Nginx configuration
COPY nginx-app.conf /etc/nginx/http.d/default.conf

# Copy Supervisor configuration
COPY supervisord.conf /etc/supervisord.conf

# Copy application files
COPY --chown=laravel:laravel . /var/www/html

# Set permissions for Laravel directories
RUN chown -R laravel:laravel /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create nginx directories and set permissions
RUN mkdir -p /var/lib/nginx/tmp /var/log/nginx \
    && chown -R laravel:laravel /var/lib/nginx /var/log/nginx \
    && chmod -R 755 /var/lib/nginx

# Expose port 80 (Nginx)
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Start Supervisor (manages PHP-FPM + Nginx)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
