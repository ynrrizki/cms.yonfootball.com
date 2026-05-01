#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

echo "[cms] ensure sqlite file..."
php bin/ensure-sqlite.php

echo "[cms] migrate..."
php artisan migrate --force

if [[ "${SKIP_DB_SEED:-0}" == "1" ]]; then
  echo "[cms] SKIP_DB_SEED=1 — seed dilewati."
else
  echo "[cms] seed (idempotent)..."
  php artisan db:seed --force
fi

echo "[cms] storage:link..."
php artisan storage:link 2>/dev/null || true

echo "[cms] cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

PORT="${PORT:-8000}"
echo "[cms] serve 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
