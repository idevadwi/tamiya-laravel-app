<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Tamiya Laravel Race Management Application

A comprehensive race management system for Tamiya mini 4WD competitions with real-time timing, tournament management, and multi-language support.

## Features

- Tournament management with multi-language support (English, Indonesian, Japanese)
- Real-time race timing with Ably integration
- Racer registration and management
- Race results tracking and leaderboards
- Best times tracking per tournament
- Role-based access control (Administrator, Moderator, User)
- Responsive AdminLTE interface

## Tech Stack

### Backend
- **Laravel 11** - PHP framework
- **PHP 8.3** - Server-side language
- **MySQL 8.0** - Database
- **Laravel Sanctum** - API authentication

### Frontend
- **Vue 3** - Progressive JavaScript framework
- **Vite** - Fast build tool and dev server
- **Tailwind CSS** - Utility-first CSS framework
- **AdminLTE** - Admin dashboard template

### Infrastructure
- **Docker** - Containerization
- **Nginx** - Web server
- **Supervisor** - Process manager
- **Ably** - Real-time messaging

---

## Local Development Setup

### Prerequisites
- PHP 8.3+
- Node.js 18+ (recommended: use Node.js 20+ for best compatibility)
- Composer
- MySQL 8.0+

### Installation

1. Clone the repository
   ```bash
   git clone https://github.com/YOUR_USERNAME/tamiya-laravel-app.git
   cd tamiya-laravel-app
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install JavaScript dependencies
   ```bash
   npm install
   ```

4. Copy environment file
   ```bash
   cp .env.example .env
   ```

5. Generate application key
   ```bash
   php artisan key:generate
   ```

6. Configure database in `.env`
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tamiya_laravel
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

7. Run migrations and seeders
   ```bash
   php artisan migrate --seed
   ```

8. Create storage symlink
   ```bash
   php artisan storage:link
   ```

### Development Servers

Start both Laravel and Vite dev servers:
```bash
npm run dev:all
```

Or start them separately:
```bash
# Laravel only (port 8080)
php artisan serve --port=8080

# Vite only
npm run dev
```

### Vue Components

Vue components are located in `resources/js/components/`:
- `ExampleComponent.vue` - Basic Vue component example
- `RaceTimer.vue` - Tamiya race timing component with start/stop/reset functionality

Components are automatically registered in `resources/js/app.js` and can be used in Blade templates.

---

## Production Deployment (Docker)

The application is deployed to production using Docker containers with automated CI/CD via GitHub Actions.

### Quick Deploy

Push to the `deva` branch to trigger automated deployment:
```bash
git push origin deva
```

GitHub Actions will automatically:
1. Build Docker image
2. Deploy to VPS
3. Run migrations
4. Optimize Laravel

### Manual Deployment

On VPS:
```bash
cd ~/apps/tamiya-laravel-app
git pull origin deva
docker build -t tamiya-laravel-app:latest .
docker-compose --env-file .env.production up -d
docker exec tamiya-laravel-app php artisan migrate --force
docker exec tamiya-laravel-app php artisan optimize
```

### Docker Files

- [Dockerfile](Dockerfile) - Container image definition
- [docker-compose.yml](docker-compose.yml) - Service orchestration
- [nginx-app.conf](nginx-app.conf) - Nginx web server configuration
- [supervisord.conf](supervisord.conf) - Process manager configuration

### Documentation

- [Deployment Structure](DEPLOYMENT-STRUCTURE.md) - Complete deployment architecture
- [Migration Guide](vps_docs/MIGRATION-GUIDE.md) - Migrate from old deployment structure
- [VPS Setup Guide](vps_docs/07-LARAVEL-DEPLOYMENT.md) - Full VPS deployment walkthrough

### Health Check

The application includes a health check endpoint:
```bash
curl http://localhost/api/health
```

Response:
```json
{
  "status": "healthy",
  "timestamp": "2026-01-14T10:30:00+00:00",
  "database": "connected"
}
```

---

## Project Structure

```
tamiya-laravel-app/
├── app/                     # Laravel application
├── bootstrap/
├── config/
├── database/
│   ├── migrations/          # Database schema
│   └── seeders/             # Database seeds
├── lang/                    # Translations (en, id, ja)
├── public/                  # Public assets
├── resources/
│   ├── js/                  # Vue components
│   └── views/               # Blade templates
├── routes/                  # API and web routes
├── storage/                 # Logs and uploads
├── tests/
├── vps_docs/                # VPS deployment guides
├── Dockerfile               # Docker image
├── docker-compose.yml       # Docker orchestration
├── nginx-app.conf          # Nginx config
└── supervisord.conf        # Process manager
```

---

## API Endpoints

### Races
- `POST /api/races` - Create new race with card code
- `GET /api/races/{id}` - Get race details
- `GET /api/races/best-times/{tournament}` - Get best times

### Tournaments
- `GET /api/tournaments` - List tournaments
- `POST /api/tournaments` - Create tournament (auth required)
- `GET /api/tournaments/{slug}` - Get tournament by slug

---

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects.

Laravel is accessible, powerful, and provides tools required for large, robust applications. Learn more at [laravel.com](https://laravel.com).

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Support

For deployment issues, see [DEPLOYMENT-STRUCTURE.md](DEPLOYMENT-STRUCTURE.md) or the [vps_docs/](vps_docs/) directory.
