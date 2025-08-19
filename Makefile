# Variables for Docker labels
VERSION := $(shell git describe --tags --always 2>/dev/null || echo "v0.0.0")
GIT_COMMIT := $(shell git rev-parse HEAD 2>/dev/null || echo "unknown")
BUILD_DATE := $(shell date -u +'%Y-%m-%dT%H:%M:%SZ')
ECR_REGISTRY := ghcr.io
SERVICE_NAME := api
PROJECT_NAME := t-esp-asstagj

LAST_COMMIT := $(shell git log -1 --oneline --pretty=format:"%h - %an, %ar")
BOLD_GREEN := \033[1;32m
NC := \033[0m

install:         ## Install dependencies
	@echo -e "\r\n${BOLD_GREEN}# Installing dependencies${NC}\r\n"
	@docker compose build

start:           ## Start the API
	@echo -e "\r\n${BOLD_GREEN}# Starting API${NC}\r\n"
	@docker compose --env-file .env.local up

stop:            ## Stop the API
	@echo -e "\r\n${BOLD_GREEN}# Stopping API${NC}\r\n"
	@docker compose down

##
## Docker Build & Push
##---------------------------------------------------------------------------

build_staging:   ## Build staging Docker image
	@echo -e "\r\n${BOLD_GREEN}# Building staging version with Docker labels${NC}\r\n"
	@echo "   Version: $(VERSION)"
	@echo "   Commit: $(shell echo $(GIT_COMMIT) | cut -c1-8)"
	@echo "   Build Date: $(BUILD_DATE)"
	@if [ -z "$APP_SECRET" ]; then echo "❌ APP_SECRET environment variable is required"; exit 1; fi
	@if [ -z "$CADDY_MERCURE_JWT_SECRET" ]; then echo "❌ CADDY_MERCURE_JWT_SECRET environment variable is required"; exit 1; fi
	@docker build \
		--build-arg VERSION="$(VERSION)" \
		--build-arg GIT_COMMIT="$(GIT_COMMIT)" \
		--build-arg BUILD_DATE="$(BUILD_DATE)" \
		--build-arg ENVIRONMENT="staging" \
		--target frankenphp_staging \
		--tag "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):staging" \
		--tag "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):staging-$(VERSION)" \
		--tag "$(SERVICE_NAME):staging" \
		.
	@echo -e "${BOLD_GREEN}✅ Staging version built successfully${NC}"
	@echo "📋 Tags created:"
	@echo "   - $(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):staging"
	@echo "   - $(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):staging-$(VERSION)"
	@echo "   - $(SERVICE_NAME):staging"

.PHONY: build_staging

build_production: ## Build production Docker image
	@echo -e "\r\n${BOLD_GREEN}# Building production version with Docker labels${NC}\r\n"
	@echo "   Version: $(VERSION)"
	@echo "   Commit: $(shell echo $(GIT_COMMIT) | cut -c1-8)"
	@echo "   Build Date: $(BUILD_DATE)"
	@if [ -z "$APP_SECRET" ]; then echo "❌ APP_SECRET environment variable is required"; exit 1; fi
	@if [ -z "$CADDY_MERCURE_JWT_SECRET" ]; then echo "❌ CADDY_MERCURE_JWT_SECRET environment variable is required"; exit 1; fi
	@docker build \
		--build-arg VERSION="$(VERSION)" \
		--build-arg GIT_COMMIT="$(GIT_COMMIT)" \
		--build-arg BUILD_DATE="$(BUILD_DATE)" \
		--build-arg ENVIRONMENT="production" \
		--target frankenphp_prod \
		--tag "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):latest" \
		--tag "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):$(VERSION)" \
		--tag "$(SERVICE_NAME):production" \
		.
	@echo -e "${BOLD_GREEN}✅ Production version built successfully${NC}"
	@echo "📋 Tags created:"
	@echo "   - $(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):latest"
	@echo "   - $(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):$(VERSION)"
	@echo "   - $(SERVICE_NAME):production"

.PHONY: build_production

push_staging:    ## Push staging image to GitHub Container Registry
push_staging: build_staging
	@echo -e "\r\n${BOLD_GREEN}# Pushing staging image to GitHub Container Registry...${NC}\r\n"
	@docker push "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):staging"
	@docker push "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):staging-$(VERSION)"
	@echo -e "${BOLD_GREEN}✅ Staging image pushed successfully${NC}"

.PHONY: push_staging

push_production: ## Push production image to GitHub Container Registry
push_production: build_production
	@echo -e "\r\n${BOLD_GREEN}# Pushing production image to GitHub Container Registry...${NC}\r\n"
	@docker push "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):latest"
	@docker push "$(ECR_REGISTRY)/$(PROJECT_NAME)/$(SERVICE_NAME):$(VERSION)"
	@echo -e "${BOLD_GREEN}✅ Production image pushed successfully${NC}"

.PHONY: push_production

##
## Quality assurance
##---------------------------------------------------------------------------

phpcs:           ## Execute phpcs
	@docker exec -it jamly-api bash -c "vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php"

.PHONY: phpcs

phpstan:         ## Execute PHPStan
	@docker exec -it -u $$(id -u):$$(id -g) jamly-api vendor/bin/phpstan analyse --memory-limit=512M

.PHONY: phpstan

unit-test:       ## Run unit tests
	@echo -e "\r\n${BOLD_GREEN}# Testing API${NC}\r\n"
	@docker exec -it -u $$(id -u):$$(id -g) -eCOMPOSER_NO_INTERACTION=1 -eXDEBUG_MODE=coverage,debug api-appserver-xdebug bin/phpunit $${filter:+--filter=$(filter)} --exclude-group=functional

.PHONY: unit-test

quality:         ## Run all quality tools
quality: phpcs phpstan unit-test

.PHONY: quality

help:            ## Show this help message
	@echo ''
	@echo ''
	@echo '                              Jamly'
	@echo '                         ---------------'
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'
	@echo ''
	@echo '---------------------------------------------------------------------------'
	@echo 'Last commit: ' $(LAST_COMMIT)
	@echo ''
	@echo "Docker build info:"
	@echo "  Version: $(VERSION)"
	@echo "  Commit: $(shell echo $(GIT_COMMIT) | cut -c1-8)"
	@echo "  Build Date: $(BUILD_DATE)"

.PHONY: help

.DEFAULT_GOAL := help