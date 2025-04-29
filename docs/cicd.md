# CI/CD Pipeline

Teko uses GitHub Actions for Continuous Integration and Continuous Deployment, ensuring code quality and streamlined deployment.

## Overview

The CI/CD pipeline for Teko is designed to handle both PHP (Laravel) and Python (Agents) components, with specialized workflows for each. It follows a trunk-based development approach with the main branch as the source of truth.

## Workflow Components

### Main CI Pipeline

The main workflow (`main.yml`) runs on every push and pull request to main branches and performs:

- **PHP Tests**: Linting, static analysis, and unit tests for Laravel components
- **Python Tests**: Linting, static analysis, and unit tests for agent components
- **Docker Build**: Builds and pushes container images to GitHub Container Registry
- **Release Creation**: Automatically creates GitHub releases

In addition to running tests, the CI/CD pipeline also:

1. Syncs documentation to the wiki (via the `wiki-sync.yml` workflow)
2. Applies repository configuration settings (via the `repo-config.yml` workflow)

This ensures that both code quality and repository configuration remain consistent.

### Security Pipeline 

The security workflow (`security.yml`) runs on merges to main branches and weekly, providing:

- **Security Scans**: Checks dependencies for vulnerabilities

## Testing Components

### PHP Testing

PHP code is tested with multiple tools:

1. **Laravel Pint**: Code style enforcement
2. **PHPStan**: Static analysis for PHP
3. **Psalm**: Type checking and additional static analysis
4. **PHPUnit**: Unit and feature tests

### Python Testing

Python agents are tested with:

1. **Black/isort**: Code formatting
2. **Flake8**: Style guide enforcement
3. **MyPy**: Type checking
4. **pytest**: Unit testing

## Environment Setup

The pipeline automatically sets up appropriate environments:

- **PHP 8.2** with required extensions
- **Python 3.10** with LangChain dependencies
- **MySQL** database for integration tests

## Docker Container Building

After successful tests, the CI pipeline automatically:

1. Builds Docker containers for the application
2. Tags with both `latest` and a version-specific tag (`YYYYMMDD-commit` or the git tag)
3. Pushes containers to GitHub Container Registry (ghcr.io)

### Container Naming Convention

Containers follow this naming structure:
```
ghcr.io/owner/teko:tag
```

Where:
- `owner` is the GitHub repository owner
- `tag` is either `latest` or a version identifier

## GitHub Release Management

The CI/CD pipeline creates GitHub releases automatically when:

1. Tests pass successfully
2. The commit is to the main/master branch

Releases include:
- Automatically generated release notes
- Version tag based on date and commit or git tag
- Links to the Docker container images

## Local Development

You can run the same tests locally using the Make commands:

```bash
# Run PHP tests
make test-php

# Run Python tests
make test-python

# Run all tests
make test

# Build Docker container locally
make docker-build
```

For more information on working with Docker containers, see the [Deployment Guide](deployment.md).
