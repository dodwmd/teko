# Installation Guide

This guide will help you set up Teko on your development environment.

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 16+ & NPM
- Python 3.10+
- Docker & Docker Compose
- MySQL 8.0+

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/your-username/teko.git
cd teko
```

### 2. Set Up PHP Environment

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate
```

### 3. Configure Database

```bash
# Start MySQL container
docker compose up -d

# Update .env file with database credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=teko
# DB_USERNAME=teko
# DB_PASSWORD=root

# Run migrations
php artisan migrate
```

### 4. Set Up Python Agent Environment

```bash
# Set up Python virtual environment
make python-setup
```

### 5. Front-end Assets

```bash
npm install
npm run build
```

### 6. Start Development Server

```bash
php artisan serve
```

Visit http://localhost:8000 in your browser.

## Using direnv for Automatic Setup

If you have direnv installed, simply navigate to the project directory, and your environment will be set up automatically:

```bash
cd teko
direnv allow
```

See [direnv setup](direnv-setup.md) for more details.

## Production Deployment

For production deployment, additional steps are recommended:

1. Set proper environment variables
2. Run optimizations: `php artisan optimize`
3. Configure secure web server (Nginx/Apache)
4. Set up a proper queue worker
5. Configure a task scheduler

See the [deployment documentation](deployment.md) for more details.
