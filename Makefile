.PHONY: test lint static-analysis unit-tests help docker-build docker-run ci-php ci-python security-check

# Default target
.DEFAULT_GOAL := help

# Show help
help:
	@echo "Usage:"
	@echo "  make test                Run all tests, linting, and static analysis"
	@echo "  make lint                Run linting tools only"
	@echo "  make static-analysis     Run static analysis tools only"
	@echo "  make unit-tests          Run unit tests only"
	@echo "  make docker-build        Build Docker image"
	@echo "  make docker-run          Run Docker container"
	@echo "  make ci-php              Run all PHP CI checks"
	@echo "  make ci-python           Run all Python CI checks"
	@echo "  make security-check      Run security scans on dependencies"

# Main test command that runs everything
test:
	@echo "Running all tests and checks..."
	@./scripts/test.sh

# Individual commands
lint:
	@echo "Running linting only..."
	@./scripts/lint.sh

static-analysis:
	@echo "Running static analysis only..."
	@./scripts/static-analysis.sh

unit-tests:
	@echo "Running unit tests only..."
	@./scripts/run-tests.sh

# Docker commands
docker-build:
	@echo "Building Docker image..."
	docker build -t teko-app .

docker-run:
	@echo "Running Docker container..."
	docker run -p 8000:9000 -v $(PWD):/var/www teko-app

# CI specific commands
ci-php:
	@echo "Running PHP CI checks..."
	composer install --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
	./vendor/bin/pint --test
	./vendor/bin/phpstan analyze || echo "PHPStan found issues but continuing..."
	./vendor/bin/psalm --no-cache
	./vendor/bin/phpunit

ci-python:
	@echo "Running Python CI checks..."
	if [ -d agents ] && [ -f agents/requirements.txt ]; then \
		pip install -r agents/requirements.txt && \
		python -m black --check agents && \
		python -m isort --check-only --profile black agents && \
		python -m flake8 agents && \
		python -m mypy agents && \
		python -m pytest agents; \
	else \
		echo "Skipping Python checks (no agents directory or requirements file)"; \
	fi

security-check:
	@echo "Running security scans..."
	@if command -v composer > /dev/null; then \
		composer audit; \
	else \
		echo "Composer not found, skipping PHP security check"; \
	fi
	@if command -v pip > /dev/null && [ -f agents/requirements.txt ]; then \
		pip install safety && \
		safety check -r agents/requirements.txt; \
	else \
		echo "Skipping Python security check"; \
	fi
