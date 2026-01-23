# 🐳 Architecture Docker – WordPress Starter Arxama

Ce document décrit l’architecture Docker du **WordPress Starter Arxama**, son découpage, et les responsabilités de chaque container.

L’objectif est de fournir :

* une vision claire de l’infrastructure locale
* une base cohérente pour la CI/CD
* une architecture proche de la production

---

## 🎯 Principes

* Un container = une responsabilité
* Aucun service "fourre-tout"
* Configuration explicite (pas de magie)
* Séparation stricte HTTP / PHP / Data

---

## 🧱 Vue d’ensemble

```text
Navigateur
   ↓ HTTPS (Traefik – stack dev)
Nginx
   ↓ FastCGI
PHP-FPM (WordPress / Bedrock)
   ↓
MariaDB / Redis
```

En production, Traefik peut être remplacé par un LB ou un reverse-proxy équivalent.

---

## 📦 Containers

### 🧭 Nginx (`docker/nginx`)

**Rôle :**

* serveur HTTP
* gestion des routes
* point d’entrée WordPress

**Responsabilités :**

* servir `/web` (Bedrock)
* rediriger vers `index.php`
* transmettre l’exécution PHP à PHP-FPM

**Pourquoi séparé de PHP ?**

* performance
* sécurité
* cohérence avec les environnements de production

---

### 🐘 PHP-FPM (`docker/php`)

**Base :** `php:8.3-fpm-alpine`

**Responsabilités :**

* exécuter WordPress
* charger Bedrock
* gérer Composer / WP-CLI / scripts

**Extensions installées :**

* pdo / pdo_mysql
* intl
* zip
* opcache

Ce container ne sert **aucun trafic HTTP directement**.

---

### 🗄️ MariaDB (`docker/mariadb`)

**Rôle :**

* base de données WordPress

**Caractéristiques :**

* données persistées via volume Docker
* accès réseau interne uniquement

---

### 🧠 Redis (`docker/redis`)

**Rôle :**

* cache objet
* sessions

**Optionnel** : activé uniquement si nécessaire par le projet.

---

## 🔐 Réseau

Tous les containers communiquent via un réseau Docker interne.

* aucun service data exposé publiquement
* seul Nginx est accessible depuis l’extérieur

---

## ⚙️ Orchestration

* `docker-compose.yml` décrit l’infrastructure
* `Makefile` fournit une UX simple aux développeurs

Exemples :

```bash
make up
make down
make restart
```

---

## 🧠 Pourquoi cette architecture ?

* Proche d’une infra de production réelle
* Facilement déployable via CI/CD
* Debug clair (logs par service)
* Évolutive (ajout API, workers, etc.)

---

## 🏁 Conclusion

Cette architecture Docker est conçue pour être :

* pédagogique
* robuste
* maintenable

Elle constitue une base saine pour tous les projets WordPress Arxama.
