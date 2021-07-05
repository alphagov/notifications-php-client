.DEFAULT_GOAL := help
SHELL := /bin/bash

.PHONY: help
help:
	@cat $(MAKEFILE_LIST) | grep -E '^[a-zA-Z_-]+:.*?## .*$$' | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: bootstrap
bootstrap:  ## Install build dependencies
	/usr/local/bin/composer update

.PHONY: build
build: bootstrap ## Build project (dummy task for CI)

.PHONY: test
test: ## Run tests
	vendor/bin/phpspec run spec/unit/ --format=pretty --verbose

.PHONY: integration-test
integration-test: ## Run integration tests
	vendor/bin/phpspec run spec/integration/ --format=pretty --verbose

.PHONY: get-client-version
get-client-version: ## Retrieve client version number from source code
	@php -r "include 'src/Client.php'; echo \\Alphagov\\Notifications\\Client::VERSION;"

.PHONY: bootstrap-with-docker
bootstrap-with-docker: ## Prepare the Docker builder image
	docker build -t notifications-php-client .
	./scripts/run_with_docker.sh make bootstrap

.PHONY: test-with-docker
test-with-docker: ## Run tests inside a Docker container
	./scripts/run_with_docker.sh make test

.PHONY: integration-test-with-docker
integration-test-with-docker: ## Run integration tests inside a Docker container
	./scripts/run_with_docker.sh make integration-test

clean:
	rm -rf .cache venv
