# Fixing 500 Error on VPS - Ably Integration Issue (Docker Environment)

## Problem Description
When submitting BTO updates from `/tournament/tracks`, you get a 500 Internal Server Error on VPS but not locally. The data saves successfully after refresh.

## Root Cause
The issue is in the Ably real-time messaging integration that runs after saving BTO data.

## Your VPS Architecture
- **Container**: `tamiya-laravel-app` (Docker)
- **App Directory**: `/home/deva/apps/tamiya-laravel-app/`
- **Environment File**: `.env.production`
- **Reverse Proxy**: Nginx Proxy Manager
- **Domain**: race-lane.com

## Investigation Steps

### 1. Check Laravel Logs (Docker)

First, check the actual error in your VPS logs:

```bash
# SSH into your VPS
ssh deva@<VPS_IP>

# View container logs (stdout/stderr)
docker logs --tail 100 tamiya-laravel-app

# View Laravel application logs
docker exec tamiya-laravel-app tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log

# View all Laravel log files
docker exec tamiya-laravel-app ls -lh storage/logs/

# Real-time log monitoring
docker logs -f tamiya-laravel-app
```

Look for errors related to:
- Ably connection failures
- SSL/TLS certificate errors
- Timeout errors
- Missing configuration errors

### 2. Verify Ably Configuration

Check if Ably key is configured on VPS:

```bash
# SSH into your VPS
ssh deva@<VPS_IP>

# Check environment file
cd ~/apps/tamiya-laravel-app
cat .env.production | grep ABLY
```

Expected output:
```
ABLY_KEY=your_ably_api_key_here
ABLY_CHANNEL_PREFIX=tamiya
```

Check if Ably key is loaded in the container:
```bash
# Verify config is cached correctly
docker exec tamiya-laravel-app php artisan config:show | grep -i ably

# Or check via tinker
docker exec tamiya-laravel-app php artisan tinker --execute="echo config('services.ably.key');"
```

If missing or incorrect, update `.env.production` and clear config cache:
```bash
# Update .env.production
nano ~/apps/tamiya-laravel-app/.env.production
# Add or update: ABLY_KEY=your_api_key_here

# Clear and re-cache config
docker exec tamiya-laravel-app php artisan config:clear
docker exec tamiya-laravel-app php artisan config:cache
```

### 3. Test Network Connectivity from Container

Test if the Docker container can reach Ably's API:

```bash
# Test DNS resolution from container
docker exec tamiya-laravel-app nslookup rest.ably.io

# Test HTTP connection from container
docker exec tamiya-laravel-app curl -I https://rest.ably.io

# Test with verbose output
docker exec tamiya-laravel-app curl -v https://rest.ably.io/time

# Test actual Ably connection
docker exec tamiya-laravel-app php artisan tinker
```

In Tinker:
```php
try {
    $ably = new \Ably\AblyRest(config('services.ably.key'));
    $channel = $ably->channels->get('test-channel');
    $channel->publish('test-event', ['message' => 'Hello from VPS']);
    echo "✅ Ably connection successful!";
} catch (\Exception $e) {
    echo "❌ Ably connection failed: " . $e->getMessage();
}
exit;
```

If any of these fail, you may need to:
- Check Docker network configuration
- Check firewall rules (allow outbound HTTPS on port 443)
- Check DNS configuration
- Contact your VPS provider about network restrictions

### 4. Check Docker Network Configuration

Verify the container is properly connected to networks:

```bash
# Check container networks
docker network inspect web-network | grep -A 5 tamiya
docker network inspect db-network | grep -A 5 tamiya

# Check if container can reach external networks
docker exec tamiya-laravel-app ping -c 3 8.8.8.8
docker exec tamiya-laravel-app curl -I https://www.google.com
```

### 5. Check SSL/TLS Certificates in Container

```bash
# Test SSL connection from container
docker exec tamiya-laravel-app openssl s_client -connect rest.ably.io:443 -servername rest.ably.io

# Check if CA certificates are installed in container
docker exec tamiya-laravel-app ls -la /etc/ssl/certs/

# Check PHP SSL configuration
docker exec tamiya-laravel-app php -i | grep -i openssl
```

