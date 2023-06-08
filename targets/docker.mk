build: ## Docker: builds the service
	$(call runDockerCompose,build)

down: ## Docker: stops the service
	$(call runDockerCompose,down)

up: ## Docker: starts the service
	$(call runDockerCompose,up --remove-orphans --detach)

logs: ## Docker: exposes the service logs
	$(call runDockerCompose,logs)

restart: ## Docker: restarts the service
	$(call runDockerCompose,restart $(SERVICE_NAME))

bash: ## Docker: stablish a bash session into main container
	$(call runDockerComposeExec,bash)
