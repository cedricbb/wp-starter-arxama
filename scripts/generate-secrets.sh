#!/usr/bin/env bash
set -e

ENV_DOCKER=".env"
ENV_LOCAL=".env.local"

# Si les fichiers existent déjà, on ne fait rien
if [ -f "$ENV_DOCKER" ] && [ -f "$ENV_LOCAL" ]; then
  echo "🔐 .env and .env.local already exist, skipping secrets generation"
  exit 0
fi

# Récupération du nom du projet (nom du dossier courant)
PROJECT_NAME=$(basename "$(pwd)")
PROJECT_DOMAIN="$PROJECT_NAME.arxama.local"

# Configuration DB - SIMPLIFIÉE POUR LE DÉVELOPPEMENT LOCAL
# On utilise root/root pour éviter les problèmes de création d'utilisateurs dynamiques
DB_NAME="$PROJECT_NAME"
DB_USER="root"
DB_PASSWORD="root"

generate_salt () {
  openssl rand -base64 64 | tr -d '\n'
}

echo "🔐 Generating secrets for project: $PROJECT_NAME"

# .env pour Docker Compose
cat > $ENV_DOCKER <<EOF
PROJECT_NAME=$PROJECT_NAME
PROJECT_DOMAIN=$PROJECT_DOMAIN

DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD
DB_HOST=mariadb
DB_ROOT_PASSWORD=root
EOF

# .env.local pour Bedrock (PHP)
cat > $ENV_LOCAL <<EOF
DB_NAME='$DB_NAME'
DB_USER='$DB_USER'
DB_PASSWORD='$DB_PASSWORD'
DB_HOST='mariadb'

WP_ENV='development'
WP_HOME='https://${PROJECT_DOMAIN}'
WP_SITEURL='https://${PROJECT_DOMAIN}/wp'

AUTH_KEY='$(generate_salt)'
SECURE_AUTH_KEY='$(generate_salt)'
LOGGED_IN_KEY='$(generate_salt)'
NONCE_KEY='$(generate_salt)'
AUTH_SALT='$(generate_salt)'
SECURE_AUTH_SALT='$(generate_salt)'
LOGGED_IN_SALT='$(generate_salt)'
NONCE_SALT='$(generate_salt)'
EOF

echo "✅ .env and .env.local generated (using root/root for local dev)"
