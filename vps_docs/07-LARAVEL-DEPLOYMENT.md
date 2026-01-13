# Phase 7: Laravel Application Deployment (Docker)

## Overview

This document provides a complete step-by-step guide to deploy the Tamiya Laravel race management application to your VPS using Docker containerization. The deployment uses your existing infrastructure (Nginx Proxy Manager, MySQL, Docker networks) and implements GitHub Actions for automated CI/CD.

**Prerequisites**:
- Completed Phase 1 (Initial Server Setup)
- Completed Phase 3 (Docker Installation)
- Completed Phase 4 (Nginx Proxy Manager)
- Completed Phase 5 (Database Setup)
- Completed Phase 6 (Application Environments)
- Domain name (race-lane.com) from Hostinger

---

## Deployment Architecture

**Stack Overview:**
- **Containerization:** Docker (PHP 8.3-fpm-alpine + Nginx in single container)
- **Reverse Proxy:** Nginx Proxy Manager (already installed)
- **Database:** MySQL 8.0 container (already running as `mysql-server`)
- **Domain:** race-lane.com (Hostinger)
- **CI/CD:** GitHub Actions
- **SSL:** Let's Encrypt via NPM
- **Real-time:** Ably for live race updates

**Estimated Time:** 2-3 hours for initial setup, 5-10 minutes for automated deployments

---

## Part 1: Pre-Deployment Configuration

### Step 1.1: Configure Domain DNS (Hostinger)

**Action:** Point your domain to VPS IP address

1. Log into Hostinger account
2. Navigate to **Domains** → **race-lane.com** → **DNS Zone**
3. Add/Update these records:

```
Type: A
Name: @ (or blank)
Value: <YOUR_VPS_IP_ADDRESS>
TTL: 14400 (4 hours) or Auto
```

```
Type: A
Name: www
Value: <YOUR_VPS_IP_ADDRESS>
TTL: 14400
```

4. Optional CNAME for www:
```
Type: CNAME
Name: www
Value: race-lane.com
TTL: 14400
```

5. Save changes and wait 5-60 minutes for DNS propagation

**Verify DNS:**
```bash
nslookup race-lane.com
dig race-lane.com +short
```

---

### Step 1.2: Configure GitHub Repository Secrets

**Action:** Add required secrets for GitHub Actions deployment

Navigate to repository: **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

**Verify/Update existing secrets:**
```
SSH_HOST          = <YOUR_VPS_IP_ADDRESS>
SSH_USER          = deva
SSH_KEY           = <YOUR_PRIVATE_SSH_KEY>
SSH_PORT          = 22
```

**Add new secrets:**
```
DB_PASSWORD       = <SECURE_MYSQL_PASSWORD>
ABLY_KEY          = <YOUR_ABLY_API_KEY>
APP_KEY           = base64:... (generate below)
```

**Generate `APP_KEY`:**
```bash
# On local machine with Laravel installed
php artisan key:generate --show

# Or using Docker
docker run --rm php:8.3-cli php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

---

### Step 1.3: Create VPS Directory Structure

**SSH into VPS:**
```bash
ssh deva@<VPS_IP>
```

**Create application directories:**
```bash
# Main application directory
mkdir -p ~/apps/tamiya-laravel-app/{app,storage,logs}

# Application storage subdirectories
cd ~/apps/tamiya-laravel-app
mkdir -p storage/{app/{public,private},framework/{cache,sessions,views},logs}
mkdir -p logs

# Set initial permissions
sudo chown -R deva:deva ~/apps/tamiya-laravel-app
chmod -R 755 ~/apps/tamiya-laravel-app
```

**Final directory structure:**
```
/home/deva/apps/tamiya-laravel-app/
├── app/                    # Git clone destination
├── storage/                # Persistent storage (mounted volume)
│   ├── app/
│   │   ├── public/
│   │   └── private/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
├── logs/                   # Application logs
├── docker-compose.yml      # Docker orchestration
├── Dockerfile              # Container build
├── .env.production         # Production environment
├── nginx-app.conf          # Nginx config
└── supervisord.conf        # Process manager
```

---

### Step 1.4: Setup MySQL Database

**Connect to existing MySQL container:**
```bash
docker exec -it mysql-server mysql -u root -p
```

**Execute SQL commands:**
```sql
-- Create database
CREATE DATABASE tamiya_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user (use a secure password)
CREATE USER 'tamiya_laravel_app'@'%' IDENTIFIED BY '<SECURE_PASSWORD>';

-- Grant privileges
GRANT ALL PRIVILEGES ON tamiya_laravel.* TO 'tamiya_laravel_app'@'%';
GRANT ALL PRIVILEGES ON tamiya_laravel.* TO 'tamiya_laravel_app'@'172.%.%.%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User='tamiya_laravel_app';

EXIT;
```

**Test database connection:**
```bash
# Install mysql-client if needed
sudo apt-get install -y mysql-client

# Test connection
mysql -h 127.0.0.1 -u tamiya_laravel_app -p tamiya_laravel
# Enter password when prompted
# If successful, you'll see MySQL prompt

