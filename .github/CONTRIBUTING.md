# Contributing to Teko

Thank you for considering contributing to Teko! This document outlines the process for contributing to the project and how to best communicate with the team.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

- **Check if the bug has already been reported** by searching on GitHub under [Issues](https://github.com/your-organization/teko/issues).
- If you're unable to find an open issue addressing the problem, [open a new one](https://github.com/your-organization/teko/issues/new/choose). Be sure to include a **title and clear description**, as much relevant information as possible, and a **code sample** or an **executable test case** demonstrating the expected behavior that is not occurring.

### Suggesting Enhancements

- **Check if the enhancement has already been suggested** by searching on GitHub under [Issues](https://github.com/your-organization/teko/issues).
- If you're unable to find an open issue regarding the enhancement, [open a new one](https://github.com/your-organization/teko/issues/new/choose). Be sure to include a **title and clear description**, as much relevant information as possible, and a **code sample** or a **conceptual example** demonstrating the enhancement.

### Pull Requests

1. **Fork the repository** and create your branch from `main` or `develop`.
2. **Install dependencies** for both PHP and Python components.
3. **Make your changes**.
4. **Run tests** using `make test` to ensure your changes do not break existing functionality.
5. **Update the documentation** if your changes affect user-visible features.
6. **Submit a pull request**.

## Development Setup

### PHP Components

```bash
# Clone the repository
git clone https://github.com/your-organization/teko.git
cd teko

# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Start the Laravel server
php artisan serve
```

### Python Components

```bash
# Install Python dependencies
pip install -r agents/requirements.txt

# Run Python tests
cd agents
pytest
```

## Testing

We use a combination of testing tools for both PHP and Python components:

### PHP Testing
- PHPUnit for unit and feature tests
- PHPStan for static analysis
- Psalm for type checking
- Laravel Pint for code style

### Python Testing
- pytest for unit tests
- mypy for type checking
- flake8 for linting
- black and isort for code formatting

Run all tests with:

```bash
make test
```

## Style Guides

### Git Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

### PHP Style Guide

We follow the PSR-12 coding standard and the PSR-4 autoloading standard. Laravel Pint is configured to enforce these standards.

### Python Style Guide

We follow PEP 8 with some exceptions defined in our Black configuration.

## License

By contributing to Teko, you agree that your contributions will be licensed under the project's license.

Thank you for contributing to Teko!
