# 🤖 AI Digest – Méthode Arxama

Ce document décrit la **méthode officielle Arxama** pour générer des *codebase digests* exploitables par une IA (ChatGPT, Claude, etc.), sans bruit inutile et avec une **maîtrise totale des tokens**.

Cette méthode est principalement destinée à la **phase de développement et de conception du starter pack**, mais peut être réutilisée ponctuellement sur d’autres projets.

---

## 🎯 Objectifs

* Fournir à l’IA **uniquement le code utile**
* Éviter toute pollution par :

    * WordPress core
    * vendor
    * assets lourds
    * code tiers (Elementor, thèmes parents, etc.)
* Réduire drastiquement la taille des prompts
* Obtenir des réponses **précises, pertinentes et actionnables**

---

## 🧠 Principe clé

> **ai-digest n’est pas intelligent sur les inclusions.**
> Il fonctionne très bien **si on contrôle le périmètre par le dossier analysé**.

➡️ La stratégie Arxama repose donc sur :

* des **dossiers temporaires** (`.aidigest/`)
* des **copies ciblées via `rsync`**
* un **digest par intention** (core / theme)

---

## 📦 Pré-requis

À la racine du projet :

* `.aidigestignore` (global, agressif)
* `.aidigestminify` (global)

Ces fichiers ne sont **pas dupliqués** dans les sous-dossiers temporaires.

---

## 🧱 Digest CORE – Infrastructure & Architecture

### 🎯 Contenu

Le digest CORE doit contenir uniquement :

* configuration Bedrock
* documentation
* fichiers de build / setup

Le digest CORE inclut également :
- les Dockerfiles applicatifs
- la configuration Nginx
- docker-compose.yml
- le Makefile

Ces éléments font partie intégrante de l’architecture du starter.

Il sert à analyser :

* l’architecture
* la sécurité
* les workflows
* la CI/CD

---

### 📁 Structure cible

```text
.aidigest/core/
├── config/
├── docs/
├── composer.json
├── composer.lock
├── Makefile
├── docker-compose.yml
├── README.md
```

---

### 🛠️ Commandes utilisées

```bash
rm -rf .aidigest/core
mkdir -p .aidigest/core

rsync -av \
  config/ \
  docs/ \
  composer.json \
  composer.lock \
  Makefile \
  docker-compose.yml \
  README.md \
  .aidigest/core/
```

Puis génération du digest :

```bash
cd .aidigest/core
npx ai-digest --output ../../codebase-wp-starter-core.md
cd -
```

---

### ✅ Résultat attendu

* ~5 à 10 fichiers
* ~3 000 à 10 000 tokens
* Digest très rapide à charger pour l’IA

---

## 🎨 Digest THEME – Code métier & Front

### 🎯 Contenu

Le digest THEME doit contenir **uniquement le code maintenu par l’équipe** :

* thème enfant
* widgets Elementor custom
* logique PHP métier
* CSS / JS écrits sur mesure

🚫 Sont exclus volontairement :

* thème parent (`hello-elementor`)
* vendor
* assets compilés tiers

---

### 📁 Structure cible

```text
.aidigest/theme/
└── theme/
    ├── functions.php
    ├── style.css
    ├── Model/
    ├── Theme/
    ├── Assets/
```

---

### 🛠️ Commandes utilisées

```bash
rm -rf .aidigest/theme
mkdir -p .aidigest/theme/theme

rsync -av \
  web/app/themes/arxama-child/ \
  .aidigest/theme/theme/
```

Puis génération du digest :

```bash
cd .aidigest/theme
npx ai-digest --output ../../codebase-wp-starter-theme.md
cd -
```

---

### ✅ Résultat attendu

* ~10 à 30 fichiers
* ~5 000 à 15 000 tokens
* Réponses IA ciblées sur **le vrai code métier**

---

## 🧹 Nettoyage

Les dossiers temporaires peuvent être supprimés après usage :

```bash
rm -rf .aidigest
```

Ou ajoutés au `.gitignore`.

---

## 🧠 Bonnes pratiques Arxama

* 1 digest = 1 intention
* Toujours exclure le code tiers
* Ne jamais donner WordPress core à l’IA
* Préférer plusieurs petits digests à un gros

---

## 🏁 Conclusion

Cette méthode permet :

* une collaboration fluide avec l’IA
* une compréhension rapide du projet
* une réduction massive du bruit et des tokens

Elle est idéale pendant la **phase de développement du starter pack**, et reste disponible comme outil ponctuel sur d’autres projets lorsque nécessaire.

---

## 🧭 Quel digest utiliser ?

### codebase-wp-starter-core.md
À utiliser pour :
- architecture Bedrock
- configuration WordPress
- sécurité
- Docker / Traefik
- CI/CD, Jenkins, déploiement

❌ Ne pas utiliser pour des questions de front ou de thème.

---

### codebase-wp-starter-theme.md
À utiliser pour :
- développement du thème
- widgets Elementor custom
- PHP métier
- CSS / JS du thème

❌ Ne contient volontairement aucun code tiers (Elementor, WordPress core).
