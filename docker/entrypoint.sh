#!/usr/bin/env bash
# Entrypoint idempotente de Deliberia (desarrollo).
#
# - Todos los servicios: crea .env desde .env.example si falta.
# - Solo el servicio con DELIBERIA_BOOTSTRAP=1 (app): instala dependencias,
#   genera APP_KEY si falta, espera a Postgres, crea la BD de tests y migra.
set -euo pipefail

cd /var/www/html

if [ ! -f .env ]; then
    echo "[deliberia] Creando .env desde .env.example"
    cp .env.example .env
fi

if [ "${DELIBERIA_BOOTSTRAP:-0}" = "1" ]; then
    echo "[deliberia] Instalando dependencias de Composer"
    composer install --no-interaction --prefer-dist --no-progress

    if ! grep -qE '^APP_KEY=.+' .env; then
        echo "[deliberia] Generando APP_KEY"
        php artisan key:generate --force
    fi

    DB_HOST="${DB_HOST:-db}"
    DB_PORT="${DB_PORT:-5432}"
    DB_USERNAME="${DB_USERNAME:-deliberia}"
    DB_DATABASE="${DB_DATABASE:-deliberia}"
    export PGPASSWORD="${DB_PASSWORD:-deliberia}"

    echo "[deliberia] Esperando a PostgreSQL en ${DB_HOST}:${DB_PORT}"
    until pg_isready -q -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
        sleep 1
    done

    TEST_DB="${DB_DATABASE}_test"
    if ! psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d postgres -tAc \
        "SELECT 1 FROM pg_database WHERE datname='${TEST_DB}'" | grep -q 1; then
        echo "[deliberia] Creando base de datos de tests ${TEST_DB}"
        createdb -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" "$TEST_DB"
    fi

    echo "[deliberia] Aplicando migraciones"
    php artisan migrate --force
fi

exec "$@"
