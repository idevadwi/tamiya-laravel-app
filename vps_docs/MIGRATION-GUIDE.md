# VPS Migration Guide: Restructured Deployment

## Overview

This guide helps you migrate from the old deployment structure (with nested `app/` directory) to the new simplified structure where Docker configuration files are versioned in Git.

## Problem Summary

**Old Structure (PROBLEMATIC):**
```
~/apps/tamiya-laravel-app/
├── app/                     ← Git clone here
│   ├── app/
│   ├── routes/
│   └── ... (Laravel files)
├── Dockerfile               ← Outside Git (manual updates)
├── docker-compose.yml       ← Outside Git (manual updates)
├── nginx-app.conf           ← Outside Git (manual updates)
├── supervisord.conf         ← Outside Git (manual updates)
└── .env.production
```

**Issue:** When you `git pull` inside `app/`, only Laravel code updates. Docker configs remain outdated.

**New Structure (FIXED):**
```
~/apps/tamiya-laravel-app/   ← Git clone directly here
├── app/
├── routes/
├── Dockerfile               ← IN Git (auto-updates)
├── docker-compose.yml       ← IN Git (auto-updates)
├── nginx-app.conf           ← IN Git (auto-updates)
├── supervisord.conf         ← IN Git (auto-updates)
├── .env.production          ← Outside Git (secrets)
└── ... (Laravel files)
```

**Benefit:** `git pull` updates everything. Docker configs are versioned.

---

## Migration Steps

### Step 1: Backup Current Setup

**On VPS:**

```bash
# Backup database
~/scripts/backup-tamiya-db.sh

# Backup current deployment directory
cd ~/apps
tar -czf tamiya-backup-$(date +%Y%m%d_%H%M%S).tar.gz tamiya-laravel-app/

# Backup .env.production
cp tamiya-laravel-app/.env.production ~/tamiya-env-backup.production
```

Verify backup:
```bash
ls -lh ~/apps/tamiya-backup-*.tar.gz
ls -lh ~/tamiya-env-backup.production
```

---

### Step 2: Stop and Remove Old Container

```bash
cd ~/apps/tamiya-laravel-app

# Stop container
docker-compose --env-file .env.production down

# Remove container and image
docker stop tamiya-laravel-app || true
docker rm tamiya-laravel-app || true
docker rmi tamiya-laravel-app:latest || true

# Verify
docker ps -a | grep tamiya
```

---

### Step 3: Restructure Directories

```bash
cd ~/apps

# Rename old directory
mv tamiya-laravel-app tamiya-laravel-app-old

# Clone repository directly (replace with your repo URL)
git clone https://github.com/YOUR_USERNAME/tamiya-laravel-app.git
cd tamiya-laravel-app
git checkout deva

# Verify new structure
ls -la
# Should see: Dockerfile, docker-compose.yml, nginx-app.conf, supervisord.conf
```

---

### Step 4: Restore Environment File

```bash
cd ~/apps/tamiya-laravel-app

# Copy .env.production from backup
cp ~/tamiya-env-backup.production .env.production

# Set correct permissions
chmod 600 .env.production
chown deva:deva .env.production

# Verify
ls -la .env.production
# Should show: -rw------- 1 deva deva
```

---

### Step 5: Restore Storage Data

```bash
cd ~/apps/tamiya-laravel-app

# Copy persistent storage from old deployment
cp -r ~/apps/tamiya-laravel-app-old/storage/app ./storage/
cp -r ~/apps/tamiya-laravel-app-old/storage/framework ./storage/

# Set permissions
sudo chown -R 1000:1000 storage/
sudo chmod -R 775 storage/
```

---

### Step 6: Install Dependencies

```bash
cd ~/apps/tamiya-laravel-app

# Install Composer dependencies
docker run --rm -v $(pwd):/app -w /app composer:2 install \
  --no-interaction \
  --prefer-dist \
  --optimize-autoloader \
  --no-dev
```

---

### Step 7: Build and Deploy

```bash
cd ~/apps/tamiya-laravel-app

# Build Docker image
docker build -t tamiya-laravel-app:latest -f Dockerfile .

# Start container
docker-compose --env-file .env.production up -d

# Wait for health check
for i in {1..30}; do
  if docker ps --filter "name=tamiya-laravel-app" --filter "health=healthy" | grep -q tamiya-laravel-app; then
    echo "✅ Container is healthy!"
    break
  fi
  echo "Waiting... ($i/30)"
  sleep 2
done
```

---

### Step 8: Run Laravel Setup

```bash
cd ~/apps/tamiya-laravel-app

# Run migrations
docker exec tamiya-laravel-app php artisan migrate --force

# Optimize Laravel
docker exec tamiya-laravel-app php artisan optimize:clear
docker exec tamiya-laravel-app php artisan config:cache
docker exec tamiya-laravel-app php artisan route:cache
docker exec tamiya-laravel-app php artisan view:cache

# Fix permissions
docker exec tamiya-laravel-app chown -R laravel:laravel /var/www/html/storage
docker exec tamiya-laravel-app chmod -R 775 /var/www/html/storage
```

---

### Step 9: Verify Deployment

```bash
# Check container status
docker ps -f name=tamiya-laravel-app

# Check health
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app

# Test health endpoint
curl http://localhost/api/health

# Test public endpoint
curl -I https://race-lane.com

# Check logs
docker logs --tail 50 tamiya-laravel-app
```

**Expected Results:**
- Container status: `Up X minutes (healthy)`
- Health endpoint: `{"status":"healthy","database":"connected"}`
- Public endpoint: `HTTP/2 200`

---

### Step 10: Update Management Scripts

**Update verification script:**

```bash
nano ~/scripts/verify-tamiya.sh
```

