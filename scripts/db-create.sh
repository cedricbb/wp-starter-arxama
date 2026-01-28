#!/usr/bin/env bash
set -e

echo "🗄️  Initialisation de la base de données..."

# Charger les variables d'environnement de manière sûre
if [ -f .env ]; then
  export $(grep -v '^#' .env | grep -vE '^(UID|GID)=' | xargs)
fi

# Vérifications
: "${DB_NAME:?DB_NAME manquant}"
: "${DB_USER:?DB_USER manquant}"
: "${DB_PASSWORD:?DB_PASSWORD manquant}"
: "${DB_HOST:=mariadb}"
: "${DB_ROOT_PASSWORD:=root}"

echo "➡️  DB_HOST=$DB_HOST"
echo "➡️  DB_NAME=$DB_NAME"
echo "➡️  DB_USER=$DB_USER"

echo "🐳 Création de la base via Docker..."

# Utiliser un "here document" pour passer le script au conteneur.
# Cela évite TOUS les problèmes de guillemets avec bash -c.
# Le -i est important pour que le conteneur lise sur son entrée standard.
docker run --rm -i --network backend \
  -e MYSQL_PWD="$DB_ROOT_PASSWORD" \
  -e DB_HOST="$DB_HOST" \
  -e DB_NAME="$DB_NAME" \
  -e DB_USER="$DB_USER" \
  -e DB_PASSWORD="$DB_PASSWORD" \
  mariadb:10.11 bash <<'EOF'
echo "⏳ Attente de MariaDB ($DB_HOST)..."
until mysqladmin ping -h "$DB_HOST" -u root --silent; do
  sleep 1
  echo -n "."
done
echo ""
echo "✅ MariaDB disponible"

echo "🛠 Création de la base et de l'utilisateur..."

# Échapper le mot de passe pour SQL directement dans le conteneur
DB_PASSWORD_ESCAPED=$(echo "$DB_PASSWORD" | sed "s/'/\\\\'/g; s/\"/\\\\\"/g")

# Utiliser un autre here-doc pour la commande SQL
mysql -h "$DB_HOST" -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD_ESCAPED';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
SQL

echo "✅ Base de données et utilisateur créés"
EOF

echo "✅ Processus de création de base de données terminé."