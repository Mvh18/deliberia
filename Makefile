# Atajos para el entorno Docker de Deliberia.
# Todo es un envoltorio de `docker compose`: sin make puedes ejecutar
# directamente los comandos que aparecen aquí.

.DEFAULT_GOAL := help
.PHONY: help up down logs shell test lint fix user fresh hooks

COMPOSE := docker compose
APP     := $(COMPOSE) exec app
NODE    := $(COMPOSE) exec vite

help: ## Muestra esta ayuda
	@grep -E '^[a-z-]+:.*##' $(MAKEFILE_LIST) | awk -F':.*## ' '{printf "  make %-8s %s\n", $$1, $$2}'

up: ## Levanta todo el entorno (docker compose up -d --build --wait)
	$(COMPOSE) up -d --build --wait

down: ## Detiene el entorno (conserva los datos)
	$(COMPOSE) down

logs: ## Sigue los logs de todos los servicios
	$(COMPOSE) logs -f

shell: ## Abre una shell en el contenedor de la app
	$(APP) bash

test: ## Ejecuta lint, análisis estático y tests (igual que el CI)
	$(APP) composer test

lint: ## Comprueba formato y tipos sin modificar nada
	$(APP) composer lint:check
	$(APP) composer types:check
	$(NODE) npm run check
	$(NODE) npm run types:check

fix: ## Corrige el formato automáticamente
	$(APP) composer lint
	$(NODE) npm run check:fix

user: ## Crea un usuario de forma interactiva
	$(COMPOSE) exec app php artisan deliberia:user:create

fresh: ## Borra datos (BD incluida) y vuelve a migrar
	$(APP) php artisan migrate:fresh

hooks: ## Instala los hooks de pre-commit (gitleaks, Conventional Commits)
	pre-commit install --hook-type pre-commit --hook-type commit-msg
