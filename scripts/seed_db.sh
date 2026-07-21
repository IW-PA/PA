#!/usr/bin/env bash
#
# Seed the Budgie database with VERIFIED accounts. Creates 1 admin + N users, each with
# a default account, a sample income and a sample expense (handy for the admin drill-down).
# Idempotent: it first removes previous "*@budgie.test" seed accounts, then recreates them.
#
# Usage:
#   ./scripts/seed_db.sh               # 1 admin + 5 users, password 'Password123!'
#   ./scripts/seed_db.sh 10            # 1 admin + 10 users
#   SEED_PASSWORD='secret' ./scripts/seed_db.sh 3
#
set -euo pipefail

DB_CONTAINER="${DB_CONTAINER:-budgie_mysql}"
APP_CONTAINER="${APP_CONTAINER:-budgie_app}"
COUNT="${1:-5}"
PASSWORD="${SEED_PASSWORD:-Password123!}"
DOMAIN="budgie.test"

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "❌ MySQL container '$DB_CONTAINER' is not running." >&2
  exit 1
fi

# Hash the password the same way the app does (password_hash / PASSWORD_DEFAULT):
# prefer the app container's PHP, fall back to a local php.
hash_password() {
  if docker ps --format '{{.Names}}' | grep -qx "$APP_CONTAINER"; then
    docker exec "$APP_CONTAINER" php -r 'echo password_hash($argv[1], PASSWORD_DEFAULT);' "$1"
  elif command -v php >/dev/null 2>&1; then
    php -r 'echo password_hash($argv[1], PASSWORD_DEFAULT);' "$1"
  else
    echo "❌ Need PHP (app container '$APP_CONTAINER' or a local php) to hash the password." >&2
    exit 1
  fi
}

mysql_run() { docker exec -i "$DB_CONTAINER" sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysql -u"$MYSQL_USER" "$MYSQL_DATABASE"'; }
esc() { printf '%s' "$1" | sed "s/'/''/g"; }  # SQL-escape single quotes

DB_NAME=$(docker exec "$DB_CONTAINER" printenv MYSQL_DATABASE)
H=$(esc "$(hash_password "$PASSWORD")")

{
  echo "SET FOREIGN_KEY_CHECKS = 0;"
  echo "DELETE FROM users WHERE email LIKE '%@${DOMAIN}';"
  echo "SET FOREIGN_KEY_CHECKS = 1;"

  # Verified admin
  echo "INSERT INTO users (first_name,last_name,email,password_hash,subscription_type,role,status,email_verified_at)"
  echo "VALUES ('Admin','Test','admin@${DOMAIN}','${H}','premium','admin','active',NOW());"
  echo "SET @uid := LAST_INSERT_ID();"
  echo "INSERT INTO accounts (user_id,name,description,balance) VALUES (@uid,'Compte Principal','Compte admin',5000.00);"

  # Verified users
  for i in $(seq 1 "$COUNT"); do
    echo "INSERT INTO users (first_name,last_name,email,password_hash,subscription_type,role,status,email_verified_at)"
    echo "VALUES ('User','${i}','user${i}@${DOMAIN}','${H}','free','user','active',NOW());"
    echo "SET @uid := LAST_INSERT_ID();"
    echo "INSERT INTO accounts (user_id,name,description,balance) VALUES (@uid,'Compte Principal','Compte de test',1000.00);"
    echo "SET @aid := LAST_INSERT_ID();"
    echo "INSERT INTO incomes  (user_id,account_id,name,amount,frequency,start_date) VALUES (@uid,@aid,'Salaire',2500.00,'mensuel',CURDATE());"
    echo "INSERT INTO expenses (user_id,account_id,name,amount,frequency,start_date) VALUES (@uid,@aid,'Loyer',800.00,'mensuel',CURDATE());"
  done
} | mysql_run

echo "✅ Seeded '${DB_NAME}': 1 admin + ${COUNT} verified users (each: 1 account, 1 income, 1 expense)."
echo "   Accounts : admin@${DOMAIN}, user1@${DOMAIN} … user${COUNT}@${DOMAIN}"
echo "   Password : ${PASSWORD}  (all accounts, email already verified)"
