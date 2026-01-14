# Deployment Structure Documentation

## Overview

This document explains the deployment structure for the Tamiya Laravel Race Management application using Docker.

---

## Project Structure

```
tamiya-laravel-app/              ← Git repository root
├── app/                         ← Laravel application directory
├── bootstrap/
├── config/
├── database/
├── lang/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── vps_docs/                    ← VPS deployment documentation
│   ├── 01-INITIAL-SERVER-SETUP.md
│   ├── 03-DOCKER-INSTALLATION.md
│   ├── 04-NGINX-PROXY-MANAGER.md
│   ├── 05-DATABASE-SETUP.md
│   ├── 06-APPLICATION-ENVIRONMENTS.md
│   ├── 07-LARAVEL-DEPLOYMENT.md
│   └── MIGRATION-GUIDE.md       ← Guide to restructure existing deployments
├── .github/
│   └── workflows/
│       └── deploy.yml           ← GitHub Actions CI/CD
├── Dockerfile                   ← Docker image definition
├── docker-compose.yml           ← Docker orchestration
├── nginx-app.conf              ← Nginx web server config
├── supervisord.conf            ← Process manager config
├── composer.json
├── artisan
├── .env.example
└── .gitignore
```

---

## VPS Deployment Structure

**Production VPS Directory:**

```
~/apps/tamiya-laravel-app/       ← Git clone directly here
├── app/
├── routes/
├── config/
├── Dockerfile                   ← From Git
├── docker-compose.yml           ← From Git
├── nginx-app.conf              ← From Git
├── supervisord.conf            ← From Git
├── .env.production             ← NOT in Git (secrets)
├── storage/                     ← Persistent data (mounted volume)
└── bootstrap/cache/             ← Persistent cache (mounted volume)
```

---

## Key Configuration Files

### 1. Dockerfile

Defines the Docker image:
- Base: `php:8.3-fpm-alpine`
- Includes: Nginx, Supervisor, PHP extensions
- User: `laravel` (UID 1000)
- Healthcheck: `/api/health` endpoint

### 2. docker-compose.yml

Orchestrates the container:
- Service name: `tamiya-app`
- Container name: `tamiya-laravel-app`
- Networks: `web-network`, `db-network`
- Volumes: `storage/`, `bootstrap/cache/`
- Environment: Loaded from `.env.production`

### 3. nginx-app.conf

Nginx web server configuration:
- Port: 80 (internal)
- Document root: `/var/www/html/public`
- PHP-FPM: 127.0.0.1:9000
- Security headers, gzip, static caching

### 4. supervisord.conf

Process manager:
- Runs PHP-FPM (priority 5)
- Runs Nginx (priority 10)
- Auto-restart on failure

### 5. .env.production (NOT in Git)

Production environment variables:
- APP_KEY, APP_DEBUG, APP_URL
- DB credentials
- ABLY_KEY for real-time features
- Cache/session configuration

**Location:** `~/apps/tamiya-laravel-app/.env.production`
**Permissions:** `600` (read/write for owner only)

---

## Deployment Workflow

### Method 1: Automated (Recommended)

**Push to GitHub:**

```bash
# On local machine
git add .
git commit -m "Your changes"
git push origin deva
```

**GitHub Actions automatically:**
1. SSH into VPS
2. Pull latest code from `deva` branch
3. Install Composer dependencies
4. Build Docker image
5. Stop old container
6. Start new container
7. Wait for health check
8. Run migrations
9. Optimize Laravel
10. Verify deployment

**Time:** 5-10 minutes

---

### Method 2: Manual Deployment

**On VPS:**

```bash
cd ~/apps/tamiya-laravel-app

# Pull latest code
git pull origin deva

# Install dependencies
docker run --rm -v $(pwd):/app -w /app composer:2 install --no-dev

# Build image
docker build -t tamiya-laravel-app:latest .

# Deploy
docker-compose --env-file .env.production down
docker-compose --env-file .env.production up -d

# Run migrations
docker exec tamiya-laravel-app php artisan migrate --force

# Optimize
docker exec tamiya-laravel-app php artisan optimize
```

**Time:** 3-5 minutes

---

## Why This Structure?

### Version Control

**All infrastructure is in Git:**
- Dockerfile changes tracked
- Nginx config changes tracked
- Docker Compose updates tracked
- Easy to review changes
- Easy to rollback

### Simplified Workflow

**Single `git pull` updates everything:**
- Application code
- Docker configuration
- Nginx configuration
- Dependencies (via composer.lock)

### Environment Separation

**Secrets stay out of Git:**
- `.env.production` excluded via `.gitignore`
- Secrets managed via GitHub Secrets
- Injected during deployment

---

## Benefits

### For Developers

- Clone repository and have complete deployment setup
- Test Docker builds locally
- See complete infrastructure configuration
- Track infrastructure changes in pull requests

### For Operations

- Consistent deployments across environments
- Easy rollback to any commit
- Automated CI/CD via GitHub Actions
- Health checks and monitoring built-in

### For Security

- Secrets never committed to Git
- Environment files have correct permissions
- Non-root user in container
- Security headers configured
- Regular dependency updates via Dependabot

---

## Networks

The application uses two Docker networks:

### web-network (external)
- Connects to Nginx Proxy Manager
- Provides HTTPS termination
- SSL certificates via Let's Encrypt

### db-network (external)
- Connects to MySQL server (`mysql-server`)
- Database: `tamiya_laravel`
- User: `tamiya_laravel_app`

---

## Persistent Data

### Storage Directory
**Host:** `~/apps/tamiya-laravel-app/storage`
**Container:** `/var/www/html/storage`
**Contents:**
- Uploaded files
- Session data
- Cache files
- Application logs

