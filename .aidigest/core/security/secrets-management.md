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

```bash
make rotate-secrets
```

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

```sql
ALTER USER 'wp_user'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
```

* Mise à jour immédiate du `.env.prod`
* Pas de downtime si exécuté correctement

---

### WordPress admin

Via WP-CLI :

```bash
wp user update admin --user_pass="new_password"
```

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
