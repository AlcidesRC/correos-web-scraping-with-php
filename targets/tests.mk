infection: ## Testing: Infection
	$(call runDockerComposeExec,./vendor/bin/infection --configuration=infection.json --threads=3 --coverage=./output/reports/coverage --ansi)

paratest: ## Testing: PHPUnit (in parallel mode)
	$(call runDockerComposeExec,php -d pcov.enabled=1 ./vendor/bin/paratest --passthru-php="'-d' 'pcov.enabled=1'" --coverage-text --coverage-xml=./output/reports/coverage/xml --coverage-html=./output/reports/coverage/html --log-junit=./output/reports/coverage/junit.xml)

phpunit: ## Testing: PHPUnit
	$(call runDockerComposeExec,./vendor/bin/phpunit --coverage-text --coverage-xml=./output/reports/coverage/xml --coverage-html=./output/reports/coverage/html --log-junit=./output/reports/coverage/junit.xml --coverage-cache ./output/.cache/coverage)

phpstan: ## Testing: PHPStan
	$(call runDockerComposeExec,./vendor/bin/phpstan analyse --level 9 --memory-limit 1G --ansi ./app ./tests)
