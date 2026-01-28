#!/usr/bin/env bash
set -e

echo "🗄️  Initialisation de la base de données..."

# Charger les variables d'environnement si .env existe
if [ -f .env ]; then
  # On utilise grep pour filtrer UID/GID qui sont des variables readonly en bash
  # et on exporte les autres
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

# On utilise un conteneur temporaire pour créer la base de données
# Cela permet d'être sur le même réseau (backend) et d'avoir le client mysql
echo "🐳 Création de la base via Docker..."

# Note: Le réseau 'backend' doit exister (créé par stack-dev)
docker run --rm --network backend \
  -e MYSQL_PWD="$DB_ROOT_PASSWORD" \
  mariadb:10.11 \
  bash -c "
    echo '⏳ Attente de MariaDB ($DB_HOST)...';
    TIMEOUT=60; COUNTER=0;
    until mysqladmin ping -h '$DB_HOST' -u root --silent; do
      if [ \$COUNTER -gt \$TIMEOUT ]; then
        echo '❌ Timeout'; exit 1;
      fi;
      sleep 2;
      let COUNTER=COUNTER+2;
      echo -n '.';
    done;
    echo '';
    echo '✅ MariaDB disponible';

    echo '🛠 Création de la base et de l\'utilisateur...';
    mysql -h '$DB_HOST' -u root <<EOF
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
EOF
"

echo "✅ Base de données prête"