If SSL issues exist, you may need to:
- Rebuild the Docker image with updated CA certificates
- Check if the Alpine base image has CA certificates installed
- Verify Dockerfile includes CA certificate installation

### 5. Temporary Fix - Disable Ably Publishing

**NOTE: The code has already been updated with try-catch blocks in [`BestTimeController.php`](app/Http/Controllers/BestTimeController.php:183-192) and [`BestTimeController.php`](app/Http/Controllers/BestTimeController.php:333-341). The 500 error should now be resolved.**

If you still need to completely disable Ably publishing while debugging:

**Option A: Comment out the publish calls**

In `app/Http/Controllers/BestTimeController.php`:

Comment out lines 183-192 and 333-341:
```php
// Publish to Ably (non-blocking - won't cause 500 error if it fails)
// try {
//     $this->publishTrackUpdate($tournament, $validated['track']);
// } catch (\Exception $e) {
//     \Log::warning('Ably publish failed after storing best time', [
//         'error' => $e->getMessage(),
//         'track' => $validated['track'],
//         'scope' => $validated['scope']
//     ]);
//     // Continue execution even if Ably fails
// }
```

**Option B: Set ABLY_KEY to empty**

Temporarily disable Ably by setting an empty key:
```bash
# On VPS
nano ~/apps/tamiya-laravel-app/.env.production
# Change: ABLY_KEY=
# Save and exit

# Clear config cache
docker exec tamiya-laravel-app php artisan config:clear
docker exec tamiya-laravel-app php artisan config:cache
```

### 6. Permanent Fix - Already Applied

The following fixes have already been applied to your codebase:

**1. BestTimeController.php** - Non-blocking Ably publishing:
- Added try-catch blocks around [`publishTrackUpdate()`](app/Http/Controllers/BestTimeController.php:183-192) calls in [`store()`](app/Http/Controllers/BestTimeController.php:92) method
- Added try-catch blocks around [`publishTrackUpdate()`](app/Http/Controllers/BestTimeController.php:333-341) calls in [`update()`](app/Http/Controllers/BestTimeController.php:235) method
- Logs warnings instead of throwing exceptions

**2. AblyHelper.php** - Improved error handling:
- Added 10-second timeout to prevent hanging
- Separate catch block for `AblyException`
- Enhanced logging with error codes
- Better SSL/TLS configuration

### 7. Queue-Based Publishing (Recommended for Production)

For better reliability, move Ably publishing to a background queue:

1. Create a job:
```bash
# On local machine
php artisan make:job PublishToAbly
```

2. In the job, handle the Ably publishing with retries
3. Dispatch the job from the controller instead of calling directly
4. Configure queue worker on VPS

**Queue worker setup in Docker:**

Update `docker-compose.yml` to add a queue worker service:
```yaml
services:
  tamiya-app:
    # ... existing config ...

  tamiya-queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: tamiya-laravel-queue
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3 --timeout=90
    volumes:
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=${APP_ENV:-production}
      - DB_CONNECTION=mysql
      - DB_HOST=mysql-server
      - DB_DATABASE=${DB_DATABASE:-tamiya_laravel}
      - DB_USERNAME=${DB_USERNAME:-tamiya_laravel_app}
      - DB_PASSWORD=${DB_PASSWORD}
      - ABLY_KEY=${ABLY_KEY}
    networks:
      - db-network
```

## Configuration Checklist

Ensure these are set correctly on your VPS:

- [ ] `ABLY_KEY` in `.env.production` file
- [ ] `config/services.php` has Ably configuration
- [ ] Outbound HTTPS (port 443) allowed in UFW firewall
- [ ] DNS resolution working from container
- [ ] SSL certificates installed and valid in container
- [ ] PHP has OpenSSL extension enabled (check with `php -i | grep openssl`)
- [ ] Sufficient memory allocated to PHP in `php.ini`
- [ ] Execution time limits are adequate (check `php -i | grep max_execution_time`)

## Testing After Fixes

After applying fixes:

