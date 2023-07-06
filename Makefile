init-dev:
	cp .env.example .env
	cp docker-compose.dev.yaml docker-compose.yaml

pre-test:
	cp app/phpunit.xml.dist app/phpunit.xml

init-prod:
	cp docker-compose.prod.yaml docker-compose.yaml

build:
	docker-compose build

start:
	docker-compose up -d

stop:
	docker-compose stop

sh:
	docker-compose exec -u www-data app sh

bash:
	make sh

sh-root:
	docker-compose exec app sh

bash-root:
	make sh-root

test:
	docker-compose exec -u www-data app bin/console --env=test doctrine:database:drop --if-exists --force
	docker-compose exec -u www-data app bin/console --env=test doctrine:database:create --if-not-exists
	docker-compose exec -u www-data app bin/console --env=test doctrine:m:m --no-interaction --allow-no-migration
	docker-compose exec -u www-data app bin/console --env=test doctrine:fixtures:load --no-interaction
	docker-compose exec -u www-data app bin/phpunit --configuration phpunit.xml

deploy:
	docker-compose stop app
	git pull
	docker-compose build app
	docker-compose up -d app
