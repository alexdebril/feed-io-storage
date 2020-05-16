build:
	docker-compose build
	docker-compose run unit-test composer install

test:
	docker-compose up -d
	docker-compose run unit-test vendor/bin/phpunit
	docker-compose down
