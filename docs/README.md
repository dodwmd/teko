# Teko

Teko (Task Engine KO) is an AI-powered development automation system built using Laravel and LangChain. It provides autonomous agents for various coding tasks, code reviews, and project management.

## About Teko

Teko is designed to automate development tasks through a series of specialized AI agents. It offers:

- **Code Implementation Agent**: Pulls tasks from Jira/GitHub, implements code, creates PRs, and runs tests
- **Code Review Agent**: Reviews codebase, raises issues and feature requests
- **Story Management Agent**: Sizes stories, rewrites/breaks up stories, prioritizes work, and groups related tasks

## Documentation

* [Installation Guide](Installation.md)
* [Developer Setup](developer-setup.md)
* [Architecture Overview](architecture.md)
* [Python Agent System](agent-system.md)
* [Testing & Quality](testing-quality.md)
* [Environment Setup with Direnv](direnv-setup.md)
* [Database Schema](database-schema.md)
* [CI/CD Pipeline](cicd.md)
* [Contributing Guidelines](../CONTRIBUTING.md)
* [Security Information](../SECURITY.md)

## Technical Stack

### Backend
- Laravel 10+ (PHP 8.2+)
- LangChain (Python 3.10+)
- MySQL Database

### Frontend
- Orchid Admin Panel
- Laravel Blade templates

### Authentication
- Socialite (Google/GitHub authentication)
- Entrust (role-based permissions)

### Testing & Quality
- PHP: PHPUnit, PHPStan, Psalm, Laravel Pint
- Python: pytest, Black, MyPy, Flake8, isort

## License

[MIT License](../LICENSE)
