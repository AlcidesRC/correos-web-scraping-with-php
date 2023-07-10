min = 1
max = 999

###
# PHP RELATED
###

version: ## Application: displays the PHP version
	$(call runDockerComposeExec,php -v)

info: ## Application: displays the php.init details
	$(call runDockerComposeExec,php -i)

###
# LINTERS & FIXERS
###

linter: ## Application: runs the PHP Linter in parallel mode
	$(call runDockerComposeExec,./vendor/bin/parallel-lint -e php -j 10 --colors ./app ./tests)

phpcs: ## Application: runs the PHPCodeSniffer to fix possible issues
	$(call runDockerComposeExec,./vendor/bin/phpcs --standard=PSR12 ./app ./tests)

phpcbf: ## Application: runs the PHPCodeBeautifier to fix possible issues
	$(call runDockerComposeExec,./vendor/bin/phpcbf --standard=PSR12 ./app ./tests)

phpinsights: ## Application: runs the PHPInsights to fix possible issues
	$(call runDockerComposeExec,./vendor/bin/phpinsights --fix)

###
# TESTING
###

infection: phpunit ## Application: runs the Infection test suite
	$(call runDockerComposeExec,./vendor/bin/infection --configuration=infection.json --threads=3 --coverage=./output/coverage --ansi)

paratest: ## Application: runs the PHPUnit test suite in parallel mode
	$(call runDockerComposeExec,php -d pcov.enabled=1 ./vendor/bin/paratest --passthru-php="'-d' 'pcov.enabled=1'" --coverage-text --coverage-xml=./output/coverage/xml --coverage-html=./output/coverage/html --log-junit=./output/coverage/junit.xml)

phpunit: ## Application: runs the PHPUnit test suite
	$(call runDockerComposeExec,./vendor/bin/phpunit --coverage-text --coverage-xml=./output/coverage/xml --coverage-html=./output/coverage/html --log-junit=./output/coverage/junit.xml --coverage-cache ./output/.cache/coverage)

phpstan: ## Application: runs PHPStan
	$(call runDockerComposeExec,./vendor/bin/phpstan analyse --level 9 --memory-limit 1G --ansi ./app ./tests)

###
# MISCELANEOUS
###

run: require-as-number-province ## Application: executes the main script
	$(call runDockerComposeExec,php ./bootstrap/app.php $(province) $(min) $(max))

combine-csv-files: ## Application: combines all CSV files into one
	$(shell awk '(NR == 1) || (FNR > 1)' ./src/output/province-*.csv > ./all-postal-codes.csv)
	@echo ""
	@echo " 📦  ${CYAN}File generated at [ ${WHITE}./all-postal-codes.csv${CYAN} ]${RESET}"
	@echo ""
	@echo " ✅  ${GREEN}Task done!${RESET}"
	@echo ""
