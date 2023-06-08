.DEFAULT_GOAL := help

###
# CONSTANTS
###

SELF_DIR := $(dir $(lastword $(MAKEFILE_LIST)))

ifneq (,$(findstring xterm,$(TERM)))
	BLACK   := $(shell tput -Txterm setaf 0)
	RED     := $(shell tput -Txterm setaf 1)
	GREEN   := $(shell tput -Txterm setaf 2)
	YELLOW  := $(shell tput -Txterm setaf 3)
	BLUE    := $(shell tput -Txterm setaf 4)
	MAGENTA := $(shell tput -Txterm setaf 5)
	CYAN    := $(shell tput -Txterm setaf 6)
	WHITE   := $(shell tput -Txterm setaf 7)
	RESET   := $(shell tput -Txterm sgr0)
else
	BLACK   := ""
	RED     := ""
	GREEN   := ""
	YELLOW  := ""
	BLUE    := ""
	MAGENTA := ""
	CYAN    := ""
	WHITE   := ""
	RESET   := ""
endif

SERVICE_NAME 		= app

DOCKER_COMPOSE 		= @docker-compose
DOCKER_COMPOSE_EXEC = $(DOCKER_COMPOSE) exec $(SERVICE_NAME)

###
# FUNCTIONS
###

require-as-number-%:
	@if [ -z "$($(*))" ] ; then \
		echo "" ; \
		echo " ⛔  ${RED}Parameter [ ${WHITE}${*}${RED} ] is required!${RESET}" ; \
		echo "" ; \
		echo "     ${YELLOW}Example:${RESET} ${CYAN}make run province=NUMBER [min=1] [max=999]${RESET}" ; \
		echo "" ; \
		exit 1 ; \
	fi;

# $(1)=CMD $(2)=OPTIONS
define runDockerCompose
	$(DOCKER_COMPOSE) $(1) $(2)
	@echo ""
	@echo " ✅  ${GREEN}Task done!${RESET}"
	@echo ""
endef

# $(1)=CMD $(2)=OPTIONS
define runDockerComposeExec
	$(DOCKER_COMPOSE_EXEC) $(1) $(2)
	@echo ""
	@echo " ✅  ${GREEN}Task done!${RESET}"
	@echo ""
endef

###
# MAKEFILE TARGETS
###

-include $(SELF_DIR)/targets/*.mk
