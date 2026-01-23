# Arxama WordPress Starter

Starter WordPress professionnel basé sur Bedrock.

## Principes
- Aucun secret dans git
- Aucune modif en prod
- Plugins via Composer
- Thème enfant versionné
- Déploiement automatisable

Ce repo est la base de tous les sites clients.

## Environment variables

This project uses two environment files:

- `.env` → Docker Compose (project name, domain, database)
- `.env.local` → WordPress / Bedrock secrets

Both files are generated automatically during project initialization.

## Secrets generation

Two environment files are generated automatically:

- `.env` → Docker Compose
- `.env.local` → WordPress / Bedrock

Scripts:
- `generate-secrets.sh` (Linux / macOS)
- `generate-secrets.ps1` (Windows)
