#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PACKAGE_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
FIXTURE_ROOT="${FIXTURE_ROOT:-${PACKAGE_ROOT}/../laravel-ddd-modular-fixtures}"
COMPOSE_FILE="${FIXTURE_ROOT}/docker-compose.yml"
APPS=(laravel10 laravel11 laravel12 laravel13)

usage() {
    cat <<'EOF'
Usage:
  scripts/run-fixture-matrix.sh [host|docker|both]

Environment variables:
  FIXTURE_ROOT  Override the default fixture workspace path.
EOF
}

require_path() {
    local path="$1"

    if [[ ! -e "$path" ]]; then
        echo "Required path not found: $path" >&2
        exit 1
    fi
}

run_host_matrix() {
    command -v php >/dev/null 2>&1 || {
        echo "php is required for host validation." >&2
        exit 1
    }

    for app in "${APPS[@]}"; do
        local app_root="${FIXTURE_ROOT}/${app}"

        require_path "$app_root"

        echo "==> Host validation: ${app}"
        (
            cd "$app_root"
            php artisan --version
            php artisan modular:make Blog --feature=api --feature=testing --force
            php artisan route:list --path=blog
        )
    done
}

run_docker_matrix() {
    command -v docker >/dev/null 2>&1 || {
        echo "docker is required for docker validation." >&2
        exit 1
    }

    require_path "$COMPOSE_FILE"

    docker compose -f "$COMPOSE_FILE" build

    for app in "${APPS[@]}"; do
        echo "==> Docker validation: ${app}"
        docker compose -f "$COMPOSE_FILE" run --rm "$app" composer install --no-interaction
        docker compose -f "$COMPOSE_FILE" run --rm "$app" php artisan key:generate --force
        docker compose -f "$COMPOSE_FILE" run --rm "$app" sh -lc 'touch database/database.sqlite && php artisan migrate --graceful'
        docker compose -f "$COMPOSE_FILE" run --rm "$app" php artisan modular:make Blog --feature=api --feature=testing --force
        docker compose -f "$COMPOSE_FILE" run --rm "$app" php artisan route:list --path=blog
    done
}

main() {
    local mode="${1:-host}"

    require_path "$FIXTURE_ROOT"

    case "$mode" in
        host)
            run_host_matrix
            ;;
        docker)
            run_docker_matrix
            ;;
        both)
            run_host_matrix
            run_docker_matrix
            ;;
        -h|--help|help)
            usage
            ;;
        *)
            usage >&2
            exit 1
            ;;
    esac
}

main "$@"
