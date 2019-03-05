P := "$$(tput setaf 2)"
S := "$$(tput setaf 4)"
L := "$$(tput setaf 6)"
R := "$$(tput sgr0)"
usage:
	@echo ""
	@echo " $(L)┏━━━━━━━━━━━━━━━━━━━━━┓$(R)"
	@echo " $(L)┃   $(R)Darsyn IP$(L)         ┃$(R)"
	@echo " $(L)┡━━━━━━━━━━━━━━━━━━━━━┩$(R)"
	@echo " $(L)│ $(R)Available Commands:$(L) │$(R)"
	@echo " $(L)╰─┬───────────────────╯$(R)"
	@echo "   $(L)├─$(R) $(P)install$(R)           Install third-party dependencies."
	@echo "   $(L)╰─$(R) $(P)test$(R)              Run all of the following tests:"
	@echo "      $(L)├─$(R) $(S)unit$(R)           • Run PHPUnit tests."
	@echo "      $(L)╰─$(R) $(S)cs$(R)             • Run check PHP code for linting and syntax errors."
	@echo ""
	@echo "   $(L)╭$(R)                                           $(L)╮$(R)"
	@echo "   $(L)│$(R) Performs basic shortcuts; use $(P)vendor/bin/$(R) $(L)│$(R)"
	@echo "   $(L)│$(R) executables for advanced usage.           $(L)│$(R)"
	@echo "   $(L)╰$(R)                                           $(L)╯$(R)"
	@echo ""

MKFILE := $(abspath $(lastword $(MAKEFILE_LIST)))
MKDIR  := $(dir $(MKFILE))

# Composer Dependencies
vendor/autoload.php:
	composer install

# Commonly-used PHP Scripts
bin/phpunit: vendor/autoload.php
bin/phpcs: vendor/autoload.php

# Shortcuts
install: vendor/autoload.php
up: composer keys
	docker-compose up -d
test: cs unit
# Specific Types of Tests
cs: bin/phpcs
	"$(MKDIR)/vendor/bin/phpcs" --standard="$(MKDIR)/phpcs.xml" src
unit: bin/phpunit
	[ -f "var/xdebug-filter.php" ] || "$(MKDIR)/vendor/bin/phpunit" -c "$(MKDIR)/phpunit.xml" --dump-xdebug-filter "var/xdebug-filter.php"
	"$(MKDIR)/vendor/bin/phpunit" -c "$(MKDIR)/phpunit.xml" --prepend "var/xdebug-filter.php" --order-by=random --resolve-dependencies

.PHONY: usage install test cs unit
