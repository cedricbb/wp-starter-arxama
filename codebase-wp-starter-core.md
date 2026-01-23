# application.php

```php
<?php

/**
 * Your base production configuration goes in this file. Environment-specific
 * overrides go in their respective config/environments/{{WP_ENV}}.php file.
 *
 * A good default policy is to deviate from the production config as little as
 * possible. Try to define as much of your configuration in this file as you
 * can.
 */

use Roots\WPConfig\Config;

use function Env\env;

// USE_ENV_ARRAY + CONVERT_* + STRIP_QUOTES
Env\Env::$options = 31;

/**
 * Directory containing all of the site's files
 *
 * @var string
 */
$root_dir = dirname(__DIR__);

/**
 * Document Root
 *
 * @var non-falsy-string
 */
$webroot_dir = $root_dir . '/web';

/**
 * Use Dotenv to set required environment variables and load .env file in root
 * .env.local will override .env if it exists
 */
if (file_exists($root_dir . '/.env')) {
    $env_files = file_exists($root_dir . '/.env.local')
        ? ['.env', '.env.local']
        : ['.env'];

    $repository = Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(Dotenv\Repository\Adapter\EnvConstAdapter::class)
        ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
        ->immutable()
        ->make();

    $dotenv = Dotenv\Dotenv::create($repository, $root_dir, $env_files, false);
    $dotenv->load();

    $dotenv->required(['WP_HOME', 'WP_SITEURL']);
    if (!env('DATABASE_URL')) {
        $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
    }
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

/**
 * Infer WP_ENVIRONMENT_TYPE based on WP_ENV
 */
if (!env('WP_ENVIRONMENT_TYPE') && in_array(WP_ENV, ['production', 'staging', 'development', 'local'])) {
    Config::define('WP_ENVIRONMENT_TYPE', WP_ENV);
}

/**
 * URLs
 */
Config::define('WP_HOME', env('WP_HOME'));
Config::define('WP_SITEURL', env('WP_SITEURL'));

/**
 * Custom Content Directory
 */
Config::define('CONTENT_DIR', '/app');
Config::define('WP_CONTENT_DIR', $webroot_dir . Config::get('CONTENT_DIR'));
Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . Config::get('CONTENT_DIR'));

/**
 * DB settings
 */
if (env('DB_SSL')) {
    Config::define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL);
}

Config::define('DB_NAME', env('DB_NAME'));
Config::define('DB_USER', env('DB_USER'));
Config::define('DB_PASSWORD', env('DB_PASSWORD'));
Config::define('DB_HOST', env('DB_HOST') ?: 'localhost');
Config::define('DB_CHARSET', 'utf8mb4');
Config::define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

if (env('DATABASE_URL')) {
    $dsn = (object) parse_url(env('DATABASE_URL'));

    Config::define('DB_NAME', substr($dsn->path, 1));
    Config::define('DB_USER', $dsn->user);
    Config::define('DB_PASSWORD', isset($dsn->pass) ? $dsn->pass : null);
    Config::define('DB_HOST', isset($dsn->port) ? "{$dsn->host}:{$dsn->port}" : $dsn->host);
}

/**
 * Authentication Unique Keys and Salts
 */
Config::define('AUTH_KEY', env('AUTH_KEY'));
Config::define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
Config::define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
Config::define('NONCE_KEY', env('NONCE_KEY'));
Config::define('AUTH_SALT', env('AUTH_SALT'));
Config::define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
Config::define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
Config::define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', true);
Config::define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);

// Disable the plugin and theme file editor in the admin
Config::define('DISALLOW_FILE_EDIT', true);

// Disable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', true);

// Limit the number of post revisions
Config::define('WP_POST_REVISIONS', env('WP_POST_REVISIONS') ?? true);

// Disable script concatenation
Config::define('CONCATENATE_SCRIPTS', false);

// Disable auto updates
Config::define('WP_AUTO_UPDATE_CORE', true);

// Force SSL Admin
Config::define('FORCE_SSL_ADMIN', true);

/**
 * Debugging Settings
 */
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('WP_DEBUG_LOG', false);
Config::define('SCRIPT_DEBUG', false);
ini_set('display_errors', '0');

/**
 * Allow WordPress to detect HTTPS when used behind a reverse proxy or a load balancer
 * See https://codex.wordpress.org/Function_Reference/is_ssl#Notes
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

Config::apply();

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}

```

