#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_NAME="${DB_NAME:-myfolio}"
DB_USER="${DB_USER:-myfolio}"
DB_HOST="${DB_HOST:-127.0.0.1}"

if [[ ! "${DB_NAME}" =~ ^[a-zA-Z0-9_]+$ || ! "${DB_USER}" =~ ^[a-zA-Z0-9_]+$ || ! "${DB_HOST}" =~ ^[a-zA-Z0-9_.-]+$ ]]; then
    echo "DB_NAME, DB_USER, and DB_HOST contain unsupported characters." >&2
    exit 1
fi

if [[ "$(id -u)" -ne 0 ]]; then
    echo "Run this script with sudo: sudo bash deploy/setup-database.sh"
    exit 1
fi

read -r -s -p "MariaDB password for ${DB_USER}@${DB_HOST}: " DB_PASSWORD
echo
if [[ -z "${DB_PASSWORD}" ]]; then
    echo "Database password must not be empty." >&2
    exit 1
fi

DB_PASSWORD_SQL="${DB_PASSWORD//\\/\\\\}"
DB_PASSWORD_SQL="${DB_PASSWORD_SQL//\'/\'\'}"

export DB_NAME DB_USER DB_HOST DB_PASSWORD PROJECT_ROOT

mariadb <<'SQL'
CREATE DATABASE IF NOT EXISTS `myfolio` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL

mariadb <<SQL
CREATE USER IF NOT EXISTS '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASSWORD_SQL}';
ALTER USER '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASSWORD_SQL}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'${DB_HOST}';
FLUSH PRIVILEGES;
SQL

mariadb -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "${PROJECT_ROOT}/database/schema.sql"
mariadb -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "${PROJECT_ROOT}/database/seed.sql"

echo "Database schema and seed imported for ${DB_NAME}."
echo "Update myfolio/.env with the same DB_PASSWORD before starting PHP-FPM/Nginx."