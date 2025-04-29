# Teko Architecture

Teko follows a modular architecture designed to support multiple language environments and specialized AI agents.

## System Overview

![Teko Architecture](https://via.placeholder.com/800x400?text=Teko+Architecture+Diagram)

Teko consists of several key components:

1. **Web Interface**: Laravel-based UI for agent management
2. **Agent System**: Python-based agents using LangChain
3. **Database Layer**: MySQL storage for repositories, tasks, and agent data
4. **Authentication System**: OAuth and role-based access control
5. **Containerization**: Docker-based deployment with GitHub Container Registry

## Component Architecture

### Web Interface

The web interface is built with Laravel and the Orchid admin panel, providing:

- Dashboard for monitoring agent activities
- Repository management
- Task management interface 
- User authentication and access control
- Error reporting and handling
- Comment system that syncs with original issues (Jira/GitHub)
- Google Analytics integration for usage tracking

### Agent Architecture

The agent system follows a modular design:

- **Base Agent**: Core functionality for all agents
- **Language-specific Agents**: Specialized handlers for different programming languages (PHP, Python, etc.)
- **Codebase Analysis Agent**: Identifies languages and adds metadata to memory
- **Scheduler Agent**: Dispatches appropriate agents based on codebase type

Each agent communicates with the Laravel backend through a standardized API.

#### Agent Types

1. **Code Implementation Agent**: 
   - Pulls tasks from Jira/GitHub
   - Implements code according to specifications
   - Creates pull requests
   - Runs tests before submission

2. **Code Review Agent**:
   - Reviews codebase for issues and improvement opportunities
   - Raises issues and feature requests
   - Suggests code optimizations

3. **Story Management Agent**:
   - Sizes stories based on complexity
   - Rewrites or breaks up stories for better implementation
   - Prioritizes work
   - Groups related tasks

### Repository Layer

The data access layer follows the Repository pattern:

- **Repository Interface**: Defines standard data access methods
- **Concrete Repositories**: Implementations for specific data models
- **Eloquent Models**: ORM models for database tables

### Memory System

Teko stores development knowledge in:

- **Vector Database**: ChromaDB for codebase insights and semantic search
- **Relational Database**: MySQL for structured data like tasks and repositories
- **LangChain Memory**: For conversation history and context

### Container Architecture

The containerized deployment architecture includes:

- **Application Container**: PHP-FPM with Laravel and Python agents
- **Web Server**: Nginx for request handling
- **Database**: MySQL for persistent storage
- **Queue System**: Laravel Horizon for job processing

The container is built with:
- PHP 8.2+ environment
- Python 3.10+ with virtual environment
- All required system dependencies
- Supervisor for process management

## Data Flow

1. **Task Creation**: Tasks enter the system from Jira/GitHub or manual creation
2. **Analysis**: Codebase Analysis Agent identifies the language and requirements
3. **Scheduling**: Scheduler Agent assigns the task to appropriate Language Agent
4. **Execution**: Language Agent performs the task (implementation, review, etc.)
5. **Feedback**: Results are stored in the database and presented in the UI

## Security Architecture

- **API Key Management**: Secure storage of API keys in environment variables
- **Authentication**: OAuth via Socialite (Google/GitHub)
- **Authorization**: Role-based access control with Entrust
- **Agent Sandboxing**: Isolation of agent operations
- **Regular Security Audits**: Automated dependency scanning

## Integration Points

- **GitHub/Jira**: For task sourcing and PR creation
- **Communication Channels**: Status updates to Slack/Telegram
- **CI/CD Integration**: For testing code changes before submission
- **Docker Registry**: GitHub Container Registry for image distribution

## Deployment Architecture

Teko follows a container-based deployment model:

1. **CI/CD Pipeline**: Automated testing and container building
2. **Container Registry**: Central storage for versioned containers
3. **Deployment Targets**: Staging and production environments
4. **Scaling**: Horizontal scaling through container orchestration

For more information on deployment, see the [Deployment Guide](deployment.md).