# composer.json

```json
{
  "name": "roots/bedrock",
  "type": "project",
  "license": "MIT",
  "description": "WordPress boilerplate with Composer, easier configuration, and an improved folder structure",
  "homepage": "https://roots.io/bedrock/",
  "authors": [
    {
      "name": "Scott Walkinshaw",
      "email": "scott.walkinshaw@gmail.com",
      "homepage": "https://github.com/swalkinshaw"
    },
    {
      "name": "Ben Word",
      "email": "ben@benword.com",
      "homepage": "https://github.com/retlehs"
    }
  ],
  "keywords": [
    "bedrock",
    "composer",
    "roots",
    "wordpress",
    "wp",
    "wp-config"
  ],
  "support": {
    "issues": "https://github.com/roots/bedrock/issues",
    "forum": "https://discourse.roots.io/category/bedrock"
  },
  "repositories": [
    {
      "name": "wpackagist",
      "type": "composer",
      "url": "https://wpackagist.org",
      "only": [
        "wpackagist-plugin/*",
        "wpackagist-theme/*"
      ]
    }
  ],
  "require": {
    "php": ">=8.1",
    "composer/installers": "^2.2",
    "vlucas/phpdotenv": "^5.5",
    "oscarotero/env": "^2.1",
    "roots/bedrock-autoloader": "^1.0",
    "roots/bedrock-disallow-indexing": "^2.0",
    "roots/wordpress": "6.9",
    "roots/wp-config": "1.0.0",
    "wpackagist-plugin/elementor": "^3.34",
    "wpackagist-theme/hello-elementor": "^3.4"
  },
  "require-dev": {
    "laravel/pint": "^1.18"
  },
  "config": {
    "optimize-autoloader": true,
    "preferred-install": "dist",
    "allow-plugins": {
      "composer/installers": true,
      "roots/wordpress-core-installer": true
    }
  },
  "minimum-stability": "dev",
  "prefer-stable": true,
  "extra": {
    "installer-paths": {
      "web/app/mu-plugins/{$name}/": [
        "type:wordpress-muplugin"
      ],
      "web/app/plugins/{$name}/": [
        "type:wordpress-plugin"
      ],
      "web/app/themes/{$name}/": [
        "type:wordpress-theme"
      ]
    },
    "wordpress-install-dir": "web/wp"
  },
  "scripts": {
    "lint": "pint --test",
    "lint:fix": "pint"
  }
}
```

# dev/ai-digest.md

```md

```

# environments/development.php

```php
<?php

/**
 * Configuration overrides for WP_ENV === 'development'
 */

use Roots\WPConfig\Config;

use function Env\env;

Config::define('SAVEQUERIES', true);
Config::define('WP_DEBUG', true);
Config::define('WP_DEBUG_DISPLAY', true);
Config::define('WP_DEBUG_LOG', env('WP_DEBUG_LOG') ?? true);
Config::define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
Config::define('SCRIPT_DEBUG', true);
Config::define('DISALLOW_INDEXING', true);

ini_set('display_errors', '1');

// Enable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', false);

```

# environments/staging.php

```php
<?php

/**
 * Configuration overrides for WP_ENV === 'staging'
 */

use Roots\WPConfig\Config;

/**
 * You should try to keep staging as close to production as possible. However,
 * should you need to, you can always override production configuration values
 * with `Config::define`.
 *
 * Example: `Config::define('WP_DEBUG', true);`
 * Example: `Config::define('DISALLOW_FILE_MODS', false);`
 */

Config::define('DISALLOW_INDEXING', true);

```

# README.md

```md
# Arxama WordPress Starter

Starter WordPress professionnel basé sur Bedrock.

## Principes
- Aucun secret dans git
- Aucune modif en prod
- Plugins via Composer
- Thème enfant versionné
- Déploiement automatisable

Ce repo est la base de tous les sites clients.

```

# security/secrets-management.md

