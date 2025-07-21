SHELL := /bin/bash
LAST_COMMIT := $(shell git log -1 --oneline --pretty=format:"%h - %an, %ar")
BOLD_GREEN := \033[1;32m
NC := \033[0m

install:         ## Install dependencies
	@echo -e "\r\n${BOLD_GREEN}# Installing dependencies${NC}\r\n"
	@docker compose build

start:           ## Start the API
	@echo -e "\r\n${BOLD_GREEN}# Starting API${NC}\r\n"
	@docker compose up -d

stop:            ## Stop the API
	@echo -e "\r\n${BOLD_GREEN}# Stopping API${NC}\r\n"
	@docker compose down

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

.PHONY: help

.DEFAULT_GOAL := help