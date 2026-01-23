#!/usr/bin/env bash
set -e

ENV_FILE=".env.local"

if [ -f "$ENV_FILE" ]; then
  echo "🔐 .env.local already exists, skipping secrets generation"
  exit 0
fi

echo "🔐 Generating secrets..."

DB_PASSWORD=$(openssl rand -base64 32)

cat > $ENV_FILE <<EOF
# Database
DB_NAME=wordpress
DB_USER=wordpress
DB_PASSWORD=${DB_PASSWORD}
DB_HOST=mariadb

# WordPress salts
AUTH_KEY=$(openssl rand -base64 64)
SECURE_AUTH_KEY=$(openssl rand -base64 64)
LOGGED_IN_KEY=$(openssl rand -base64 64)
NONCE_KEY=$(openssl rand -base64 64)
AUTH_SALT=$(openssl rand -base64 64)
SECURE_AUTH_SALT=$(openssl rand -base64 64)
LOGGED_IN_SALT=$(openssl rand -base64 64)
NONCE_SALT=$(openssl rand -base64 64)
EOF

echo "✅ .env.local generated"