```md
# 🔐 Secrets Management

Ce document décrit la stratégie de gestion des secrets pour les projets basés sur le **starter pack Bedrock**, en local et en production (Infomaniak).

L’objectif est de garantir :

* des **secrets uniques par projet**
* un **maximum d’automatisation raisonnable**
* une **sécurité pragmatique**, compatible avec des hébergements managés

---

## 🎯 Objectifs

* ❌ Aucun secret versionné dans Git
* 🔑 Secrets différents pour chaque projet
* 🔁 Possibilité de rotation (partielle ou totale)
* 🤖 Déploiement automatisé via SSH (Jenkins)
* 🚫 FTP considéré comme accès de secours uniquement

---

## 🧩 Typologie des secrets

| Secret            | Environnement | Rotation | Automatisable |
| ----------------- | ------------- | -------- | ------------- |
| WP Salts          | Local / Prod  | Oui      | Oui           |
| DB password       | Local / Prod  | Oui      | Oui           |
| WP admin password | Local / Prod  | Oui      | Oui           |
| SSH keys          | Local / Prod  | Rare     | Semi          |
| FTP password      | Prod          | Rare     | ❌ Non         |

---

## 🟢 Environnement local

### Génération initiale

Au `make up` du projet :

* génération automatique de :

    * WP salts
    * mot de passe DB
    * mot de passe admin WP
* écriture dans un fichier `.env.local`
* fichier **gitignored**

Un fichier `.env.example` est fourni sans valeurs sensibles.

### Rotation locale

Commande prévue :

\`\`\`bash
make rotate-secrets
\`\`\`

Actions possibles :

* régénération des salts
* changement du mot de passe DB
* mise à jour du mot de passe admin WP

⚠️ Attention : la rotation des salts invalide toutes les sessions utilisateurs.

---

## 🔵 Environnement de production (Infomaniak)

### Contraintes connues

* Pas d’API publique complète pour :

    * FTP
    * SSH users
* Les accès sont créés via le Manager Infomaniak
* Le WordPress *1-click* est proscrit

➡️ Le projet est déployé sur un **serveur vierge**.

---

## 🔐 Modèle de sécurité retenu (Prod)

### Déploiement

* ✅ SSH uniquement
* 🔑 Authentification par clés SSH
* 🤖 Jenkins comme point d’entrée
* 🚫 Aucun déploiement via FTP

### FTP (fallback uniquement)

* Mot de passe extrêmement long
* Utilisation exceptionnelle
* Rotation manuelle uniquement

➡️ Le FTP **n’est pas un secret critique** du système.

---

## 🔁 Rotation des secrets en production

### Base de données (automatisable)

Via SSH :

\`\`\`sql
ALTER USER 'wp_user'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
\`\`\`

* Mise à jour immédiate du `.env.prod`
* Pas de downtime si exécuté correctement

---

### WordPress admin

Via WP-CLI :

\`\`\`bash
wp user update admin --user_pass="new_password"
\`\`\`

* Scriptable
* Intégrable Jenkins

---

### WordPress Salts

* Régénération possible à tout moment
* Mise à jour dans `.env.prod`

⚠️ Invalidation des sessions en cours.

---

## 📁 Stockage des secrets

* `.env.local` / `.env.prod`
* Hors Git
* Droits fichiers restrictifs
* Sauvegardes chiffrées recommandées

---

## 🚀 Jenkins – rôle attendu

* Connexion SSH au serveur
* Déploiement Bedrock
* Création / mise à jour des secrets
* Rotation planifiée (DB / WP)

Le FTP n’intervient **à aucun moment** dans la CI/CD.

---

## 🛣️ Évolutions possibles

* Script de rotation avancé
* Gestion centralisée des secrets (NAS / Vault-like)
* Audit de sécurité périodique

Ces évolutions sont **hors scope du starter pack v1**.

---

## ✅ Conclusion

Cette stratégie offre :

* un excellent compromis sécurité / faisabilité
* une compatibilité totale avec Infomaniak
* une base saine pour évoluer vers des pratiques DevSecOps plus avancées

Le FTP est volontairement marginalisé.
Le SSH et les secrets applicatifs sont les piliers du système.

```

