test:
	docker-compose up -d
	vendor/bin/phpunit
	docker-compose down
