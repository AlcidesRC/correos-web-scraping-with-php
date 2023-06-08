linter: ## Fixers: Linter
	$(call runDockerComposeExec,./vendor/bin/parallel-lint -e php -j 10 --colors ./app ./tests)

phpinsights: ## Fixers: PHPInsights
	$(call runDockerComposeExec,./vendor/bin/phpinsights --fix)
