init: docker-down-clear docker-pull docker-build docker-up api-init frontend-init
up: docker-up
down: docker-down
restart: down up
lint: pint format swagger

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-down-clear:
	docker compose down -v --remove-orphans

docker-pull:
	docker compose pull

docker-build:
	docker compose build

api-init: api-composer-install migrate passport-client

api-composer-install:
	docker compose run --rm live-api-php-cli composer install

test: test-backend

test-backend:
	docker compose run --rm live-api-php-cli php artisan

test-frontend:
	docker compose run --rm live-frontend-node-cli npm run test:unit

test-coverage:
	docker compose run --rm -e XDEBUG_MODE=coverage live-api-php-cli php artisan test --coverage-html=coverage

migrate:
	docker compose run --rm live-api-php-cli php artisan migrate

passport-client:
	docker compose run --rm live-api-php-cli php artisan passport:client --personal --no-interaction

pint:
	docker compose run --rm live-api-php-cli ./vendor/bin/pint --parallel --max-processes=4

frontend-init: frontend-install

format:
	docker compose run --rm live-frontend-node-cli npm run format

frontend-install:
	docker compose run --rm live-frontend-node-cli npm install

frontend-dev:
	docker compose run --rm live-frontend-node-cli npm run dev

frontend-build:
	docker compose run --rm live-frontend-node-cli npm run build

frontend-ssr:
	docker compose run --rm -p 3000:3000 live-frontend-node-cli sh -c "npm run build && npm run serve:ssr"

swagger:
	docker compose run --rm live-api-php-cli php artisan l5-swagger:generate
