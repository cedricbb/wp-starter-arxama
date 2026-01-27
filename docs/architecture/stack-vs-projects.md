# 🧱 Architecture Arxama — Stack Dev vs Starter Pack vs Projets

## 🎯 Objectif

L’architecture Arxama vise à fournir une **plateforme de développement locale robuste**, proche de la production, tout en garantissant :

- une séparation claire des responsabilités
- zéro conflit entre projets
- une maintenance simple
- une montée en charge maîtrisée

Cette architecture repose sur **trois niveaux distincts**.

---

## 1️⃣ La Stack Dev Arxama (Infrastructure)

### 🎯 Rôle

La **stack-dev** est la **couche infrastructure locale**.  
Elle fournit des services **partagés**, persistants et stables.

👉 Elle est lancée **une fois par machine**, pas par projet.

---

### 🧩 Services gérés par la stack-dev

La stack-dev est **la seule** responsable de :

- Traefik (reverse-proxy, HTTPS)
- Certificats SSL wildcard (`*.arxama.local`)
- MariaDB (base de données)
- PostgreSQL
- Redis
- Mailhog
- PhpMyAdmin
- PgAdmin
- Réseau Docker `backend`

---

### 🗄️ Gestion de la base de données

- **Une seule MariaDB**
- **Un seul volume Docker**
- Les données sont persistantes
- Aucune DB n’est liée à un projet Docker

```text
MariaDB
└── volume: mariadb:/var/lib/mysql
```

👉 La base de données est une ressource d’infrastructure, pas applicative.

---

### 🔐 Sécurité

- Le mot de passe root MariaDB :
  - est défini dans la stack-dev 
  - n’est jamais versionné 
  - n’est jamais stocké dans les projets
- Les projets n’ont aucun privilège infra

---

## 2️⃣ Le Starter Pack WordPress Arxama

### 🎯 Rôle

Le starter-pack est un socle projet.

Il définit :
- la structure WordPress (Bedrock)
- les conventions techniques
- les scripts d’initialisation
- les outils de développement

👉 Il ne fournit aucune infrastructure.

---

### ❌ Ce que le starter-pack ne fait PAS

Le starter-pack ne doit jamais :

- lancer MariaDB 
- définir de volumes de base de données 
- gérer des containers infra 
- contenir des secrets d’infrastructure 
- exposer des ports

---

### ✅ Ce que le starter-pack fait

- Générer les secrets projet (.env, .env.local)
- Créer automatiquement :
  - la base de données 
  - l’utilisateur DB 
  - les droits
- Lancer les containers applicatifs :
  - PHP 
  - Nginx 
  - Redis (si nécessaire)
  - Installer les dépendances (Composer)

---

### 🔑 Relation à la base de données

Le starter-pack :
- consomme MariaDB via le réseau Docker backend 
- se connecte via :

    ```env
    DB_HOST=mariadb
    ```

- crée logiquement sa base, sans toucher au volume

---

## 3️⃣ Les Projets WordPress

### 🎯 Rôle

Un projet WordPress est :
- un dépôt Git indépendant
- une instance applicative isolée 
- un consommateur de la stack-dev

---

### 🧠 Principes clés

- Un projet = un sous-domaine

    ```text
    myproject.arxama.local
    ```

- Un projet = une base de données dédiée 
- Aucun port exposé 
- Aucun certificat SSL géré côté projet

---

### 🐳 Docker côté projet

Un projet Docker :
- ne contient pas de MariaDB 
- ne contient pas de volumes DB 
- se connecte au réseau backend 
- délègue HTTPS à Traefik

---

## 4️⃣ Pourquoi cette architecture

### ❌ Architecture rejetée

- 1 MariaDB par projet 
- volumes dynamiques par projet 
- DB embarquée dans les docker-compose projets

➡️ génère :
- conflits 
- volumes orphelins 
- comportements imprévisibles 
- complexité inutile

---

### ✅ Architecture retenue

- Infrastructure centralisée 
- Projets légers 
- DB logique plutôt que DB physique 
- Séparation stricte des responsabilités

---

### 🏁 Conclusion

- La stack-dev possède l’infrastructure 
- Les projets possèdent leur logique applicative

Cette règle est fondamentale dans l’écosystème Arxama.

Toute évolution future (CI, provisioning, prod, cloud) repose sur cette séparation.