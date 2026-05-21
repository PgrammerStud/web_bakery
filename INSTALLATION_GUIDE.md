# Web Bakery - Installation & Setup Guide

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Project Structure](#project-structure)
3. [Installation Steps](#installation-steps)
4. [Environment Configuration](#environment-configuration)
5. [Database Setup](#database-setup)
6. [Running the Application](#running-the-application)
7. [Docker Setup](#docker-setup)
8. [Troubleshooting](#troubleshooting)
9. [Development Tools](#development-tools)

---

## Prerequisites

### System Requirements
- **OS:** Windows, macOS, or Linux
- **PHP:** >= 8.2
- **Node.js:** >= 18.0 (for asset compilation)
- **MySQL/MariaDB:** >= 8.0
- **Composer:** Latest version
- **Git:** For version control

### Optional Services
- **Stripe Account:** For payment processing
- **Firebase Account:** For notifications and authentication
- **Google OAuth:** For social login
- **Docker & Docker Compose:** For containerized setup

---

## Project Structure

```
Web_Bakery/
├── src/                          # Application source code
│   ├── Controller/              # Route controllers
│   ├── Entity/                  # Doctrine entities (database models)
│   ├── Repository/              # Database queries
│   ├── Service/                 # Business logic services
│   ├── Security/                # Authentication & security
│   ├── Form/                    # Form types
│   └── EventListener/           # Event listeners
├── config/                       # Application configuration
│   ├── packages/                # Symfony bundle configs
│   ├── services.yaml            # Service definitions
│   ├── routes.yaml              # Route imports
│   └── routes/                  # Route groups
├── templates/                    # Twig templates
├── public/                       # Web root (accessed by browser)
│   └── index.php                # Entry point
├── assets/                       # Frontend assets
│   ├── app.js                   # Main JavaScript
│   ├── styles/                  # CSS/SCSS
│   └── controllers/             # Stimulus JS controllers
├── migrations/                   # Database migrations
├── tests/                        # Unit & integration tests
├── docker-compose.yaml          # Docker configuration
├── Dockerfile                   # Container image definition
├── composer.json                # PHP dependencies
├── .env                         # Environment variables
└── bin/console                  # Symfony console commands
```

---

## Installation Steps

### Step 1: Clone the Repository

```bash
# Using HTTPS
git clone https://github.com/your-org/web-bakery.git
cd Web_Bakery

# Using SSH
git clone git@github.com:your-org/web-bakery.git
cd Web_Bakery
```

### Step 2: Install PHP Dependencies

```bash
# Install Composer dependencies
composer install

# For development with additional tools
composer install --dev
```

### Step 3: Install Node Dependencies

```bash
npm install
# or
yarn install
```

### Step 4: Create Environment Configuration

```bash
# Copy example environment file
cp .env.example .env

# Generate application secret
php bin/console secrets:generate-keys
```

### Step 5: Configure Environment Variables

Edit `.env` file with your settings:

```env
# Application
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=your_generated_secret_here

# Database
DATABASE_URL="mysql://user:password@127.0.0.1:3306/web_bakery?serverVersion=8.0&charset=utf8mb4"

# Stripe
STRIPE_API_KEY=sk_test_your_stripe_key
STRIPE_PUBLIC_KEY=pk_test_your_stripe_key

# Firebase
GOOGLE_APPLICATION_CREDENTIALS=config/firebase/serviceAccountKey.json

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_jwt_passphrase

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# Mercure (WebSocket)
MERCURE_URL=http://mercure:80/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET=your_mercure_secret

# Mailer
MAILER_DSN=smtp://username:password@smtp.mailtrap.io:465?encryption=tls

# Redis (optional)
REDIS_URL=redis://localhost:6379
```

### Step 6: Create & Setup Database

```bash
# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Load fixtures (optional - for development data)
php bin/console doctrine:fixtures:load
```

### Step 7: Generate JWT Keys

```bash
# Generate private key
openssl genrsa -out config/jwt/private.pem 4096

# Generate public key from private key
openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem

# Fix permissions
chmod 600 config/jwt/private.pem
chmod 644 config/jwt/public.pem
```

### Step 8: Compile Frontend Assets

```bash
# Development mode (with hot reload)
npm run dev

# Production mode (minified)
npm run build

# Using asset mapper (Symfony 7.4)
php bin/console asset-map:compile
```

### Step 9: Create First Admin User

```bash
php bin/console app:create-user --admin
# or manually
php bin/console doctrine:fixtures:load --group=admin
```

---

## Environment Configuration

### Development Environment (.env.local)

```env
APP_ENV=dev
APP_DEBUG=1
SYMFONY_DEPRECATIONS_HELPER=disabled

# Use SQLite for simple development
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# MySQL local development
DATABASE_URL="mysql://root:password@127.0.0.1:3306/web_bakery"

# Disable external services for testing
STRIPE_API_KEY=sk_test_disabled
```

### Production Environment

```env
APP_ENV=prod
APP_DEBUG=0
DATABASE_URL="mysql://prod_user:secure_password@prod-db.example.com/web_bakery"

# Use real API keys
STRIPE_API_KEY=sk_live_...
STRIPE_PUBLIC_KEY=pk_live_...

# Enable caching
APP_CACHE_DIR=/var/cache/web-bakery
```

---

## Database Setup

### Running Migrations

```bash
# Execute pending migrations
php bin/console doctrine:migrations:migrate

# Rollback last migration
php bin/console doctrine:migrations:migrate prev

# View migration status
php bin/console doctrine:migrations:status
```

### Creating New Migration

```bash
# Auto-generate migration from entity changes
php bin/console make:migration

# Name your migration
php bin/console make:migration --description="Add new fields to Product"
```

### Database Backup & Restore

```bash
# Backup database
mysqldump -u user -p web_bakery > backup.sql

# Restore database
mysql -u user -p web_bakery < backup.sql
```

### Useful Database Commands

```bash
# Drop and recreate entire database
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Load test fixtures
php bin/console doctrine:fixtures:load --env=test

# Clear Doctrine cache
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-result
php bin/console doctrine:cache:clear-query
```

---

## Running the Application

### Option 1: Symfony Development Server

```bash
# Start the built-in web server (fastest)
php -S localhost:8000 -t public

# Or using Symfony CLI
symfony serve

# With HTTPS
symfony serve -d  # Runs in background
symfony serve:stop  # Stop server

# Check server status
symfony serve:status
```

### Option 2: Apache/Nginx

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName web-bakery.local
    DocumentRoot /path/to/Web_Bakery/public

    <Directory /path/to/Web_Bakery/public>
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/web_bakery-error.log
    CustomLog ${APACHE_LOG_DIR}/web_bakery-access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name web-bakery.local;
    root /path/to/Web_Bakery/public;

    location / {
        try_files $uri @rewrite;
    }

    location @rewrite {
        rewrite ^(.*)$ /index.php$1 last;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

---

## Docker Setup

### Quick Start with Docker Compose

```bash
# Build and start all services
docker-compose up -d

# View running containers
docker-compose ps

# View logs
docker-compose logs -f app

# Stop services
docker-compose down
```

### Services Included

| Service | Port | Description |
|---------|------|-------------|
| app | 8000 | PHP/Symfony application |
| mysql | 3312 | MySQL database |
| phpmyadmin | 8085 | Database management UI |
| mercure | 3000 | WebSocket server |

### Access Services

- **Application:** http://localhost:8000
- **PHPMyAdmin:** http://localhost:8085
- **Mercure:** http://localhost:3000/.well-known/mercure

### Docker Commands

```bash
# Execute command in container
docker exec -it web_bakery_app php bin/console doctrine:migrations:migrate

# Access container shell
docker exec -it web_bakery_app bash

# View container logs
docker logs -f web_bakery_app

# Rebuild containers
docker-compose down --volumes
docker-compose up --build -d

# Remove all Docker data
docker system prune -a
```

### Building Custom Docker Image

```bash
# Build image
docker build -t web-bakery:latest .

# Run container
docker run -d \
  -p 8000:80 \
  -e APP_ENV=prod \
  -v /path/to/Web_Bakery:/app \
  web-bakery:latest

# Push to registry
docker tag web-bakery:latest your-registry/web-bakery:latest
docker push your-registry/web-bakery:latest
```

---

## Configuration Files

### Symfony Configuration

#### config/services.yaml
- Service definitions
- Dependency injection
- Service aliases

#### config/packages/security.yaml
- Authentication methods
- Firewall rules
- Access control
- Password hashers

#### config/packages/lexik_jwt_authentication.yaml
- JWT token configuration
- Key paths
- Token expiration

#### config/packages/stripe.yaml
- Stripe API configuration
- Payment settings

---

## Troubleshooting

### Issue: "Unable to connect to database"

```bash
# Check database credentials in .env
# Verify MySQL is running
sudo systemctl status mysql

# Test connection
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
```

### Issue: "JWT key files not found"

```bash
# Regenerate JWT keys
php bin/console secrets:generate-keys

# Or manually
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem
```

### Issue: "Cache permission denied"

```bash
# Fix cache directory permissions
chmod -R 777 var/cache
chmod -R 777 var/log

# Or use proper permissions
chmod -R 755 var
chmod -R 777 var/cache var/log
```

### Issue: "Assets not loading"

```bash
# Recompile assets
npm run build
php bin/console asset-map:compile

# Clear cache
php bin/console cache:clear
php bin/console cache:warmup
```

### Issue: "Migrations failed"

```bash
# Check migration status
php bin/console doctrine:migrations:status

# Migrate from specific version
php bin/console doctrine:migrations:execute Version20240521000000

# Mark migration as executed without running
php bin/console doctrine:migrations:version Version20240521000000 --add
```

### Issue: Composer "memory exhausted"

```bash
# Increase PHP memory limit
php -d memory_limit=-1 bin/composer.phar install

# Or set in php.ini
memory_limit = 2G
```

---

## Development Tools

### Useful Console Commands

```bash
# List all routes
php bin/console debug:router

# Show specific route
php bin/console debug:router app_product_index

# List services
php bin/console debug:container

# Check configuration
php bin/console config:dump framework

# Clear all caches
php bin/console cache:clear

# Generate entities from database
php bin/console doctrine:mapping:import App\\Entity annotation

# Make new entity
php bin/console make:entity

# Make new controller
php bin/console make:controller

# Make form type
php bin/console make:form

# Run tests
php bin/phpunit

# Code style check
vendor/bin/phpstan analyse src

# Code style fix
vendor/bin/php-cs-fixer fix src
```

### Debugging

#### Using Symfony Profiler

```bash
# Access profiler at
http://localhost:8000/_profiler

# View specific request
http://localhost:8000/_profiler/latest
```

#### Using Var Dump

```php
// In controller
dump($variable);
dd($variable);  // dump and die

// Will appear in web profiler
```

#### Using Monolog (Logging)

```php
$logger = $this->container->get('logger');
$logger->info('User logged in', ['userId' => $user->getId()]);
$logger->error('Payment failed', ['orderId' => $order->getId()]);

// View logs at
var/log/dev.log
var/log/prod.log
```

### PHPUnit Testing

```bash
# Run all tests
php bin/phpunit

# Run specific test file
php bin/phpunit tests/Controller/ProductControllerTest.php

# Run specific test method
php bin/phpunit --filter testGetProducts

# Generate code coverage
php bin/phpunit --coverage-html coverage/
```

---

## Performance Optimization

### Caching

```bash
# Enable Redis for caching
# Add to .env
REDIS_URL=redis://localhost:6379

# Clear all caches
php bin/console cache:clear
```

### Database Optimization

```bash
# Create indexes
ALTER TABLE products ADD INDEX idx_category (category_id);
ALTER TABLE orders ADD INDEX idx_customer (customer_id);
ALTER TABLE order_items ADD INDEX idx_order (order_id);

# Analyze tables
ANALYZE TABLE products;
ANALYZE TABLE orders;
```

### Asset Optimization

```bash
# Minify CSS/JS
npm run build

# Optimize images
find public -name "*.jpg" -o -name "*.png" | xargs optipng -o5
```

---

## Production Deployment

### Pre-deployment Checklist

- [ ] Set `APP_ENV=prod`
- [ ] Set `APP_DEBUG=0`
- [ ] Run migrations
- [ ] Clear caches
- [ ] Build assets
- [ ] Update environment variables
- [ ] Setup SSL certificates
- [ ] Configure backup strategy
- [ ] Setup monitoring

### Deploy Commands

```bash
# Build production assets
npm run build

# Install production dependencies only
composer install --no-dev --optimize-autoloader

# Clear cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Run migrations
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
```

### Server Configuration

```bash
# Set proper file permissions
sudo chown -R www-data:www-data /path/to/Web_Bakery
sudo chmod -R 755 /path/to/Web_Bakery
sudo chmod -R 777 /path/to/Web_Bakery/var

# Enable PHP opcache
# Edit php.ini
opcache.enable=1
opcache.memory_consumption=128
```

---

## Support & Resources

- **Symfony Documentation:** https://symfony.com/doc/current/index.html
- **Doctrine ORM:** https://www.doctrine-project.org/
- **API Platform:** https://api-platform.com/
- **Stripe API:** https://stripe.com/docs/api
- **Firebase:** https://firebase.google.com/docs

---

## FAQ

**Q: How do I reset the database?**
```bash
php bin/console doctrine:database:drop --force && \
php bin/console doctrine:database:create && \
php bin/console doctrine:migrations:migrate
```

**Q: How do I add a new user?**
```bash
php bin/console make:user
```

**Q: Can I use SQLite for development?**
Yes! Set `DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"`

**Q: How do I enable HTTPS locally?**
```bash
symfony serve --cert=localcerts
```

**Q: What's the default admin username?**
Check `DataFixtures` or create with `app:create-user --admin`

---

Last Updated: May 21, 2024
