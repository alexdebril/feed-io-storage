build:
	docker-compose build
	docker-compose run unit-test composer install

start:
	docker-compose up -d

stop:
	docker-compose down

test:
	docker-compose run unit-test composer src:test

update:
	docker-compose run unit-test composer update

lint:
	docker-compose run unit-test composer src:lint

cs-fix:
	docker-compose run unit-test composer src:cs-fix

stan:
	docker-compose run unit-test composer src:stan
