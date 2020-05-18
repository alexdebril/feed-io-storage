build:
	docker-compose build
	docker-compose run unit-test composer install

start:
	docker-compose up -d

stop:
	docker-compose down

test:
	docker-compose run unit-test vendor/bin/phpunit

update:
	docker-compose run unit-test composer update

