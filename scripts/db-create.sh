#!/usr/bin/env bash
set -e

echo "🗄️  Initialisation de la base de données…"

# Vérifications
: "${DB_NAME:?DB_NAME manquant}"
: "${DB_USER:?DB_USER manquant}"
: "${DB_PASSWORD:?DB_PASSWORD manquant}"
: "${DB_HOST:=mariadb}"
: "${DB_ROOT_PASSWORD:?DB_ROOT_PASSWORD manquant}"

echo "➡️  DB_HOST=$DB_HOST"
echo "➡️  DB_NAME=$DB_NAME"
echo "➡️  DB_USER=$DB_USER"

# Attente MariaDB
echo "⏳ Attente de MariaDB…"
until mysqladmin ping -h"$DB_HOST" -u root -p"$DB_ROOT_PASSWORD" --silent; do
  sleep 2
done

echo "✅ MariaDB disponible"

# Création DB + user (idempotent)
mysql -h"$DB_HOST" -u root -p"$DB_ROOT_PASSWORD" <<EOF
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS '${DB_USER}'@'%'
  IDENTIFIED BY '${DB_PASSWORD}';

GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';

FLUSH PRIVILEGES;
EOF

echo "✅ Base de données prête"
