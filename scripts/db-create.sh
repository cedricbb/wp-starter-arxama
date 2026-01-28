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

# Construire la requête SQL dans une variable pour éviter les problèmes de heredoc
# Échapper les apostrophes dans le mot de passe pour la sécurité
DB_PASSWORD_ESCAPED=$(echo "$DB_PASSWORD" | sed "s/'/\\\\'/g")

SQL_COMMAND="
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD_ESCAPED';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
"

# Utiliser un conteneur temporaire pour exécuter la commande
docker run --rm --network backend \
  -e MYSQL_PWD="$DB_ROOT_PASSWORD" \
  mariadb:10.11 \
  bash -c "
    echo '⏳ Attente de MariaDB ($DB_HOST)...';
    until mysqladmin ping -h \"$DB_HOST\" -u root --silent; do
      sleep 1;
      echo -n '.';
    done;
    echo '';
    echo '✅ MariaDB disponible';

    echo '🛠 Création de la base et de l\\\'utilisateur...';
    mysql -h \"$DB_HOST\" -u root -e \"$SQL_COMMAND\"
"

echo "✅ Base de données prête"