#!/usr/bin/env bash

# Sets the project name and namespace for re-usability
PROJECT_NAME='Seamless Wallet Client'

# Setup variable for needed console color definitions
RED='\033[1;31m'
YELLOW='\033[1;33m'
GREEN='\033[1;32m'
NC='\033[0m' # No Color

# Displays the help message for the script
function displayHelp() {
    # Be sure to get the filename dynamically
    filename=$(basename "$0")

    # Construct the help screen to be displayed
    printf "${GREEN}App Shell ${YELLOW}version 0.0.1 ${RED}${PROJECT_NAME}${GREEN}\n"
    echo   "----------------------------------------------------------------------------------------------------"
    echo -e "${YELLOW}Usage: ${NC}"
    printf "  %s [option] [arguments]\n" "$filename"
    echo ""
    echo -e "${YELLOW}Options: ${NC}"
    printf "  %-23s %s\n" "help" "Displays this help screen"
    printf "  %-23s %s\n" "test" "Runs all tests with coverage"
    printf "  %-23s %s\n" "integration-tests" "Runs all integration tests"
    printf "  %-23s %s\n" "cs" "Runs a CS check WITHOUT fixing code"
    printf "  %-23s %s\n" "fixcs" "Runs a CS check THAT FIXES code"
    printf "  %-23s %s\n" "preflight" "Runs a CS check THAT FIXES code & PhpStan"
    printf "  %-23s %s\n" "stan" "Runs phpstan to check code"
}

# Runs all PHPUnit tests
function runTests() {
    echo -e "${GREEN}Running tests"
    echo -e "${GREEN}--------------------------------------- ${NC}"

    $(pwd)/vendor/bin/phpunit --configuration $(pwd)/phpunit.xml
}

# Runs all PHPUnit tests
function runIntegrationTests() {
    echo -e "${GREEN}Running integration tests"
    echo -e "${GREEN}--------------------------------------- ${NC}"

    $(pwd)/vendor/bin/phpunit --configuration $(pwd)/phpunit.integration.xml
}

# Runs the code style checker in dry-run mode
function runCodeStyleCheck() {
    echo -e "${GREEN}Running code style check"
    echo -e "${GREEN}--------------------------------------- ${NC}"

    $(pwd)/vendor/bin/php-cs-fixer fix --dry-run --verbose
}

# Runs CS fixer in a base container
function runCodeStyleFixer {
    echo -e "${GREEN}Running CS fixer"
    echo -e "${GREEN}--------------------------------------- ${NC}"

    $(pwd)/vendor/bin/php-cs-fixer fix --verbose
}

# Runs the PHPStan code check
function runPhpStanCheck() {
    echo -e "${GREEN}Running PhpStan check"
    echo -e "${GREEN}--------------------------------------- ${NC}"

    $(pwd)/vendor/bin/phpstan analyse --memory-limit=2G --level=7 $(pwd)/src/
}

# Runs both the cs fixer and PHPStan
function runPreflight() {
    set -e
    runCodeStyleFixer
    runPhpStanCheck
    runTests
}

# Display help if no arguments are passed to the script
if [[ ! $1 ]]; then
    displayHelp
    exit 0
fi

# Case that delegates to the correct function
case "$1" in
    help)
        displayHelp
        ;;
    test)
        runTests
        ;;
    integration-tests)
        runIntegrationTests
        ;;
    cs)
        runCodeStyleCheck
        ;;
    fixcs)
        runCodeStyleFixer
        ;;
    stan)
        runPhpStanCheck
        ;;
    preflight)
      runPreflight
      ;;
    *)
        echo $"Usage: $0 {help}"
        exit 1
esac

exit 0