1. Clear all caches in container:
```bash
docker exec tamiya-laravel-app php artisan optimize:clear
docker exec tamiya-laravel-app php artisan config:cache
docker exec tamiya-laravel-app php artisan route:cache
docker exec tamiya-laravel-app php artisan view:cache
```

2. Test the BTO update functionality at https://race-lane.com/tournament/tracks

3. Check logs for any remaining errors:
```bash
# Real-time monitoring
docker logs -f tamiya-laravel-app

# Check for Ably-specific errors
docker logs tamiya-laravel-app 2>&1 | grep -i ably

# Check Laravel logs
docker exec tamiya-laravel-app tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
```

4. Verify data is saved correctly in database:
```bash
docker exec -it mysql-server mysql -u tamiya_laravel_app -p tamiya_laravel
# Enter password when prompted

# Check best_times table
SELECT * FROM best_times ORDER BY created_at DESC LIMIT 5;
EXIT;
```

5. Verify Ably messages are being published (if enabled):
```bash
# Check logs for successful publishes
docker logs tamiya-laravel-app 2>&1 | grep "Ably message published"
```

## Common Error Messages and Solutions

### "Connection timed out"
**Symptoms**: Ably request hangs or takes too long

**Solutions**:
```bash
# Check firewall settings
sudo ufw status | grep 443
# Should show: 443/tcp ALLOW Anywhere

# Check Docker network
docker network inspect web-network | grep -A 10 tamiya

# Test connectivity from container
docker exec tamiya-laravel-app timeout 5 curl -I https://rest.ably.io

# Check if timeout is set correctly in AblyHelper
docker exec tamiya-laravel-app grep -A 5 "new AblyRest" app/Helpers/AblyHelper.php
```

### "SSL certificate problem"
**Symptoms**: SSL/TLS handshake fails

**Solutions**:
```bash
# Check system time (SSL certificates fail if time is incorrect)
docker exec tamiya-laravel-app date
# Should show correct time

# Check if CA certificates are installed in container
docker exec tamiya-laravel-app ls -la /etc/ssl/certs/

# Rebuild Docker image with updated CA certificates if needed
cd ~/apps/tamiya-laravel-app
docker build -t tamiya-laravel-app:latest -f Dockerfile .
docker-compose --env-file .env.production up -d
```

### "Ably key not configured"
**Symptoms**: Warning in logs about missing ABLY_KEY

**Solutions**:
```bash
# Add ABLY_KEY to .env.production
nano ~/apps/tamiya-laravel-app/.env.production
# Add: ABLY_KEY=your_api_key_here

# Verify key is set
docker exec tamiya-laravel-app php artisan tinker --execute="echo config('services.ably.key');"

# Clear and re-cache config
docker exec tamiya-laravel-app php artisan config:clear
docker exec tamiya-laravel-app php artisan config:cache

# Verify in config
docker exec tamiya-laravel-app php artisan config:show | grep -i ably
```

### "cURL error 6: Could not resolve host"
**Symptoms**: DNS resolution fails

**Solutions**:
```bash
# Check DNS configuration from container
docker exec tamiya-laravel-app cat /etc/resolv.conf

# Test DNS resolution
docker exec tamiya-laravel-app nslookup rest.ably.io
docker exec tamiya-laravel-app dig rest.ably.io +short

# Test with different DNS server
docker exec tamiya-laravel-app nslookup rest.ably.io 8.8.8.8

# Check Docker daemon DNS settings
sudo systemctl restart docker
# Or configure DNS in /etc/docker/daemon.json
```

### "Ably API error: 40101"
**Symptoms**: Invalid API key

**Solutions**:
```bash
# Verify ABLY_KEY format (should be: xxxxxxxx.xxxxxxxx:xxxxxxxxxxxxxxxxxxxx)
docker exec tamiya-laravel-app php artisan tinker --execute="echo config('services.ably.key');"

# Check if key is correct in Ably dashboard
# https://ably.com/dashboard

# Update .env.production with correct key
nano ~/apps/tamiya-laravel-app/.env.production

# Clear config cache
docker exec tamiya-laravel-app php artisan config:clear
docker exec tamiya-laravel-app php artisan config:cache
```

## Monitoring Ably Publishing

Set up monitoring for Ably publishing failures:

