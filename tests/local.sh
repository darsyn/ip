#!/usr/bin/env bash
set -uo pipefail

# USAGE (from host where Docker is available):
#   bash "tests/local.sh" [<...versions>]

# PURPOSE:
# - Because I'm sick of `wip` commits just to trigger the CI and then finding
#   out by fixing PHP 8.5 I broke something on PHP 7.1 :(
# - Runs the full CI suite locally via Docker, mirroring the two GitHub Actions
#   workflows (note to self: try not let this get out-of-sync, dumdum).

# ALTERNATIVES:
# - https://github.com/bahdotsh/wrkflw (Rust)
# - https://github.com/nektos/act (Golang)

# Grab the full normalized path to the parent/project directory.
export PROJECT_DIR="$(dirname "$(dirname "$(readlink -f -- "$0")")")"
# Every PHP version exercised by the lint + unit-test matrix in `runtime-testing.yaml`.
ALL_VERSIONS=("7.1" "7.2" "7.3" "7.4" "8.0" "8.1" "8.2" "8.3" "8.4" "8.5")
# The subset that runs analysis in the workflow `static-analysis.yaml`.
STATIC_ANALYSIS_VERSIONS=("7.4" "8.5")

FAILURES=()

is_static_analysis_version() {
    local NEEDLE="${1}" VERSION
    for VERSION in "${STATIC_ANALYSIS_VERSIONS[@]}"; do
        [ "${VERSION}" = "${NEEDLE}" ] && return 0
    done
    return 1
}

docker_composer() {
    mkdir -p '/tmp/composer' 2>'/dev/null' || true
    docker run --rm \
        --volume "${PROJECT_DIR}:/host:rw" \
        --workdir "/host" \
        --volume "/tmp/composer:/tmp/composer:rw" \
        --env "COMPOSER_HOME=/tmp/composer" \
        --user "$(id -u):$(id -g)" \
        "composer:latest" "$@"
}

docker_php() {
    local PHP_VERSION="${1}"
    shift
    docker run --rm \
        --volume "${PROJECT_DIR}:/host:rw" \
        --workdir "/host" \
        --user "$(id -u):$(id -g)" \
        "php:${PHP_VERSION}-cli" "$@"
}

# Two details are needed to reproduce CI's per-version resolution from a single,
# modern Composer image:
#  - `platform.php` is overridden to the target version so Composer resolves as
#    though it were running on that PHP, without needing a Composer binary
#    inside each php:*-cli image.
#  - security-advisory blocking is disabled, because the 7.1-era PHPUnit 7.x
#    line carries an advisory that modern Composer refuses to install.
# Any extra arguments are installed as additional dev dependencies (eg, PHPStan).
docker_install() {
    local PHP_VERSION="${1}"
    shift
    docker_composer config 'platform.php' "${PHP_VERSION}.99" \
        && docker_composer config 'policy.advisories.block' 'false' \
        && docker_composer update --prefer-stable --no-interaction --no-progress \
        || return 1
    if [ "$#" -gt 0 ]; then
        docker_composer require --dev --no-interaction --no-progress "$@" || return 1
    fi
}

cleanup() {
    command rm -rf "${PROJECT_DIR}/vendor" "${PROJECT_DIR}/composer.lock"
    if [ -f "${PROJECT_DIR}/composer.json.suite-backup" ]; then
        command mv -f "${PROJECT_DIR}/composer.json.suite-backup" "${PROJECT_DIR}/composer.json"
    fi
}
trap cleanup EXIT INT TERM

run_suite() {
    local PHP_VERSION="${1}"
    local FAILED=0
    echo
    echo "================================================================"
    echo " PHP ${PHP_VERSION}"
    echo "================================================================"

    command cp -f "${PROJECT_DIR}/composer.json" "${PROJECT_DIR}/composer.json.suite-backup"

    # 1. Syntax linting (runtime-testing.yaml: syntax-linting). No dependencies needed.
    echo "--- [${PHP_VERSION}] Syntax linting ---"
    docker_php "${PHP_VERSION}" sh -c \
        'find src/ -type f -name "*.php" -print0 | xargs -0 -n1 -P4 php -d"error_reporting=E_ALL&~E_DEPRECATED" -l -n | (! grep -v "No syntax errors detected")' \
        || { echo "[${PHP_VERSION}] SYNTAX LINTING FAILED"; FAILED=1; }

    # 2. Install dependencies (PHPStan+Fixer added only for static-analysis versions).
    echo "--- [${PHP_VERSION}] Installing dependencies ---"
    local DEV_DEPS=()
    if is_static_analysis_version "${PHP_VERSION}"; then
        DEV_DEPS=('phpstan/phpstan:^2.2' 'phpstan/phpstan-deprecation-rules' 'php-cs-fixer/shim:^3.95')
    fi
    docker_install "${PHP_VERSION}" "${DEV_DEPS[@]}" \
        || { echo "[${PHP_VERSION}] DEPENDENCY INSTALL FAILED"; FAILED=1; }

    if [ "${FAILED}" -eq 0 ]; then
        # 3. Code style (static-analysis.yaml: code-style) on the relevant versions.
        if is_static_analysis_version "${PHP_VERSION}"; then
            echo "--- [${PHP_VERSION}] Code style ---"
            docker_php "${PHP_VERSION}" php -d'memory_limit=512M' \
                'vendor/bin/php-cs-fixer' \
                check \
                --allow-risky='yes' \
                --diff \
                || { echo "[${PHP_VERSION}] CODE STYLE FAILED"; FAILED=1; }
        fi
    fi

    if [ "${FAILED}" -eq 0 ]; then
        # 4. Unit tests (runtime-testing.yaml: unit-testing).
        echo "--- [${PHP_VERSION}] Unit tests ---"
        docker_php "${PHP_VERSION}" php \
            'vendor/bin/phpunit' \
            --bootstrap='tests/bootstrap.php' \
            --test-suffix='Test.php' \
            tests \
            || { echo "[${PHP_VERSION}] UNIT TESTS FAILED"; FAILED=1; }
    fi

    if [ "${FAILED}" -eq 0 ]; then
        # 5. Static analysis (static-analysis.yaml: phpstan) on the relevant versions.
        if is_static_analysis_version "${PHP_VERSION}"; then
            echo "--- [${PHP_VERSION}] Static analysis ---"
            docker_php "${PHP_VERSION}" php -d'memory_limit=1G' \
                'vendor/bin/phpstan' \
                analyze \
                --no-progress \
                || { echo "[${PHP_VERSION}] STATIC ANALYSIS FAILED"; FAILED=1; }
        fi
    fi

    cleanup

    if [ "${FAILED}" -ne 0 ]; then
        FAILURES+=("${PHP_VERSION}")
        echo "--- [${PHP_VERSION}] RESULT: FAILED ---"
    else
        echo "--- [${PHP_VERSION}] RESULT: PASSED ---"
    fi
}

VERSIONS=("$@")
[ "${#VERSIONS[@]}" -eq 0 ] && VERSIONS=("${ALL_VERSIONS[@]}")

for version in "${VERSIONS[@]}"; do
    run_suite "${version}"
done

echo
echo "================================================================"
if [ "${#FAILURES[@]}" -eq 0 ]; then
    echo " ALL SUITES PASSED (${VERSIONS[*]})"
    echo "================================================================"
    exit 0
fi
echo " FAILED: ${FAILURES[*]}"
echo "================================================================"
exit 1
