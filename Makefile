.PHONY: install bench phpbench all clean

install: ## Install dependencies
	composer install

bench: ## Run CLI benchmark (all routers, all scenarios)
	php bin/benchmark

bench-filter: ## Run CLI benchmark with filter (e.g. make bench-filter ROUTER=waypoint SCENARIO=static)
	php bin/benchmark $(if $(ROUTER),--router=$(ROUTER)) $(if $(SCENARIO),--scenario=$(SCENARIO))

phpbench: ## Run PHPBench benchmarks
	vendor/bin/phpbench run --report=aggregate

phpbench-filter: ## Run PHPBench with filter (e.g. make phpbench-filter FILTER=Static)
	vendor/bin/phpbench run --report=aggregate --filter='$(FILTER)'

all: install bench ## Install and run benchmark

clean: ## Remove vendor directory
	rm -rf vendor composer.lock

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
