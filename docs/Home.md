# Teko

Teko (Task Engine KO) is an AI-powered development automation system built using Laravel and LangChain. It provides autonomous agents for various coding tasks, code reviews, and project management.

## About Teko

Teko is designed to automate development tasks through a series of specialized AI agents. It offers:

- **Code Implementation Agent**: Pulls tasks from Jira/GitHub, implements code, creates PRs, and runs tests
- **Code Review Agent**: Reviews codebase, raises issues and feature requests
- **Story Management Agent**: Sizes stories, rewrites/breaks up stories, prioritizes work, and groups related tasks

## Documentation

* [Installation Guide](Installation.md)
* [Architecture Overview](architecture.md)
* [CI/CD Pipeline](cicd.md)
* [Repository Configuration](repo-config.md)
* [Environment Setup with Direnv](direnv-setup.md)
* [Security Information](https://github.com/dodwmd/teko/blob/master/SECURITY.md)
* [Contributing Guidelines](https://github.com/dodwmd/teko/blob/master/.github/CONTRIBUTING.md)

## Technical Stack

### Backend
- Laravel 10+ (PHP 8.2+)
- LangChain (Python 3.10+)
- MySQL Database
- ChromaDB for vector storage

### Frontend
- Orchid Admin Panel
- Tailwind CSS
- Alpine.js

### Infrastructure
- GitHub Actions for CI/CD
- Docker containers
- Trunk-based development workflow

## Getting Started

See the [Installation Guide](Installation.md) for instructions on setting up the project locally.

## Project Status

This project is currently in the initial development phase with focus on establishing the core architecture, agent system, and admin interfaces.
