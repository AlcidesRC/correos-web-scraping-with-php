min = 1
max = 999

version: ## Application: displays the PHP version
	$(call runDockerComposeExec,php -v)

run: require-as-number-province ## Application: executes the main script
	$(call runDockerComposeExec,php ./bootstrap/app.php $(province) $(min) $(max))

combine-csv-files: ## Application: combines all CSV files into one
	$(shell awk '(NR == 1) || (FNR > 1)' ./src/output/province-*.csv > ./all-postal-codes.csv)
	@echo ""
	@echo " 📦  ${CYAN}File generated at [ ${WHITE}./all-postal-codes.csv${CYAN} ]${RESET}"
	@echo ""
	@echo " ✅  ${GREEN}Task done!${RESET}"
	@echo ""