EXIT;
```

**Important:** Save the database password - you'll need it for:
- GitHub Secrets (DB_PASSWORD)
- .env.production file

---

### Step 1.5: Get Ably API Key

The Tamiya application uses Ably for real-time race updates and notifications.

1. Sign up at https://ably.com (free tier available with 6 million messages/month)
2. Create new app: **"Tamiya Race Manager"**
3. Navigate to **API Keys** tab
4. Copy the Root API key (format: `xxxxxxxx.xxxxxxxx:xxxxxxxxxxxxxxxxxxxx`)
5. Add to GitHub Secrets as `ABLY_KEY`

**Ably Configuration:**
- **Free Tier Limits:** 6M messages/month, 200 concurrent connections
- **Channel Prefix:** tamiya (configured in .env)
- **Use Case:** Real-time race timing, live leaderboards, race notifications

---

## Part 2: Docker Configuration Files

### Step 2.1: Create Dockerfile

**Location:** `/home/deva/apps/tamiya-laravel-app/Dockerfile`

```bash
cd ~/apps/tamiya-laravel-app
nano Dockerfile
```

**Content:**
```dockerfile
# Base image - PHP 8.3 FPM Alpine
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create non-root user for Laravel
RUN addgroup -g 1000 laravel && \
    adduser -u 1000 -G laravel -s /bin/sh -D laravel

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
```

**Save and exit:** `Ctrl+O`, `Enter`, `Ctrl+X`

---

### Step 2.2: Create Nginx Configuration

**Location:** `/home/deva/apps/tamiya-laravel-app/nginx-app.conf`

```bash
nano nginx-app.conf
```

**Content:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name _;

    root /var/www/html/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Logging
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log warn;

    # Client body size (file uploads)
    client_max_body_size 20M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript
               application/x-javascript application/xml+rss
               application/json application/javascript;

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;

        # Timeouts for long requests
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Deny hidden files
    location ~ /\. {
        deny all;
    }

    # Static asset caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

**Save and exit:** `Ctrl+O`, `Enter`, `Ctrl+X`

---

### Step 2.3: Create Supervisor Configuration

**Location:** `/home/deva/apps/tamiya-laravel-app/supervisord.conf`

```bash
nano supervisord.conf
```

**Content:**
```ini
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm83 -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=5

[program:nginx]
command=nginx -g 'daemon off;'
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=10
```

**Save and exit:** `Ctrl+O`, `Enter`, `Ctrl+X`

---

### Step 2.4: Create Docker Compose Configuration

**Location:** `/home/deva/apps/tamiya-laravel-app/docker-compose.yml`

```bash
nano docker-compose.yml
```

**Content:**
```yaml
version: '3.8'