Replace the cd path:
```bash
# OLD:
cd ~/apps/tamiya-laravel-app/app

# NEW:
cd ~/apps/tamiya-laravel-app
```

**Update backup script:**

```bash
nano ~/scripts/backup-tamiya-db.sh
```

Verify path is correct (should be fine, but check):
```bash
DB_PASSWORD=$(grep DB_PASSWORD ~/apps/tamiya-laravel-app/.env.production | cut -d'=' -f2)
```

**Update rollback script:**

```bash
nano ~/scripts/rollback-tamiya.sh
```

Replace:
```bash
# OLD:
cd ~/apps/tamiya-laravel-app/app

# NEW:
cd ~/apps/tamiya-laravel-app
```

---

### Step 11: Test Full Workflow

```bash
# Test verification script
~/scripts/verify-tamiya.sh

# Test git pull + rebuild workflow
cd ~/apps/tamiya-laravel-app
git pull origin deva
docker build -t tamiya-laravel-app:latest .
docker-compose --env-file .env.production up -d

# Verify
docker ps -f name=tamiya-laravel-app
curl https://race-lane.com/api/health
```

---

### Step 12: Clean Up Old Files

**Only after confirming everything works:**

```bash
# Remove old deployment directory
rm -rf ~/apps/tamiya-laravel-app-old

# Remove old Docker images
docker image prune -a -f

# Verify disk space recovered
df -h
```

---

## New Deployment Workflow

### Manual Deployment (When Needed)

```bash
cd ~/apps/tamiya-laravel-app
git pull origin deva
docker run --rm -v $(pwd):/app -w /app composer:2 install --no-dev
docker build -t tamiya-laravel-app:latest .
docker-compose --env-file .env.production down
docker-compose --env-file .env.production up -d
docker exec tamiya-laravel-app php artisan migrate --force
docker exec tamiya-laravel-app php artisan optimize
```

### Automated Deployment (Recommended)

Just push to GitHub:

```bash
# On local machine
git add .
git commit -m "Your changes"
git push origin deva
```

GitHub Actions automatically:
1. Pulls code on VPS
2. Installs dependencies
3. Builds Docker image
4. Deploys container
5. Runs migrations
6. Optimizes Laravel

---

## Benefits of New Structure

### Before (Old Structure):
- `git pull` only updates Laravel code in `app/`
- Docker configs outside Git require manual updates
- Difficult to track configuration changes
- Risk of config drift between environments

### After (New Structure):
- `git pull` updates **everything** (code + Docker configs)
- All infrastructure is version-controlled
- Easy to rollback both code and config
- Configuration changes tracked in Git history
- Simpler directory structure

---

## Troubleshooting

### Container won't start

```bash
# Check logs
docker logs tamiya-laravel-app

# Common issues:
# 1. .env.production not found
cp ~/tamiya-env-backup.production ~/apps/tamiya-laravel-app/.env.production

# 2. Permission issues
sudo chown -R 1000:1000 ~/apps/tamiya-laravel-app/storage
sudo chmod -R 775 ~/apps/tamiya-laravel-app/storage
```

### Database connection failed

```bash
# Test MySQL connection
docker exec -it mysql-server mysql -u tamiya_laravel_app -p tamiya_laravel

# Check .env.production
cat ~/apps/tamiya-laravel-app/.env.production | grep DB_

# Update if needed
nano ~/apps/tamiya-laravel-app/.env.production
```

### Git pull fails

```bash
cd ~/apps/tamiya-laravel-app

# Check git status
git status

# Reset if needed
git fetch origin
git reset --hard origin/deva
git clean -fd
```

### Missing storage data

```bash
# Restore from old deployment
cp -r ~/apps/tamiya-laravel-app-old/storage/app ~/apps/tamiya-laravel-app/storage/
cp -r ~/apps/tamiya-laravel-app-old/storage/framework ~/apps/tamiya-laravel-app/storage/

# Fix permissions
sudo chown -R 1000:1000 ~/apps/tamiya-laravel-app/storage
sudo chmod -R 775 ~/apps/tamiya-laravel-app/storage
```

---

## Rollback to Old Structure (Emergency)

If migration fails and you need to restore:

```bash
# Stop new deployment
cd ~/apps/tamiya-laravel-app
docker-compose --env-file .env.production down

# Remove new directory
cd ~/apps
rm -rf tamiya-laravel-app

# Restore from backup
tar -xzf tamiya-backup-YYYYMMDD_HHMMSS.tar.gz

# Start old container
cd tamiya-laravel-app
docker-compose --env-file .env.production up -d
```

---

## Verification Checklist

After migration, verify:

- [ ] Container is running and healthy
- [ ] Website is accessible at https://race-lane.com
- [ ] Health endpoint returns 200 OK
- [ ] Database connection works
- [ ] Storage files are preserved
- [ ] SSL certificate is valid
- [ ] Nginx Proxy Manager proxy host works
- [ ] GitHub Actions workflow succeeds
- [ ] Management scripts work
- [ ] Git pull updates all files

---

## Summary

**Old Workflow:**
```bash
cd ~/apps/tamiya-laravel-app/app
git pull
# Docker configs outdated, need manual update
```

**New Workflow:**
```bash
cd ~/apps/tamiya-laravel-app
git pull
docker build -t tamiya-laravel-app:latest .
docker-compose --env-file .env.production up -d
```

**Even Better (Automated):**
```bash
# Just push to GitHub, CI/CD handles everything
git push origin deva
```

---

**Migration Complete!** Your deployment structure is now cleaner, easier to maintain, and fully version-controlled.

**Document Version**: 1.0
**Last Updated**: 2026-01-14
**Migration Time**: ~15-20 minutes
