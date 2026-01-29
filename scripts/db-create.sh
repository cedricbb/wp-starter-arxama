#!/usr/bin/env bash
set -e

echo "🗄️  Initialisation de la base de données..."

# Charger les variables d'environnement de manière sûre
if [ -f .env ]; then
  export $(grep -v '^#' .env | grep -vE '^(UID|GID)=' | xargs)
fi

# Vérifications
: "${DB_NAME:?DB_NAME manquant}"
: "${DB_HOST:=mariadb}"
: "${DB_ROOT_PASSWORD:=root}"

echo "➡️  DB_HOST=$DB_HOST"
echo "➡️  DB_NAME=$DB_NAME"
echo "ℹ️  Utilisation de l'utilisateur root pour la connexion locale"

echo "🐳 Création de la base via Docker..."

# Utiliser un "here document" pour passer le script au conteneur.
# On ne crée plus d'utilisateur spécifique, on utilise root.
docker run --rm -i --network backend \
  -e MYSQL_PWD="$DB_ROOT_PASSWORD" \
  -e DB_HOST="$DB_HOST" \
  -e DB_NAME="$DB_NAME" \
  mariadb:10.11 bash <<'EOF'
echo "⏳ Attente de MariaDB ($DB_HOST)..."
until mysqladmin ping -h "$DB_HOST" -u root --silent; do
  sleep 1
  echo -n "."
done
echo ""
echo "✅ MariaDB disponible"

echo "🛠 Création de la base de données..."

mysql -h "$DB_HOST" -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL

echo "✅ Base de données créée"
EOF

echo "✅ Processus terminé."