### Bootstrap Cache
**Host:** `~/apps/tamiya-laravel-app/bootstrap/cache`
**Container:** `/var/www/html/bootstrap/cache`
**Contents:**
- Compiled views
- Cached config
- Cached routes
- Service cache

**Why mounted?**
- Persists across container restarts
- Survives container rebuilds
- Faster container startup
- No data loss on deployment

---

## Health Checks

### Docker Health Check
**Endpoint:** `http://localhost/api/health`
**Interval:** 30 seconds
**Timeout:** 10 seconds
**Start period:** 60 seconds
**Retries:** 3

**States:**
- `starting` - Container initializing
- `healthy` - All checks passing
- `unhealthy` - Checks failing

### Health Endpoint Response

```json
{
  "status": "healthy",
  "timestamp": "2026-01-14T10:30:00+00:00",
  "database": "connected"
}
```

---

## Monitoring

### Container Status

```bash
# Check status
docker ps -f name=tamiya-laravel-app

# Check health
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app

# View logs
docker logs -f tamiya-laravel-app
```

### Application Logs

```bash
# Laravel logs
docker exec tamiya-laravel-app tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Nginx access logs
docker exec tamiya-laravel-app tail -f /var/log/nginx/access.log

# Nginx error logs
docker exec tamiya-laravel-app tail -f /var/log/nginx/error.log
```

### Verification Script

```bash
~/scripts/verify-tamiya.sh
```

Checks:
- Container status
- Health status
- Database connection
- Web endpoint
- Health endpoint JSON

---

## Backup Strategy

### Database Backups
**Schedule:** Daily at 3 AM (cron)
**Retention:** 30 days or last 50 backups
**Location:** `~/backups/tamiya/`
**Script:** `~/scripts/backup-tamiya-db.sh`

### Manual Backup

```bash
~/scripts/backup-tamiya-db.sh
```

### Restore from Backup

```bash
# List backups
ls -lh ~/backups/tamiya/

# Restore specific backup
gunzip -c ~/backups/tamiya/tamiya_laravel_20260114_030000.sql.gz | \
  docker exec -i mysql-server mysql -u tamiya_laravel_app -p tamiya_laravel
```

---

## Rollback Procedure

### Code Rollback

```bash
cd ~/apps/tamiya-laravel-app

# View recent commits
git log --oneline -10

# Rollback to specific commit
git checkout <commit-hash>

# Rebuild and deploy
docker build -t tamiya-laravel-app:latest .
docker-compose --env-file .env.production up -d

# Run migrations if needed
docker exec tamiya-laravel-app php artisan migrate --force
```

### Database Rollback

```bash
# Rollback last migration batch
docker exec tamiya-laravel-app php artisan migrate:rollback --force

# Rollback specific steps
docker exec tamiya-laravel-app php artisan migrate:rollback --step=2 --force
```

---

## Security Considerations

### Container Security
- Non-root user (`laravel`, UID 1000)
- Read-only filesystem where possible
- No unnecessary capabilities
- Regular base image updates

### Network Security
- Internal networks only
- No direct internet exposure
- HTTPS via Nginx Proxy Manager
- Firewall rules via UFW

### Application Security
- Environment secrets via GitHub Secrets
- `.env.production` permissions: 600
- Security headers configured
- SQL injection prevention via Eloquent
- XSS prevention via Blade escaping

### Database Security
- Dedicated user with limited privileges
- Network-isolated via db-network
- Regular backups
- No root access from application

---

## Troubleshooting

### Container Won't Start

```bash
# View logs
docker logs tamiya-laravel-app

# Common fixes:
# 1. Check .env.production exists
# 2. Verify permissions on storage/
# 3. Test database connection
# 4. Rebuild image
```

### Database Connection Failed

```bash
# Test MySQL
docker exec -it mysql-server mysql -u tamiya_laravel_app -p

# Check credentials
cat ~/apps/tamiya-laravel-app/.env.production | grep DB_

# Test from container
docker exec tamiya-laravel-app php artisan tinker --execute="DB::connection()->getPdo();"
```

### Health Check Failing

```bash
# Test endpoint directly
docker exec tamiya-laravel-app curl http://localhost/api/health

# Check PHP-FPM
docker exec tamiya-laravel-app ps aux | grep php-fpm

# Check Nginx
docker exec tamiya-laravel-app ps aux | grep nginx
```

### Permission Issues

```bash
# Fix storage permissions (host)
sudo chown -R 1000:1000 ~/apps/tamiya-laravel-app/storage
sudo chmod -R 775 ~/apps/tamiya-laravel-app/storage

# Fix storage permissions (container)
docker exec tamiya-laravel-app chown -R laravel:laravel /var/www/html/storage
docker exec tamiya-laravel-app chmod -R 775 /var/www/html/storage
```

---

## Related Documentation

- [VPS Migration Guide](vps_docs/MIGRATION-GUIDE.md) - Migrate from old structure
- [Laravel Deployment Guide](vps_docs/07-LARAVEL-DEPLOYMENT.md) - Full deployment walkthrough
- [GitHub Actions Workflow](.github/workflows/deploy.yml) - CI/CD configuration

---

## Quick Reference

### Build and Deploy
```bash
docker build -t tamiya-laravel-app:latest .
docker-compose --env-file .env.production up -d
```

### View Logs
```bash
docker logs -f tamiya-laravel-app
```

### Run Artisan Commands
```bash
docker exec tamiya-laravel-app php artisan <command>
```

### Access Shell
```bash
docker exec -it tamiya-laravel-app sh
```

### Restart Container
```bash
docker restart tamiya-laravel-app
```

### Check Health
```bash
curl http://localhost/api/health
```

---

**Document Version:** 1.0
**Last Updated:** 2026-01-14
**Maintained By:** DevOps Team
