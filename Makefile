# GÉNIE MARKETING Mag — raccourcis DX
# Usage : make <cible>

SHELL := /bin/bash
.DEFAULT_GOAL := help

DOCKER_DEV = docker compose -f infra/docker/docker-compose.yml
DOCKER_PROD = docker compose -f infra/docker/docker-compose.prod.yml

# -----------------------------------------------------------------------------
# Aide
# -----------------------------------------------------------------------------
.PHONY: help
help: ## Affiche cette aide
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# -----------------------------------------------------------------------------
# Environnement de développement
# -----------------------------------------------------------------------------
.PHONY: up down restart logs ps shell
up: ## Démarre les services Docker (MySQL, Redis, MinIO, Mailpit, MeiliSearch)
	$(DOCKER_DEV) up -d
	@echo ""
	@echo "✓ Services démarrés"
	@echo "  MySQL        localhost:3306 (user=gm / pass=gm / db=gm)"
	@echo "  Redis        localhost:6379"
	@echo "  MinIO UI     http://localhost:9001 (minio / minio12345)"
	@echo "  Mailpit UI   http://localhost:8025"
	@echo "  MeiliSearch  http://localhost:7700"

down: ## Arrête les services Docker
	$(DOCKER_DEV) down

restart: down up ## Redémarre les services

logs: ## Suit les logs des services (Ctrl+C pour quitter)
	$(DOCKER_DEV) logs -f

ps: ## Liste les services Docker
	$(DOCKER_DEV) ps

shell: ## Ouvre un shell dans le conteneur MySQL
	$(DOCKER_DEV) exec mysql sh

# -----------------------------------------------------------------------------
# Laravel
# -----------------------------------------------------------------------------
.PHONY: serve fresh migrate seed fmt lint tinker
serve: ## Lance le serveur Laravel sur http://localhost:8000
	cd web && php artisan serve --host=127.0.0.1 --port=8000

fresh: ## Rebuild BD + seed (à utiliser avec précaution : détruit les données)
	cd web && php artisan migrate:fresh --seed

migrate: ## Applique les migrations en attente
	cd web && php artisan migrate

seed: ## Lance les seeders
	cd web && php artisan db:seed

fmt: ## Formate le code PHP avec Pint (PSR-12)
	cd web && ./vendor/bin/pint

lint: ## Vérifie le formatage sans modifier (pour CI)
	cd web && ./vendor/bin/pint --test

tinker: ## Ouvre une REPL Laravel
	cd web && php artisan tinker

# -----------------------------------------------------------------------------
# Tests
# -----------------------------------------------------------------------------
.PHONY: test test-unit test-feature test-coverage
test: ## Lance toute la suite Pest
	cd web && vendor/bin/pest --compact

test-unit: ## Tests unitaires uniquement
	cd web && vendor/bin/pest tests/Unit

test-feature: ## Tests feature uniquement
	cd web && vendor/bin/pest tests/Feature

test-coverage: ## Tests avec couverture (nécessite Xdebug ou pcov)
	cd web && vendor/bin/pest --coverage --min=70

# -----------------------------------------------------------------------------
# Front-end
# -----------------------------------------------------------------------------
.PHONY: build dev-assets
build: ## Build des assets Vite (production)
	cd web && npm run build

dev-assets: ## Vite en watch mode
	cd web && npm run dev

# -----------------------------------------------------------------------------
# Opérations
# -----------------------------------------------------------------------------
.PHONY: pre-launch prune-audit expire-subs remind-renewal
pre-launch: ## Check-list de mise en production
	cd web && php artisan gm:pre-launch

prune-audit: ## Purge les logs d'audit > 12 mois (RGPD)
	cd web && php artisan gm:audit:prune

expire-subs: ## Bascule les abonnements échus en "expired"
	cd web && php artisan gm:subscriptions:expire

remind-renewal: ## Envoie les rappels de renouvellement J-7
	cd web && php artisan gm:subscriptions:remind-renewal

# -----------------------------------------------------------------------------
# Production
# -----------------------------------------------------------------------------
.PHONY: prod-pull prod-up prod-deploy prod-logs
prod-pull: ## Pull la dernière image Docker depuis GHCR
	$(DOCKER_PROD) pull app

prod-up: ## Démarre la stack production
	$(DOCKER_PROD) up -d

prod-deploy: prod-pull prod-up ## Pull + rollout app + migrations + cache
	$(DOCKER_PROD) exec app php artisan migrate --force
	$(DOCKER_PROD) exec app php artisan config:cache
	$(DOCKER_PROD) exec app php artisan route:cache
	$(DOCKER_PROD) exec app php artisan view:cache
	@echo ""
	@echo "✓ Déploiement terminé"

prod-logs: ## Logs production en temps réel
	$(DOCKER_PROD) logs -f --tail=100 app
