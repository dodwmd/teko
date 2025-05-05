.PHONY: test lint python-lint static-analysis python-static-analysis unit-tests python-tests test-python test-php help docker-build docker-run ci-php ci-python security-check python-setup python-run

# Default target
.DEFAULT_GOAL := help

# Show help
help:
	@echo "Usage:"
	@echo "  make test                Run all tests, linting, and static analysis"
	@echo "  make lint                Run PHP linting only"
	@echo "  make python-lint         Run Python linting only"
	@echo "  make static-analysis     Run PHP static analysis only"
	@echo "  make python-static-analysis Run Python static analysis only"
	@echo "  make unit-tests          Run PHP unit tests only"
	@echo "  make python-tests        Run Python tests only"
	@echo "  make test-python         Run all Python tests"
	@echo "  make test-php            Run all PHP tests"
	@echo "  make docker-build        Build Docker image"
	@echo "  make docker-run          Run Docker container"
	@echo "  make ci-php              Run all PHP CI checks"
	@echo "  make ci-python           Run all Python CI checks"
	@echo "  make security-check      Run security scans on dependencies"
	@echo "  make python-setup        Set up Python virtual environment"
	@echo "  make python-run          Run Python agent (example: make python-run AGENT=core/orchestrator.py)"

# Individual commands
lint:
	@echo "Running PHP linting..."
	@./scripts/lint.sh

python-lint:
	@echo "Running Python linting..."
	@./scripts/python-lint.sh

static-analysis:
	@echo "Running PHP static analysis..."
	@./scripts/static-analysis.sh

python-static-analysis:
	@echo "Running Python static analysis..."
	@./scripts/python-static-analysis.sh

unit-tests:
	@echo "Running PHP unit tests..."
	@./scripts/run-tests.sh

python-tests:
	@echo "Running Python tests..."
	@./scripts/python-tests.sh

test-python: python-lint python-static-analysis python-tests
	@echo "All Python tests completed"

test-php: lint static-analysis unit-tests
	@echo "All PHP tests completed"

# Main test command that runs everything
test: test-php test-python
	@echo "All tests completed"

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
		./scripts/python-lint.sh && \
		./scripts/python-static-analysis.sh && \
		./scripts/python-tests.sh; \
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

# Python virtual environment commands
python-setup:
	@echo "Setting up Python virtual environment..."
	@./scripts/setup-python-env.sh

python-run:
	@if [ -z "$(AGENT)" ]; then \
		echo "Error: AGENT parameter is required. Example: make python-run AGENT=core/orchestrator.py"; \
		exit 1; \
	fi
	@echo "Running Python agent: $(AGENT)"
	@./scripts/run-agent.sh $(AGENT)
