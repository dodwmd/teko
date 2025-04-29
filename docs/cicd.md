# CI/CD Pipeline

Teko uses GitHub Actions for Continuous Integration and Continuous Deployment, ensuring code quality and streamlined deployment.

## Overview

The CI/CD pipeline for Teko is designed to handle both PHP (Laravel) and Python (Agents) components, with specialized workflows for each.

## Workflow Components

### Main CI Pipeline

The main workflow (`main.yml`) runs on every push and pull request to main branches and performs:

- **PHP Tests**: Linting, static analysis, and unit tests for Laravel components
- **Python Tests**: Linting, static analysis, and unit tests for agent components

In addition to running tests, the CI/CD pipeline also:

1. Syncs documentation to the wiki (via the `wiki-sync.yml` workflow)
2. Applies repository configuration settings (via the `repo-config.yml` workflow)

This ensures that both code quality and repository configuration remain consistent.

### Security and Deployment Pipeline 

The security workflow (`security-deploy.yml`) runs on merges to main branches and weekly, providing:

- **Security Scans**: Checks dependencies for vulnerabilities
- **Deployment**: Automates deployment to staging environments

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

## Local Development

You can run the same checks locally using the Makefile:

```bash
# Run all tests
make test

# Run specific checks
make lint
make static-analysis
make unit-tests

# Run security checks
make security-check
```

## Docker Support

The pipeline also builds and tests Docker containers:

```bash
# Build image locally
make docker-build

# Run container
make docker-run
```

## Wiki Synchronization

A special workflow (`wiki-sync.yml`) automatically synchronizes the `docs/` directory with the GitHub wiki whenever documentation changes are made, ensuring documentation remains up-to-date.

## Deployment Strategy

The deployment process follows these steps:

1. Tests and security checks pass
2. Docker image is built with the current commit
3. Image is deployed to staging environment
4. For production, manual approval is required

## Configuration

All workflow files can be found in the `.github/workflows/` directory.
