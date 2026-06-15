# StockManager — Système de Gestion de Stock

Application web PHP/PostgreSQL MVC-POO modulaire pour la gestion complète de stock.

## Prérequis

- PHP 8.2+ avec extensions : `pdo`, `pdo_pgsql`, `mbstring`, `json`
- PostgreSQL 16+
- Node.js 18+ (pour compiler Tailwind CSS)
- Apache 2 ou Nginx avec `mod_rewrite` activé

## Installation

### 1. Base de données

```bash
psql -U postgres -c "CREATE DATABASE gestion_stock;"
psql -U postgres -d gestion_stock -f schema.sql
```

Le fichier `schema.sql` crée toutes les tables, triggers, fonctions et insère les données initiales
(catégories clients, groupes, droits, utilisateur admin).

**Compte admin par défaut :**
- Login : `admin`
- Mot de passe : `admin123` *(changer immédiatement en production)*

### 2. Configuration

```bash
cp .env.example .env
```

Éditer `.env` :
```
DB_HOST=localhost
DB_PORT=5432
DB_NAME=gestion_stock
DB_USER=postgres
DB_PASSWORD=votre_mot_de_passe
APP_URL=http://localhost/gestion-stock/public
```

### 3. Compilation Tailwind CSS

Installation des dependances et compilation:
```bash
npm install
npm run build
```

Pour le développement (recompilation automatique) :
```bash
npm run watch
```

**Recommendation:**
Utiliser que le fichier statique(sans installer Taillwind):
```
public/assets/css/app.css
```
Apres compilation en dev avec 
```bash
npm run build
```

### 4. FontAwesome (pour les icones)

Télécharger FontAwesome 6 Free et placer les fichiers comme suit dans :
```
public/assets/fontawesome/
```
├── js/
│   └── all.min.js
└── LICENSE.txt

### 5. Serveur web

**Apache** — DocumentRoot pointant vers `public/` (le `.htaccess` est inclus).

**Nginx** :
```nginx
server {
    root /var/www/gestion-stock/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ { fastcgi_pass unix:/run/php/php8.2-fpm.sock; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; }
}
```

## Structure du projet

```
gestion-stock/
├── app/
│   ├── Core/           ← Database, Router, Controller, Session, Autoloader
│   ├── Modules/        ← Un dossier par module (Auth, Dashboard, ...)
│   └── Shared/         ← Layout, helpers, vues communes
├── config/             ← database.php, app.php
├── public/             ← Point d'entrée (index.php), assets CSS/FA
├── storage/archives/   ← Fichiers XML de la corbeille
└── tailwind.config.js
```

## Phases de développement

| Phase | État    | Contenu |
|-------|---------|---------|
| P0    | ✅ Fait  | Core, Auth, Layout, Dashboard |
| P1    | 🔜 À faire | Module Structure |
| P2    | 🔜 À faire | Module Approvisionnement |
| P3    | 🔜 À faire | Module Vente |
| P4    | ✅ Fait  | Module Utilisateurs |
| P5    | ✅ Fait | Audit + Corbeille XML |

## Identifiants par défaut

| Login | Mot de passe | Groupe |
|-------|-------------|--------|
| admin | admin123    | Administrateur |

⚠️ **Changer le mot de passe admin dès le premier déploiement.**
