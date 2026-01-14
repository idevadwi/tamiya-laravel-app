# VPS Migration Commands - Execute These on Your Server

## Summary of Changes

You now have a **restructured deployment** where:
- All Docker configs are **versioned in Git** (Dockerfile, docker-compose.yml, etc.)
- `git pull` updates **everything** (code + configs)
- Simpler, cleaner directory structure
- Automated CI/CD via GitHub Actions

---

## Step 1: Push Changes to GitHub

**On your local machine, run these commands:**

```bash
cd "F:\File Deva\Git Clone\tamiya-laravel-app"

# Check what files were added
git status

# Add all new Docker configuration files
git add Dockerfile docker-compose.yml nginx-app.conf supervisord.conf
git add .github/workflows/deploy.yml
git add DEPLOYMENT-STRUCTURE.md VPS-COMMANDS.md
git add vps_docs/MIGRATION-GUIDE.md
git add README.md

# Commit the changes
git commit -m "Restructure deployment: Add Docker configs to Git repository

- Add Dockerfile, docker-compose.yml, nginx-app.conf, supervisord.conf
- Update GitHub Actions workflow for new structure
- Add comprehensive deployment documentation
- Update README with deployment instructions
- Simplify deployment workflow (git pull updates everything)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"

# Push to deva branch
git push origin deva
```

---

## Step 2: SSH into Your VPS

```bash
ssh deva@YOUR_VPS_IP
```

---

## Step 3: Backup Current Setup

**IMPORTANT: Always backup before making changes!**

```bash
# Backup database
~/scripts/backup-tamiya-db.sh

# Backup current deployment directory
cd ~/apps
tar -czf tamiya-backup-$(date +%Y%m%d_%H%M%S).tar.gz tamiya-laravel-app/

# Backup .env.production
cp tamiya-laravel-app/.env.production ~/tamiya-env-backup.production

# Verify backups
ls -lh ~/apps/tamiya-backup-*.tar.gz
ls -lh ~/tamiya-env-backup.production
```

---

## Step 4: Check Current Structure

```bash
cd ~/apps/tamiya-laravel-app
ls -la

# You should see:
# - app/ directory (git clone)
# - Dockerfile, docker-compose.yml, etc. (outside app/)
```

---

## Step 5: Stop Container

```bash
cd ~/apps/tamiya-laravel-app

# Stop and remove container
docker-compose --env-file .env.production down
docker stop tamiya-laravel-app || true
docker rm tamiya-laravel-app || true
```

---

## Step 6: Restructure Directories

```bash
cd ~/apps

# Rename old directory
mv tamiya-laravel-app tamiya-laravel-app-old

# Clone repository directly (replace YOUR_USERNAME)
git clone https://github.com/YOUR_USERNAME/tamiya-laravel-app.git
cd tamiya-laravel-app
git checkout deva

# Verify new structure
ls -la
# Should see: Dockerfile, docker-compose.yml, nginx-app.conf, supervisord.conf
# All in root, not in app/ subdirectory!
```

---

## Step 7: Restore Environment and Storage

```bash
cd ~/apps/tamiya-laravel-app

# Restore .env.production
cp ~/tamiya-env-backup.production .env.production
chmod 600 .env.production
chown deva:deva .env.production

# Restore storage data
cp -r ~/apps/tamiya-laravel-app-old/storage/app ./storage/ 2>/dev/null || true
cp -r ~/apps/tamiya-laravel-app-old/storage/framework ./storage/ 2>/dev/null || true

# Set permissions
sudo chown -R 1000:1000 storage/
sudo chmod -R 775 storage/
```

---

## Step 8: Install Dependencies and Build

```bash
cd ~/apps/tamiya-laravel-app

# Install Composer dependencies
docker run --rm -v $(pwd):/app -w /app composer:2 install \
  --no-interaction \
  --prefer-dist \
  --optimize-autoloader \
  --no-dev

# Build Docker image
docker build -t tamiya-laravel-app:latest -f Dockerfile .
```

---

## Step 9: Deploy Container

```bash
cd ~/apps/tamiya-laravel-app

# Start container
docker-compose --env-file .env.production up -d

# Wait for health check (30-60 seconds)
for i in {1..30}; do
  if docker ps --filter "name=tamiya-laravel-app" --filter "health=healthy" | grep -q tamiya-laravel-app; then
    echo "✅ Container is healthy!"
    break
  fi
  echo "Waiting for health check... ($i/30)"
  sleep 2
done
```

---

## Step 10: Run Laravel Setup

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

## Step 11: Verify Deployment

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

## Step 12: Update Management Scripts

### Update Verification Script

```bash
nano ~/scripts/verify-tamiya.sh
```

Change line (if it exists):
```bash
# OLD:
cd ~/apps/tamiya-laravel-app/app

# NEW:
cd ~/apps/tamiya-laravel-app
```

Save and exit: `Ctrl+O`, `Enter`, `Ctrl+X`

### Update Rollback Script (if exists)

```bash
nano ~/scripts/rollback-tamiya.sh
```

Change line:
```bash
# OLD:
cd ~/apps/tamiya-laravel-app/app

# NEW:
cd ~/apps/tamiya-laravel-app
```

Save and exit: `Ctrl+O`, `Enter`, `Ctrl+X`

---

## Step 13: Test New Workflow

```bash
# Test git pull + rebuild workflow
cd ~/apps/tamiya-laravel-app
git pull origin deva

# Build and deploy
docker build -t tamiya-laravel-app:latest .
docker-compose --env-file .env.production up -d

# Verify
docker ps -f name=tamiya-laravel-app
curl https://race-lane.com/api/health
```

---

## Step 14: Clean Up (Optional - After Confirming Everything Works)

**ONLY run this after 24-48 hours of successful operation!**

```bash
# Remove old deployment directory
rm -rf ~/apps/tamiya-laravel-app-old

# Remove old Docker images
docker image prune -a -f

# Verify disk space recovered
df -h
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
- [ ] Management scripts work
- [ ] Git pull updates all files (test this!)

---

## Troubleshooting

### Container won't start

```bash
# Check logs
docker logs tamiya-laravel-app

# Common fixes:
# 1. Check .env.production exists
ls -la .env.production

# 2. Verify permissions
sudo chown -R 1000:1000 ~/apps/tamiya-laravel-app/storage
sudo chmod -R 775 ~/apps/tamiya-laravel-app/storage
```

### Database connection failed

```bash
# Test MySQL connection
docker exec -it mysql-server mysql -u tamiya_laravel_app -p tamiya_laravel

# Check .env.production
cat ~/apps/tamiya-laravel-app/.env.production | grep DB_
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

---

## New Deployment Workflow (After Migration)

### Automated (Recommended)

Just push to GitHub from your local machine:
```bash
git push origin deva
```

GitHub Actions automatically deploys (5-10 minutes).

### Manual (When Needed)

On VPS:
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

---

## Rollback (Emergency Only)

If migration fails completely:

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

## Summary

**Before:**
- `git clone` goes into `app/` subdirectory
- Docker configs outside Git (manual updates)
- `git pull` only updates Laravel code

**After:**
- `git clone` directly into deployment directory
- All configs in Git (version-controlled)
- `git pull` updates everything

**Benefits:**
- Simpler structure
- Easier maintenance
- Better version control
- Consistent deployments

---

**Migration Complete!** Your deployment is now properly structured.

**Document Version:** 1.0
**Last Updated:** 2026-01-14
