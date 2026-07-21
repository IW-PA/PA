#!/usr/bin/env bash
#
# Apply every database/migrations/*.sql to the running MySQL container.
# Migrations are written to be idempotent, so this is safe to re-run; benign
# "already exists / duplicate column" errors (from migrations already applied)
# are ignored via mysql --force. Use it to bring a dev (or prod) DB up to date:
#
#   ./scripts/migrate_db.sh
#   DB_CONTAINER=budgie_mysql ./scripts/migrate_db.sh
#
set -uo pipefail

DB_CONTAINER="${DB_CONTAINER:-budgie_mysql}"
cd "$(dirname "$0")/.." || exit 1   # repo root

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "❌ MySQL container '$DB_CONTAINER' is not running." >&2
  echo "   Start the stack first (docker compose up -d), or set DB_CONTAINER=<name>." >&2
  exit 1
fi

shopt -s nullglob
files=(database/migrations/*.sql)
if [ ${#files[@]} -eq 0 ]; then
  echo "No migrations found."; exit 0
fi

for m in "${files[@]}"; do
  echo "→ $(basename "$m")"
  docker exec -i "$DB_CONTAINER" sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysql --force -u"$MYSQL_USER" "$MYSQL_DATABASE"' < "$m" 2>&1 \
    | grep -viE 'using a password|Duplicate column name|already exists|Duplicate entry|Duplicate key name' || true
done

echo "✅ Migrations applied to '$(docker exec "$DB_CONTAINER" printenv MYSQL_DATABASE)'."
