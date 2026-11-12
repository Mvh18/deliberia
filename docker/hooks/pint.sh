#!/usr/bin/env bash
# Hook de pre-commit: comprueba el formato PHP con Pint dentro del contenedor.
# Si el entorno Docker no está levantado se avisa y se omite: el CI lo comprueba igualmente.
set -euo pipefail

if ! docker compose ps --status running --services 2>/dev/null | grep -qx app; then
    echo "[pint] El contenedor 'app' no está en marcha: se omite la comprobación local (el CI la ejecutará)."
    exit 0
fi

exec docker compose exec -T app vendor/bin/pint --test "$@"