**Option 1: Log monitoring**
```bash
# Create script to monitor Ably failures
nano ~/scripts/monitor-ably.sh
```

Content:
```bash
#!/bash
# Monitor Ably publish failures in last hour
docker logs --since 1h tamiya-laravel-app 2>&1 | grep -i "ably publish failed" | wc -l
```

Make executable:
```bash
chmod +x ~/scripts/monitor-ably.sh
```

**Option 2: Alert on failures**
Add to `app/Helpers/AblyHelper.php`:
```php
public static function publish(string $channelName, string $eventName, array $data): bool
{
    try {
        // ... existing code ...
        return true;
    } catch (\Exception $e) {
        Log::error('Ably publish failed', [
            'channel' => $channelName,
            'event' => $eventName,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Send alert (implement your notification system)
        // Example: Slack, email, Sentry, etc.
        // \App\Helpers\AlertHelper::notify('Ably publish failed', $e->getMessage());
        
        return false;
    }
}
```

## Alternative: Disable Real-time Features

If real-time updates are not critical for your use case, you can:

1. **Remove Ably dependency**:
```bash
# On local machine
composer remove ably/ably-php

# Commit and push
git add composer.json composer.lock
git commit -m "Remove Ably dependency"
git push origin deva
```

2. **Remove publish calls from controllers**:
   - Already wrapped in try-catch, so no errors will occur
   - Or comment out the try-catch blocks entirely

3. **Use polling instead of real-time updates**:
   - Frontend can poll API every 5-10 seconds
   - Simpler architecture, no external dependencies

This eliminates the VPS-specific issues entirely.

## Deployment Instructions

### Deploy the Fixes to VPS

The code changes have already been applied to your local repository. To deploy to VPS:

**Option 1: Automated Deployment (Recommended)**
```bash
# On local machine
git add app/Http/Controllers/BestTimeController.php
git add app/Helpers/AblyHelper.php
git add ABLY_DEBUG_GUIDE.md
git commit -m "Fix 500 error on VPS - Add non-blocking Ably publishing"
git push origin deva
```

GitHub Actions will automatically deploy the changes to your VPS.

**Option 2: Manual Deployment**
```bash
# SSH into VPS
ssh deva@<VPS_IP>

cd ~/apps/tamiya-laravel-app

# Pull latest code
git pull origin deva

# Install dependencies
docker run --rm -v $(pwd):/app -w /app composer:2 install --no-dev

# Rebuild Docker image
docker build -t tamiya-laravel-app:latest -f Dockerfile .

# Restart container
docker-compose --env-file .env.production down
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

# Clear caches
docker exec tamiya-laravel-app php artisan optimize:clear
docker exec tamiya-laravel-app php artisan config:cache
docker exec tamiya-laravel-app php artisan route:cache
docker exec tamiya-laravel-app php artisan view:cache
```

### Verify Deployment

```bash
# Check container status
docker ps -f name=tamiya-laravel-app

# Check health
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app

# Test health endpoint
curl https://race-lane.com/api/health

# Check logs
docker logs --tail 50 tamiya-laravel-app

# Test BTO update functionality
# Open browser: https://race-lane.com/tournament/tracks
# Try updating BTO for a track
# Should work without 500 error
```

## Docker-Specific Considerations

### Container Network Isolation

Your application runs in a Docker container, which has its own network namespace. This means:

1. **DNS Resolution**: Container uses Docker's DNS resolver
2. **Network Access**: Container must have outbound internet access
3. **SSL Certificates**: Container has its own CA certificate bundle

### Docker Network Configuration

Verify your container is properly connected to networks:

```bash
# Check container networks
docker network inspect web-network
docker network inspect db-network

# Check container IP
docker inspect tamiya-laravel-app | grep IPAddress

# Test internet connectivity from container
docker exec tamiya-laravel-app ping -c 3 8.8.8.8
docker exec tamiya-laravel-app curl -I https://www.google.com
```

### Docker Volume Mounts

Your application uses persistent volumes for storage:

```bash
# Check volume mounts
docker inspect tamiya-laravel-app | grep -A 10 Mounts

# Expected output:
# "Source": "/home/deva/apps/tamiya-laravel-app/storage"
# "Destination": "/var/www/html/storage"
```

