---
name: dev-commands
description: Reference for FilkxTech's Makefile/Docker commands — starting the stack, running backend and frontend tests, linting, migrations, and the Docker service/volume layout. Use when running, testing, linting, or building the api/ or frontend/ services.
---

The Docker network must exist before starting:

```bash
docker network create filkx    # one-time
make init                      # docker-down-clear, docker-pull, docker-build, docker-up, api-init, frontend-init
make up / make down / make restart
```

### API

```bash
make api-init                  # composer install + migrate + passport:client
make migrate
make passport-client
make pint                      # PHP lint: tech-api-php-cli ./vendor/bin/pint --parallel --max-processes=4
make swagger                   # l5-swagger:generate (OpenAPI docs)
make test / make test-backend  # php artisan test
make test-coverage             # php artisan test --coverage-html=coverage (XDEBUG_MODE=coverage)
docker compose run --rm tech-api-php-cli php artisan <command>
```

### Frontend

```bash
make frontend-install
make frontend-dev              # Vite dev server
make frontend-build
make frontend-ssr              # runs `npm run serve:ssr` → currently fails, server.js is missing
make format                    # prettier + eslint --fix via tech-frontend-node-cli
make test-frontend             # npm run test:unit (vitest)
docker compose run --rm tech-frontend-node-cli npm run test:e2e   # playwright — no config/tests exist yet
```

### Docker services (docker-compose.yml)

Service names follow a `tech-` prefix convention (see `docker-compose.yml` for the current list).
`filkx` (network) and `api-postgres`/`redis`/`meilisearch-data`/`tech-nginx-temp` (volumes) are named
volume/network declarations, not services — despite `tech-nginx-temp` also carrying the `tech-` prefix.
`docker-compose-production.yml` uses `tech-frontend` (nginx+static dist) instead of the dev
`tech-frontend-spa`+`tech-frontend-node-cli` pair; otherwise mirrors the dev service set.