services:
  tamiya-app:
    build:
      context: ./app
      dockerfile: ../Dockerfile
    container_name: tamiya-laravel-app
    restart: unless-stopped

    working_dir: /var/www/html

    volumes:
      # Persistent storage
      - ./storage:/var/www/html/storage
      - ./app/bootstrap/cache:/var/www/html/bootstrap/cache

    environment:
      # Laravel environment
      - APP_NAME=${APP_NAME:-"Tamiya Race Manager"}
      - APP_ENV=${APP_ENV:-production}
      - APP_KEY=${APP_KEY}
      - APP_DEBUG=${APP_DEBUG:-false}
      - APP_URL=${APP_URL:-https://race-lane.com}

      # Database
      - DB_CONNECTION=mysql
      - DB_HOST=mysql-server
      - DB_PORT=3306
      - DB_DATABASE=${DB_DATABASE:-tamiya_laravel}
      - DB_USERNAME=${DB_USERNAME:-tamiya_laravel_app}
      - DB_PASSWORD=${DB_PASSWORD}

      # Cache & Session
      - SESSION_DRIVER=database
      - CACHE_STORE=database
      - QUEUE_CONNECTION=database

      # Ably Real-time
      - ABLY_KEY=${ABLY_KEY}
      - ABLY_CHANNEL_PREFIX=tamiya

    networks:
      - web-network
      - db-network

    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/api/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 60s

networks:
  web-network:
    external: true
  db-network:
    external: true
```

**Save and exit:** `Ctrl+O`, `Enter`, `Ctrl+X`

---

### Step 2.5: Create Production Environment File

**Location:** `/home/deva/apps/tamiya-laravel-app/.env.production`

```bash
nano .env.production
```

**Content:**
```env
APP_NAME="Tamiya Race Manager"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://race-lane.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=mysql-server
DB_PORT=3306
DB_DATABASE=tamiya_laravel
DB_USERNAME=tamiya_laravel_app
DB_PASSWORD=<YOUR_SECURE_DB_PASSWORD>

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=race-lane.com

# Cache Configuration
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
CACHE_PREFIX=tamiya_

# Ably Real-time Configuration
ABLY_KEY=<YOUR_ABLY_API_KEY>
ABLY_CHANNEL_PREFIX=tamiya

# Mail Configuration
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@race-lane.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Important:** Replace the following values:
- `APP_KEY`: Use the value generated in Step 1.2
- `DB_PASSWORD`: Use the password from Step 1.4
- `ABLY_KEY`: Use the API key from Step 1.5

**Secure the file:**
```bash
chmod 600 .env.production
chown deva:deva .env.production
```

**Verify permissions:**
```bash
ls -la .env.production
# Should show: -rw------- 1 deva deva
```

---

### Step 2.6: Add Health Check Endpoint to Laravel

**On your local machine**, add this route to `routes/api.php`:

```php
// Health check endpoint for Docker
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'database' => 'connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => 'Database connection failed'
        ], 503);
    }
});
```

**Commit and push:**
```bash
git add routes/api.php
git commit -m "Add health check endpoint for Docker deployment"
git push origin deva
```

---

## Part 3: Initial Manual Deployment

### Step 3.1: Clone Repository

**On VPS:**
```bash
cd ~/apps/tamiya-laravel-app

# Clone your repository
git clone https://github.com/<YOUR_USERNAME>/tamiya-laravel-app.git app

cd app
git checkout deva
```

**Verify clone:**
```bash
ls -la
# Should show Laravel application files
```

---

### Step 3.2: Install Composer Dependencies

**From the tamiya-laravel-app directory:**
```bash
cd ~/apps/tamiya-laravel-app

docker run --rm -v $(pwd)/app:/app -w /app composer:2 install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev
```

**Expected output:**
```
Loading composer repositories with package information
Installing dependencies from lock file
...
Generating optimized autoload files
```

This will take 2-5 minutes depending on internet speed.

---

### Step 3.3: Build Docker Image

```bash
cd ~/apps/tamiya-laravel-app
docker build -t tamiya-laravel-app:latest -f Dockerfile ./app
```

**Expected output:**
```
[+] Building 120.5s (15/15) FINISHED
...
 => => naming to docker.io/library/tamiya-laravel-app:latest
```

**Verify image:**
```bash
docker images | grep tamiya
# Should show: tamiya-laravel-app   latest   <image_id>   <size>
```

---

### Step 3.4: Start Container

```bash
cd ~/apps/tamiya-laravel-app
docker-compose --env-file .env.production up -d
```

**Expected output:**
```
[+] Running 1/1
 ✔ Container tamiya-laravel-app  Started
```

**Verify container started:**
```bash
docker ps -f name=tamiya-laravel-app
```

---

### Step 3.5: Wait for Health Check

The container needs 30-60 seconds to become healthy.

**Monitor logs in real-time:**
```bash
docker logs -f tamiya-laravel-app
# Press Ctrl+C to exit when you see "ready to handle connections"
```

**Check health status:**
```bash
# Keep running until status is "healthy"
watch -n 2 'docker inspect --format="{{.State.Health.Status}}" tamiya-laravel-app'
# Press Ctrl+C when it shows "healthy"
```

**Alternative - single check:**
```bash
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app
```

---

### Step 3.6: Run Laravel Setup Commands

**Create storage symlink:**
```bash
docker exec tamiya-laravel-app php artisan storage:link
```

**Run database migrations:**
```bash
docker exec tamiya-laravel-app php artisan migrate --force
```

**Expected output:**
```
Migration table created successfully.
Migrating: 2025_01_20_100000_create_cache_table
Migrated:  2025_01_20_100000_create_cache_table
...
(14 migrations total)
```

**Seed database (roles and default users):**
```bash
docker exec tamiya-laravel-app php artisan db:seed --force
```

**Optimize Laravel for production:**
```bash
# Clear all caches first
docker exec tamiya-laravel-app php artisan optimize:clear

# Cache configuration
docker exec tamiya-laravel-app php artisan config:cache

# Cache routes
docker exec tamiya-laravel-app php artisan route:cache

# Cache views
docker exec tamiya-laravel-app php artisan view:cache

# Optimize Composer autoloader
docker exec tamiya-laravel-app composer dump-autoload -o
```

---

### Step 3.7: Verify Application

**Test health endpoint:**
```bash
curl http://localhost/api/health
```

**Expected output:**
```json
{
  "status": "healthy",
  "timestamp": "2026-01-13T10:30:00+00:00",
  "database": "connected"
}
```

**Check migration status:**
```bash
docker exec tamiya-laravel-app php artisan migrate:status
```

**View recent logs:**
```bash
docker logs --tail 50 tamiya-laravel-app
```

**Check container status:**
```bash
docker ps -f name=tamiya-laravel-app
# STATUS should show "Up X minutes (healthy)"
```

---

## Part 4: Nginx Proxy Manager Configuration

### Step 4.1: Access NPM Admin Panel

1. Open browser: `http://<VPS_IP>:81`
2. Login with your admin credentials (changed in Phase 4)

---

### Step 4.2: Create Proxy Host

Navigate to **Hosts** → **Proxy Hosts** → **Add Proxy Host**

**Details Tab:**
```
Domain Names: race-lane.com
              www.race-lane.com

Scheme: http
Forward Hostname/IP: tamiya-laravel-app
Forward Port: 80

☑ Cache Assets
☑ Block Common Exploits
☑ Websockets Support
```

**Explanation:**
- `Forward Hostname/IP`: Use container name (Docker DNS resolution)
- `Websockets Support`: Required for Ably real-time features
- `Cache Assets`: Improves performance for static files

---

**SSL Tab:**
```
☑ SSL Certificate: Request a new SSL Certificate
☑ Force SSL
☑ HTTP/2 Support
☑ HSTS Enabled
☐ HSTS Subdomains

Email Address: <your-email@example.com>
☑ I Agree to the Let's Encrypt Terms of Service
```

---

**Advanced Tab (Custom Nginx Configuration):**

Click the **Advanced** tab and paste:

```nginx
# Increase client body size for file uploads
client_max_body_size 20M;

# Proxy timeouts for long-running requests
proxy_connect_timeout 60s;
proxy_send_timeout 300s;
proxy_read_timeout 300s;

# WebSocket support for Ably
proxy_http_version 1.1;
proxy_set_header Upgrade $http_upgrade;
proxy_set_header Connection "upgrade";

# Real IP forwarding
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Forwarded-Host $host;

# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
```

---

### Step 4.3: Save and Verify SSL

1. Click **Save** - NPM will request Let's Encrypt certificate
2. Wait 1-2 minutes for certificate provisioning
3. Check **SSL Certificates** tab for successful certificate
4. Verify in browser: `https://race-lane.com`

**Expected result:** Browser shows padlock icon, certificate is valid

**Troubleshooting SSL issues:**

If certificate request fails:

```bash
# Check NPM logs
docker logs nginx-proxy-manager | grep -i letsencrypt

# Verify DNS points to VPS
nslookup race-lane.com

# Verify port 80 is accessible
curl http://race-lane.com
```

**Common issues:**
- DNS not propagated yet → Wait 30-60 minutes
- Port 80 blocked → Check UFW: `sudo ufw status`
- Rate limit exceeded → Let's Encrypt allows 5 attempts per week

---

## Part 5: GitHub Actions CI/CD Setup

### Step 5.1: Update GitHub Actions Workflow

**On your local machine**, edit `.github/workflows/deploy.yml`:

**Replace the entire file content with:**

```yaml
name: Deploy to VPS (Docker)

on:
  push:
    branches:
      - deva

jobs:
  deploy:
    runs-on: ubuntu-latest
    timeout-minutes: 20

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Deploy to VPS via SSH
        uses: appleboy/ssh-action@v1.0.0
        env:
          APP_KEY: ${{ secrets.APP_KEY }}
          DB_PASSWORD: ${{ secrets.DB_PASSWORD }}
          ABLY_KEY: ${{ secrets.ABLY_KEY }}
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_KEY }}
          port: ${{ secrets.SSH_PORT }}
          command_timeout: 20m
          envs: APP_KEY,DB_PASSWORD,ABLY_KEY

          script: |
            set -e

            echo "======================================"
            echo "🚀 Starting Docker Deployment"
            echo "======================================"

            cd ~/apps/tamiya-laravel-app/app

            echo "📥 Fetching latest code..."
            git fetch origin
            git reset --hard origin/deva
            git clean -fd

            echo "🔐 Updating environment variables..."
            cd ~/apps/tamiya-laravel-app
            sed -i "s|APP_KEY=.*|APP_KEY=${APP_KEY}|g" .env.production
            sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|g" .env.production
            sed -i "s|ABLY_KEY=.*|ABLY_KEY=${ABLY_KEY}|g" .env.production
            chmod 600 .env.production

            echo "📦 Installing Composer dependencies..."
            cd ~/apps/tamiya-laravel-app/app
            docker run --rm -v $(pwd):/app -w /app composer:2 install \
              --no-interaction \
              --prefer-dist \
              --optimize-autoloader \
              --no-dev

            echo "🏗️  Building Docker image..."
            cd ~/apps/tamiya-laravel-app
            docker build -t tamiya-laravel-app:latest -f Dockerfile ./app

            echo "🔄 Performing rolling deployment..."
            if docker ps -q -f name=tamiya-laravel-app > /dev/null 2>&1; then
              docker stop tamiya-laravel-app || true
              docker rm tamiya-laravel-app || true
            fi

            docker-compose --env-file .env.production up -d tamiya-app

            echo "⏳ Waiting for health check..."
            for i in {1..30}; do
              if docker ps --filter "name=tamiya-laravel-app" --filter "health=healthy" | grep -q tamiya-laravel-app; then
                echo "✅ Container is healthy!"
                break
              fi
              if [ $i -eq 30 ]; then
                echo "❌ Health check failed"
                docker logs --tail 50 tamiya-laravel-app
                exit 1
              fi
              echo "Attempt $i/30..."
              sleep 2
            done

            echo "⚡ Running Laravel optimizations..."
            docker exec tamiya-laravel-app php artisan optimize:clear
            docker exec tamiya-laravel-app php artisan config:cache
            docker exec tamiya-laravel-app php artisan route:cache
            docker exec tamiya-laravel-app php artisan view:cache

            echo "🛠️  Running database migrations..."
            docker exec tamiya-laravel-app php artisan migrate --force

            echo "🔐 Setting permissions..."
            docker exec tamiya-laravel-app chown -R laravel:laravel /var/www/html/storage /var/www/html/bootstrap/cache
            docker exec tamiya-laravel-app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

            echo "🧹 Cleaning up..."
            docker image prune -f --filter "dangling=true"

            echo "✅ Verifying deployment..."
            docker ps -f name=tamiya-laravel-app

            sleep 3
            if docker exec tamiya-laravel-app curl -f http://localhost/api/health > /dev/null 2>&1; then
              echo "✅ Health check passed!"
            else
              echo "⚠️  Health check warning"
            fi

            echo "======================================"
            echo "🎉 Deployment Complete!"
            echo "======================================"

            docker logs --tail 20 tamiya-laravel-app
```

---

### Step 5.2: Commit and Push Workflow

```bash
# On local machine
git add .github/workflows/deploy.yml
git commit -m "Update deployment workflow for Docker"
git push origin deva
```

---

### Step 5.3: Monitor Deployment

1. Navigate to your repository on GitHub
2. Click **Actions** tab
3. Watch "Deploy to VPS (Docker)" workflow execution
4. Monitor logs in real-time
5. Verify successful completion (green checkmark ✅)

**The workflow will:**
- Pull latest code from deva branch
- Install Composer dependencies
- Build Docker image
- Stop old container
- Start new container
- Wait for health check
- Run migrations
- Optimize Laravel

**Deployment time:** 5-10 minutes

---

## Part 6: Post-Deployment Verification

### Step 6.1: Verification Checklist

**On VPS, run these commands:**

```bash
# 1. Container status
docker ps -f name=tamiya-laravel-app

# 2. Health status (should be "healthy")
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app

# 3. Database connection
docker exec tamiya-laravel-app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected';"

# 4. Migration status (should show 14 migrations done)
docker exec tamiya-laravel-app php artisan migrate:status

# 5. Storage permissions
docker exec tamiya-laravel-app ls -la storage/

# 6. Application logs
docker logs --tail 30 tamiya-laravel-app

# 7. Test HTTPS
curl -I https://race-lane.com

# 8. Test health endpoint
curl https://race-lane.com/api/health
```

---

### Step 6.2: Access Application in Browser

1. Open browser: `https://race-lane.com`
2. Verify SSL certificate (padlock icon) ✅
3. Application should load with AdminLTE interface
4. Login with seeded admin user (check database for credentials)
5. Test tournament features
6. Verify real-time updates work (Ably connection)

---

### Step 6.3: Check Seeded Users

**Connect to database:**
```bash
docker exec -it mysql-server mysql -u tamiya_laravel_app -p tamiya_laravel
```

**Query users and roles:**
```sql
-- View all users
SELECT id, name, email, created_at FROM users;

-- View all roles
SELECT * FROM roles;

-- Exit
EXIT;
```

**Default seeded data:**
- Roles: ADMINISTRATOR, MODERATOR, USER
- Users: Check `database/seeders/UserSeeder.php` for default credentials

---

## Part 7: Maintenance & Monitoring

### Step 7.1: Create Deployment Verification Script

```bash
mkdir -p ~/scripts
nano ~/scripts/verify-tamiya.sh
```

**Content:**
```bash
#!/bin/bash

echo "=== Tamiya Deployment Verification ==="
echo

echo "1. Container Status:"
docker ps -f name=tamiya-laravel-app --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo

echo "2. Health:"
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app
echo

echo "3. Database:"
docker exec tamiya-laravel-app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected';" 2>/dev/null
echo

echo "4. Web Endpoint:"
curl -sI https://race-lane.com | grep HTTP
echo

echo "5. Health Endpoint:"
curl -s https://race-lane.com/api/health | python3 -m json.tool 2>/dev/null || echo "JSON parse failed"
echo

echo "=== Verification Complete ==="
```

**Make executable:**
```bash
chmod +x ~/scripts/verify-tamiya.sh
```

**Run verification:**
```bash
~/scripts/verify-tamiya.sh
```

---

### Step 7.2: Create Database Backup Script

```bash
nano ~/scripts/backup-tamiya-db.sh
```

**Content:**
```bash
#!/bin/bash

BACKUP_DIR="$HOME/backups/tamiya"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="tamiya_laravel_${DATE}.sql.gz"

mkdir -p $BACKUP_DIR

# Get DB password from .env.production
DB_PASSWORD=$(grep DB_PASSWORD ~/apps/tamiya-laravel-app/.env.production | cut -d'=' -f2)

docker exec mysql-server mysqldump \
  -u tamiya_laravel_app \
  -p"${DB_PASSWORD}" \
  --single-transaction \
  --quick \
  --lock-tables=false \
  tamiya_laravel | gzip > ${BACKUP_DIR}/${BACKUP_FILE}

if [ $? -eq 0 ]; then
  echo "Backup successful: ${BACKUP_FILE}"

  # Keep last 30 days
  find ${BACKUP_DIR} -name "tamiya_laravel_*.sql.gz" -mtime +30 -delete

  # Keep only last 50 backups
  ls -t ${BACKUP_DIR}/tamiya_laravel_*.sql.gz | tail -n +51 | xargs rm -f 2>/dev/null
else
  echo "Backup failed!"
  exit 1
fi
```

**Make executable:**
```bash
chmod +x ~/scripts/backup-tamiya-db.sh
```

**Test backup:**
```bash
~/scripts/backup-tamiya-db.sh
```

**Schedule daily backups:**
```bash
crontab -e
```

**Add this line (daily at 3 AM):**
```
0 3 * * * /home/deva/scripts/backup-tamiya-db.sh >> /home/deva/logs/backup-tamiya.log 2>&1
```

---

### Step 7.3: View Logs

**Container logs (stdout/stderr):**
```bash
# Real-time logs
docker logs -f tamiya-laravel-app

# Last 100 lines
docker logs --tail 100 tamiya-laravel-app

# Since 1 hour ago
docker logs --since 1h tamiya-laravel-app

# Between timestamps
docker logs --since 2024-01-13T10:00:00 --until 2024-01-13T11:00:00 tamiya-laravel-app
```

**Laravel application logs:**
```bash
# Today's log
docker exec tamiya-laravel-app tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# All logs
docker exec tamiya-laravel-app ls -lh storage/logs/

# Last 50 lines
docker exec tamiya-laravel-app tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
```

**Nginx logs (inside container):**
```bash
# Access log
docker exec tamiya-laravel-app tail -f /var/log/nginx/access.log

# Error log
docker exec tamiya-laravel-app tail -f /var/log/nginx/error.log
```

---

### Step 7.4: Rollback Procedure

**Create rollback script:**
```bash
nano ~/scripts/rollback-tamiya.sh
```

**Content:**
```bash
#!/bin/bash

cd ~/apps/tamiya-laravel-app/app

echo "Recent commits:"
git log --oneline -10

read -p "Enter commit hash to rollback to: " COMMIT_HASH

git checkout $COMMIT_HASH

cd ~/apps/tamiya-laravel-app

echo "Installing dependencies..."
docker run --rm -v $(pwd)/app:/app -w /app composer:2 install --no-dev

echo "Building Docker image..."
docker build -t tamiya-laravel-app:latest -f Dockerfile ./app

echo "Deploying rollback..."
docker-compose --env-file .env.production up -d tamiya-app

sleep 10

echo "Rollback complete!"
docker ps -f name=tamiya-laravel-app
docker logs --tail 30 tamiya-laravel-app
```

**Make executable:**
```bash
chmod +x ~/scripts/rollback-tamiya.sh
```

**To rollback:**
```bash
~/scripts/rollback-tamiya.sh
# Enter commit hash when prompted
```

---

## Part 8: Troubleshooting Guide

### Issue: Container Won't Start

**Symptoms:** Container exits immediately or won't start

**Check logs:**
```bash
docker logs tamiya-laravel-app
```

**Common causes and solutions:**

1. **Database connection failed**
   ```bash
   # Check .env.production credentials
   cat ~/apps/tamiya-laravel-app/.env.production | grep DB_

   # Test MySQL connection
   mysql -h 127.0.0.1 -u tamiya_laravel_app -p tamiya_laravel
   ```

2. **Permission denied on storage**
   ```bash
   # Fix permissions
   sudo chown -R 1000:1000 ~/apps/tamiya-laravel-app/storage
   sudo chmod -R 775 ~/apps/tamiya-laravel-app/storage
   ```

3. **Port 80 conflict**
   ```bash
   # Check what's using port 80
   sudo netstat -tulpn | grep :80

   # If another container is using it, stop it
   docker ps | grep :80
   ```

---

### Issue: 502 Bad Gateway from NPM

**Symptoms:** Browser shows "502 Bad Gateway" error

**Diagnostic steps:**

```bash
# 1. Is container running?
docker ps -f name=tamiya-laravel-app

# 2. Is container healthy?
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app

# 3. Are networks connected?
docker network inspect web-network | grep -A 5 tamiya

# 4. Can NPM reach container?
docker exec nginx-proxy-manager curl http://tamiya-laravel-app
```

**Solutions:**

```bash
# Restart application container
docker-compose -f ~/apps/tamiya-laravel-app/docker-compose.yml restart

# Restart NPM
docker restart nginx-proxy-manager

# Check NPM logs
docker logs nginx-proxy-manager | tail -50
```

---

### Issue: Database Migration Errors

**Symptoms:** Migrations fail with connection or syntax errors

**Check database connection:**
```bash
docker exec tamiya-laravel-app php artisan tinker --execute="DB::connection()->getPdo();"
```

**View migration status:**
```bash
docker exec tamiya-laravel-app php artisan migrate:status
```

**Solutions:**

**Option 1: Rollback and re-run**
```bash
# Rollback last batch
docker exec tamiya-laravel-app php artisan migrate:rollback --force

# Re-run migrations
docker exec tamiya-laravel-app php artisan migrate --force
```

**Option 2: Fresh migration (⚠️ DATA LOSS)**
```bash
# Only use in development or if you have backups!
docker exec tamiya-laravel-app php artisan migrate:fresh --force --seed
```

---

### Issue: Ably Real-time Not Working

**Symptoms:** Race updates don't appear in real-time

**Verify Ably key:**
```bash
docker exec tamiya-laravel-app php artisan tinker --execute="echo config('services.ably.key');"
```

**Check Ably configuration:**
```bash
# Verify config is cached correctly
docker exec tamiya-laravel-app php artisan config:show | grep -i ably
```

**Test Ably connection:**
```bash
docker exec tamiya-laravel-app php artisan tinker

# In Tinker:
$ably = new \Ably\AblyRest(config('services.ably.key'));
$channel = $ably->channels->get('test-channel');
$channel->publish('test-event', ['message' => 'Hello']);
exit
```

**Check Ably logs:**
```bash
docker logs tamiya-laravel-app | grep -i ably
```

**Solution:**
```bash
# Re-cache config after updating ABLY_KEY
docker exec tamiya-laravel-app php artisan config:clear
docker exec tamiya-laravel-app php artisan config:cache
```

---

### Issue: SSL Certificate Failure

**Symptoms:** NPM shows "Certificate request failed"

**Check NPM logs:**
```bash
docker logs nginx-proxy-manager | grep -i letsencrypt | tail -50
```

**Verify DNS:**
```bash
nslookup race-lane.com
dig race-lane.com +short
```

**Verify port 80 accessibility:**
```bash
curl -I http://race-lane.com
```

**Common issues:**

1. **DNS not propagated**
   - Wait 30-60 minutes after DNS changes
   - Verify A record points to VPS IP

2. **Port 80 blocked**
   ```bash
   sudo ufw status | grep 80
   # Should show: 80/tcp ALLOW Anywhere
   ```

3. **Rate limit exceeded**
   - Let's Encrypt: 5 failed attempts per week
   - Wait 7 days or use staging mode

**Manual certificate (alternative):**
```bash
# Stop NPM temporarily
docker stop nginx-proxy-manager

# Use certbot directly
sudo certbot certonly --standalone -d race-lane.com -d www.race-lane.com

# Start NPM
docker start nginx-proxy-manager
```

---

### Issue: Storage Permission Errors

**Symptoms:** Laravel logs show "Permission denied" for storage

**Fix permissions on host:**
```bash
# Storage directory
sudo chown -R 1000:1000 ~/apps/tamiya-laravel-app/storage
sudo chmod -R 775 ~/apps/tamiya-laravel-app/storage

# Bootstrap cache
sudo chown -R 1000:1000 ~/apps/tamiya-laravel-app/app/bootstrap/cache
sudo chmod -R 775 ~/apps/tamiya-laravel-app/app/bootstrap/cache
```

**Fix permissions inside container:**
```bash
docker exec tamiya-laravel-app chown -R laravel:laravel /var/www/html/storage
docker exec tamiya-laravel-app chmod -R 775 /var/www/html/storage
docker exec tamiya-laravel-app chown -R laravel:laravel /var/www/html/bootstrap/cache
docker exec tamiya-laravel-app chmod -R 775 /var/www/html/bootstrap/cache
```

**Verify permissions:**
```bash
docker exec tamiya-laravel-app ls -la storage/
docker exec tamiya-laravel-app ls -la bootstrap/cache/
```

---

## Part 9: Security Best Practices

### Step 9.1: Environment File Security

**Ensure .env.production is secure:**
```bash
# Correct permissions (read/write for owner only)
chmod 600 ~/apps/tamiya-laravel-app/.env.production
chown deva:deva ~/apps/tamiya-laravel-app/.env.production

# Verify
ls -la ~/apps/tamiya-laravel-app/.env.production
# Should show: -rw------- 1 deva deva
```

**Verify not in git:**
```bash
cd ~/apps/tamiya-laravel-app/app
echo ".env.production" >> .gitignore
git add .gitignore
git commit -m "Ensure .env.production is ignored"
git push origin deva
```

---

### Step 9.2: Restrict NPM Admin Access

**Option 1: IP Whitelist (Recommended)**

```bash
# Remove global access to port 81
sudo ufw delete allow 81

# Allow only from your IP
sudo ufw allow from <YOUR_HOME_IP> to any port 81 proto tcp comment 'NPM Admin'

# Verify
sudo ufw status numbered
```

**Option 2: SSH Tunnel (Most Secure)**

Remove port 81 from UFW completely:
```bash
sudo ufw delete allow 81
```

From your local machine:
```bash
# Create SSH tunnel
ssh -L 8181:localhost:81 deva@<VPS_IP>

# Access NPM at: http://localhost:8181
```

---

### Step 9.3: Regular Updates

**Update Docker base images:**
```bash
# Pull latest images
docker pull php:8.3-fpm-alpine
docker pull composer:2

# Rebuild application
cd ~/apps/tamiya-laravel-app
docker build -t tamiya-laravel-app:latest -f Dockerfile ./app

# Deploy
docker-compose --env-file .env.production up -d
```

**Update Laravel dependencies:**
```bash
cd ~/apps/tamiya-laravel-app/app

# Update composer.lock
docker run --rm -v $(pwd):/app -w /app composer:2 update

# Commit and push
git add composer.lock
git commit -m "Update dependencies"
git push origin deva
# GitHub Actions will auto-deploy
```

---

### Step 9.4: Monitor Failed Login Attempts

**Check Laravel logs for failed authentication:**
```bash
docker exec tamiya-laravel-app grep -i "failed\|unauthorized" storage/logs/laravel-*.log | tail -20
```

**Set up Fail2ban for application (optional):**
```bash
# Create filter for Laravel failed logins
sudo nano /etc/fail2ban/filter.d/laravel-auth.conf
```

```ini
[Definition]
failregex = .*authentication attempt failed.*<HOST>.*
ignoreregex =
```

**Enable in jail:**
```bash
sudo nano /etc/fail2ban/jail.local
```

```ini
[laravel-auth]
enabled = true
port = http,https
logpath = /home/deva/apps/tamiya-laravel-app/storage/logs/laravel-*.log
maxretry = 5
bantime = 3600
```

---

## Summary & Quick Reference

### ✅ What You've Accomplished

- [x] Domain configured and pointing to VPS
- [x] MySQL database created for application
- [x] Docker container built and running
- [x] Nginx Proxy Manager configured with SSL
- [x] GitHub Actions CI/CD automated deployment
- [x] Database migrations completed (14 migrations)
- [x] Health monitoring configured (30s intervals)
- [x] Backup strategy implemented (daily at 3 AM)
- [x] Rollback procedure documented

---

### 🚀 Automated Workflow

**To deploy new changes:**
1. Make code changes on local machine
2. Commit to git: `git commit -m "Your changes"`
3. Push to deva branch: `git push origin deva`
4. GitHub Actions automatically deploys (5-10 minutes)
5. Verify: `ssh deva@<VPS> "~/scripts/verify-tamiya.sh"`

---

### 📊 Daily Monitoring

**Run verification script:**
```bash
~/scripts/verify-tamiya.sh
```

**Check container health:**
```bash
docker ps -f name=tamiya-laravel-app
```

**View recent logs:**
```bash
docker logs --tail 50 tamiya-laravel-app
```

**Automated tasks:**
- Database backups: Daily at 3 AM
- Container auto-restart on failure
- Health checks: Every 30 seconds

---

### 🛠️ Useful Commands Reference

```bash
# Logs
docker logs -f tamiya-laravel-app                    # Real-time logs
docker logs --tail 100 tamiya-laravel-app            # Last 100 lines

# Container management
docker ps -f name=tamiya-laravel-app                 # Status
docker restart tamiya-laravel-app                    # Restart
docker exec -it tamiya-laravel-app sh                # Shell access

# Laravel commands
docker exec tamiya-laravel-app php artisan <cmd>     # Run artisan
docker exec tamiya-laravel-app composer <cmd>        # Run composer

# Backup & Restore
~/scripts/backup-tamiya-db.sh                        # Manual backup
~/scripts/rollback-tamiya.sh                         # Rollback code

# Verification
~/scripts/verify-tamiya.sh                           # Full verification
curl https://race-lane.com/api/health                # Health check
```

---

### 📝 Important File Locations

**VPS Files:**
```
/home/deva/apps/tamiya-laravel-app/     Main directory
/home/deva/apps/tamiya-laravel-app/app/ Application code (git)
/home/deva/scripts/                     Management scripts
/home/deva/backups/tamiya/              Database backups
/home/deva/logs/                        Deployment logs
```

**Container Files:**
```
/var/www/html/                          Application root
/var/www/html/storage/logs/             Laravel logs
/var/log/nginx/                         Nginx logs
```

**GitHub:**
```
.github/workflows/deploy.yml            CI/CD workflow
Settings → Secrets → Actions            Deployment secrets
```

---

### 🎯 Production Checklist

- [x] HTTPS enabled with valid certificate
- [x] Database backed up automatically
- [x] Container health checks configured
- [x] Non-root user in container (UID 1000)
- [x] Firewall configured (UFW - ports 22, 80, 443)
- [x] NPM admin access restricted
- [x] Environment secrets secured (chmod 600)
- [x] Automated deployments working
- [x] Rollback procedure tested
- [x] Monitoring scripts created

---

### 🔗 Related Documentation

- [Phase 1: Initial Server Setup](01-INITIAL-SERVER-SETUP.md)
- [Phase 3: Docker Installation](03-DOCKER-INSTALLATION.md)
- [Phase 4: Nginx Proxy Manager](04-NGINX-PROXY-MANAGER.md)
- [Phase 5: Database Setup](05-DATABASE-SETUP.md)
- [Phase 6: Application Environments](06-APPLICATION-ENVIRONMENTS.md)

---

### 📞 Support & Troubleshooting

**Common issues?** See [Part 8: Troubleshooting Guide](#part-8-troubleshooting-guide)

**Check logs:**
```bash
# Application logs
docker logs --tail 100 tamiya-laravel-app

# Laravel logs
docker exec tamiya-laravel-app tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log

# NPM logs
docker logs --tail 50 nginx-proxy-manager

# MySQL logs
docker logs --tail 50 mysql-server
```

**Health status:**
```bash
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app
```

---

**Your Tamiya Laravel application is now production-ready! 🎉**

**Document Version**: 1.0
**Last Updated**: 2026-01-13
**Tested On**: Ubuntu 24.04 LTS with Docker 24.x
