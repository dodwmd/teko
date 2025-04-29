# Teko Deployment Guide

This guide provides information on deploying the Teko AI agent system using Docker containers and managing releases.

## Docker Deployment

Teko uses Docker to create reproducible, portable environments that contain the complete application stack with all dependencies.

### Container Architecture

The Teko container includes:

- PHP 8.2+ with Laravel framework
- Python 3.10+ with LangChain dependencies
- Supervisor for process management
- MySQL client libraries

### Container Registry

Teko Docker images are stored in GitHub Container Registry (GHCR):

```
ghcr.io/[owner]/teko:[tag]
```

Where:
- `[owner]` is the GitHub repository owner
- `[tag]` is either `latest` or a version identifier (YYYYMMDD-commit)

### Pulling the Container

To pull the latest container:

```bash
docker pull ghcr.io/[owner]/teko:latest
```

Or pull a specific version:

```bash
docker pull ghcr.io/[owner]/teko:20250429-a1b2c3d
```

### Container Environment Variables

The Teko container requires these environment variables:

```
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=teko
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# API Keys
OPENAI_API_KEY=your-openai-key

# Application Settings
APP_URL=https://your-teko-instance.com
APP_ENV=production
APP_DEBUG=false
```

### Running the Container

Basic run command:

```bash
docker run -d --name teko \
  -p 8080:9000 \
  --env-file .env \
  ghcr.io/[owner]/teko:latest
```

With Docker Compose:

```yaml
version: '3'
services:
  teko:
    image: ghcr.io/[owner]/teko:latest
    ports:
      - "8080:9000"
    env_file:
      - .env
    depends_on:
      - mysql
  
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: your-root-password
      MYSQL_DATABASE: teko
      MYSQL_USER: teko
      MYSQL_PASSWORD: your-password
    volumes:
      - mysql-data:/var/lib/mysql

volumes:
  mysql-data:
```

## Release Management

Teko uses GitHub Releases for versioning, which are automatically created by the CI/CD pipeline.

### Release Naming Convention

Releases follow this naming pattern:
- Git tags: `v1.0.0`, `v1.1.0`, etc.
- Auto-generated: `vYYYYMMDD-commit` (e.g., `v20250429-a1b2c3d`)

### Deployment Stages

#### Development

For local development:

```bash
# Run with local PHP & MySQL
php artisan serve

# Run with Docker
make docker-build
make docker-run
```

#### Staging

Staging environments can be configured to automatically deploy the latest container from GHCR when a new release is created.

#### Production

Production deployments should always use a specific version tag rather than `latest`:

```bash
docker pull ghcr.io/[owner]/teko:v1.0.0
```

## Rollback Procedure

To rollback to a previous version:

1. Identify the previous working release tag
2. Pull and deploy that specific container:

```bash
docker pull ghcr.io/[owner]/teko:v0.9.0
docker stop teko
docker rm teko
docker run -d --name teko -p 8080:9000 --env-file .env ghcr.io/[owner]/teko:v0.9.0
```

## Health Checks

The container provides health check endpoints:

- `/api/health` - Basic application health
- `/api/health/db` - Database connectivity
- `/api/health/agents` - AI agent system status

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [GitHub Container Registry Documentation](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