This ensures:
- Storage data persists across container restarts
- Logs are accessible from host
- File uploads work correctly

### Docker Resource Limits

Check if container has sufficient resources:

```bash
# Check container stats
docker stats tamiya-laravel-app --no-stream

# Check memory limit
docker inspect tamiya-laravel-app | grep Memory

# Check CPU limit
docker inspect tamiya-laravel-app | grep NanoCpus
```

If resources are limited, consider:
```yaml
# Add to docker-compose.yml
services:
  tamiya-app:
    # ... existing config ...
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

### Docker Health Checks

Your container has a health check configured:

```dockerfile
# From Dockerfile
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1
```

Check health status:
```bash
docker inspect --format='{{.State.Health.Status}}' tamiya-laravel-app
# Should show: healthy

# Watch health status changes
watch -n 2 'docker inspect --format="{{.State.Health.Status}}" tamiya-laravel-app'
```

## Related Documentation

For more information about your Docker deployment:

- **[vps_docs/07-LARAVEL-DEPLOYMENT.md](vps_docs/07-LARAVEL-DEPLOYMENT.md)** - Complete Docker deployment guide
- **[vps_docs/MIGRATION-GUIDE.md](vps_docs/MIGRATION-GUIDE.md)** - Migration to new structure
- **[vps_docs/04-NGINX-PROXY-MANAGER.md](vps_docs/04-NGINX-PROXY-MANAGER.md)** - Nginx Proxy Manager setup

## Quick Reference Commands

### Container Management
```bash
docker ps -f name=tamiya-laravel-app                    # Status
docker logs -f tamiya-laravel-app                      # Real-time logs
docker logs --tail 100 tamiya-laravel-app              # Last 100 lines
docker restart tamiya-laravel-app                       # Restart
docker exec -it tamiya-laravel-app sh                  # Shell access
```

### Laravel Commands in Container
```bash
docker exec tamiya-laravel-app php artisan <cmd>         # Run artisan
docker exec tamiya-laravel-app composer <cmd>            # Run composer
docker exec tamiya-laravel-app php artisan tinker        # Interactive PHP
```

### Database Operations
```bash
docker exec -it mysql-server mysql -u tamiya_laravel_app -p tamiya_laravel
```

### Backup & Restore
```bash
~/scripts/backup-tamiya-db.sh                          # Manual backup
~/scripts/verify-tamiya.sh                             # Full verification
```

## Summary

### ✅ What's Been Fixed

1. **Non-blocking Ably publishing**: Added try-catch blocks in [`BestTimeController.php`](app/Http/Controllers/BestTimeController.php:183-192) and [`BestTimeController.php`](app/Http/Controllers/BestTimeController.php:333-341)
2. **Improved error handling**: Enhanced [`AblyHelper.php`](app/Helpers/AblyHelper.php:38-68) with timeout and better exception handling
3. **Comprehensive debugging guide**: Created this Docker-specific troubleshooting guide

### 🚀 Next Steps

1. **Deploy the fixes** to your VPS using automated or manual deployment
2. **Test BTO updates** at https://race-lane.com/tournament/tracks
3. **Monitor logs** for Ably warnings (non-blocking)
4. **Fix Ably configuration** if real-time updates are needed

### 📊 Expected Behavior

- **BTO updates work**: Data saves successfully without 500 errors
- **Ably warnings logged**: If Ably fails, warnings appear in logs but don't block operations
- **Real-time optional**: Application works with or without Ably

### 🔧 If Real-time Updates Are Critical

Follow the "Fix Ably Configuration" section to:
- Verify ABLY_KEY is correct
- Test network connectivity from container
- Check SSL/TLS certificates
- Test Ably connection manually

### 📝 If Real-time Updates Are Not Critical

The application will work perfectly without Ably:
- Data saves correctly
- All features function normally
- No errors or 500 status codes
- Simply ignore Ably warnings in logs

---

**Document Version**: 2.0 (Docker-aligned)
**Last Updated**: 2026-01-15
**Architecture**: Docker-based VPS deployment
