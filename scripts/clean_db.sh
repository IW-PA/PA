#!/usr/bin/env bash
#
# Clean the Budgie database: TRUNCATE every table (keeps the schema, removes ALL data).
# Runs against the running MySQL container, using the container's own credentials.
#
# Usage:
#   ./scripts/clean_db.sh              # asks for confirmation
#   ./scripts/clean_db.sh --force      # no prompt
#   DB_CONTAINER=budgie_mysql ./scripts/clean_db.sh
#
set -euo pipefail

DB_CONTAINER="${DB_CONTAINER:-budgie_mysql}"
FORCE=0
[ "${1:-}" = "--force" ] && FORCE=1

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "❌ MySQL container '$DB_CONTAINER' is not running." >&2
  echo "   Start the stack first (docker compose up -d), or set DB_CONTAINER=<name>." >&2
  exit 1
fi

mysql_run() { docker exec -i "$DB_CONTAINER" sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysql -u"$MYSQL_USER" "$MYSQL_DATABASE"'; }
DB_NAME=$(docker exec "$DB_CONTAINER" printenv MYSQL_DATABASE)

if [ "$FORCE" -ne 1 ]; then
  echo "⚠️  About to DELETE ALL DATA from database '$DB_NAME' (container '$DB_CONTAINER')."
  read -rp "   Type 'yes' to confirm: " ans
  [ "$ans" = "yes" ] || { echo "Aborted."; exit 1; }
fi

TABLES=$(docker exec "$DB_CONTAINER" sh -c \
  'MYSQL_PWD="$MYSQL_PASSWORD" mysql -N -u"$MYSQL_USER" -e "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()" "$MYSQL_DATABASE"')

if [ -z "$TABLES" ]; then
  echo "No tables in '$DB_NAME' — nothing to clean."
  exit 0
fi

{
  echo "SET FOREIGN_KEY_CHECKS = 0;"
  for t in $TABLES; do echo "TRUNCATE TABLE \`$t\`;"; done
  echo "SET FOREIGN_KEY_CHECKS = 1;"
} | mysql_run

echo "✅ Database '$DB_NAME' cleaned — $(echo "$TABLES" | wc -w | tr -d ' ') tables truncated."
