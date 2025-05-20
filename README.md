## EcoRide – Plateforme de covoiturage écologique

EcoRide est une application web visant à promouvoir le covoiturage écologique à travers une interface moderne, intuitive et accessible.  
Développée dans le cadre du TP Développeur Web & Web Mobile, elle propose différentes fonctionnalités selon le rôle de l'utilisateur : passager, conducteur, employé ou administrateur.

## Fonctionnalités principales

- Recherche et réservation de trajets
- Espace utilisateur personnalisé
- Création et gestion de trajets en tant que conducteur
- Système d'avis et de notation
- Interface d'administration et de gestion
- Statistiques et crédits virtuels
- Intégration de Leaflet.js pour l'affichage des cartes
- Utilisation d'OpenRouteService (ORS) pour le calcul d'itinéraires
- Paiement factice et gestion de crédits
- Téléversement de permis (vérification manuelle par l’équipe)
- Envoi de mails
- Autocomplétion des villes via Geo API

## Arborescence du projet (simplifiée avec commentaires)

```
Ecoride/
├── index.html                     ← Page d'accueil temporaire
├── README.md                      ← Fichier de documentation
├── back/                          ← Traitements serveur (PHP)
│   ├── ajax/                      ← Fichiers appelés en JS (AJAX)
│   ├── api/                       ← Requêtes API internes sécurisées
│   ├── classes/                   ← Définition des classes utilisateurs, véhicules...
│   ├── composants/                ← Fonctions réutilisables (autoloader, sanitize, PHPMailer...)
│   ├── config/                    ← Fichiers de configuration (DB, captcha, ORS, Firebase)
│   ├── uploads/                   ← Dossiers contenant les fichiers envoyés par les utilisateurs
│   └── *.php                      ← Actions principales : connexion, inscription, trajets...
├── database/                      ← Scripts pour initialiser la base et injecter des données
│   └── ecoride.sql                ← Structure MySQL
│   └── createUsers.php            ← Injection des comptes de test
├── front/                         ← Pages visibles par l'utilisateur
│   ├── admin/                     ← Pages de gestion pour l'administrateur
│   ├── driver/                    ← Interface spécifique aux conducteurs
│   ├── employee/                  ← Interface pour les employés (validation, gestion)
│   ├── user/                      ← Espace personnel des utilisateurs classiques
│   └── *.php/html                 ← Pages transversales (accueil, debug, mise à jour session)
```

## Installation & configuration

### Prérequis

- Serveur local (XAMPP, WAMP ou Laragon)
- PHP 8+
- MySQL 8+
- phpMyAdmin (recommandé)
- Git
- Compte Google (reCAPTCHA v3 requis)
- PHPMailer
- MailHog (pour tester les mails en local)

## Cloner le projet

```bash
git clone https://github.com/Pierre-Cinart/Ecoride.git
cd ecoride
```

## Base de données relationnelle

Importer le fichier `ecoride.sql` via phpMyAdmin.

### Fichiers de configuration requis

#### /back/config/db_config.php

```php
<?php 
$DB_HOST = "votre adresse de site"; 
$DB_NAME = "nom de votre base de données"; 
$DB_USER = "nom d'utilisateur"; 
$DB_PASS = "votre mot de passe"; 
?>
```

#### /back/config/configCaptcha.php

```php
<?php
$RECAPTCHA_PUBLIC_KEY = 'votre clé publique';
$RECAPTCHA_PRIVATE_KEY = 'votre clé privée';
?>
```

#### /back/config/configORS.php

```php
<?php 
$OPEN_ROUTE_KEY = 'votre_clé_ORS';
?>
```

#### Fichiers à ignorer dans Git :

```
/back/config/db_config.php
/back/config/configCaptcha.php
/back/config/configORS.php
/back/config/configAnalytics.php
/back/config/service-account.json
```

## Google Analytics – Statistiques de fréquentation

Le projet utilise **Google Analytics 4** pour afficher les statistiques de visites hebdomadaires (via l'API Analytics Data).

### 📌 Prérequis

Si vous souhaitez activer les statistiques de visite sur votre version du projet, vous devez configurer votre propre compte Google Analytics.

### 🔐 Étapes de configuration

1. Créez un compte sur [Google Analytics](https://analytics.google.com/)
2. Créez une propriété GA4
3. Activez l'API \"Google Analytics Data API v1\" dans Google Cloud Console
4. Générez une clé de service (fichier `.json`)
5. Placez-la ici :

```
/back/config/service-account.json
```

> Le fichier doit être nommé exactement **`service-account.json`**

6. Créez le fichier :

```
/back/config/configAnalytics.php
```

Contenu :

```php
<?php
$ANALYTICS_KEY = 'VOTRE_CLE_ANALYTICS';
$AUTDOMAIN = 'votre-domaine.firebaseapp.com';
$PROJECT_ID = 'votre-id-projet';
$STORAGE_BUCKET = 'votre-bucket.appspot.com';
$MESSAGE_ID_SENDER = 'votre-message-id';
$APP_ID = 'votre-app-id';
$MESUREMENT_ID = 'votre-id-mesure';
$PROPRIETY_ID = 'votre-property-id-GA4';
?>
```

> Les deux fichiers doivent être dans `.gitignore`

### ⚠️ Important

Sans ces fichiers, les graphiques de visite ne pourront pas fonctionner.

## Services tiers utilisés

### 🌍 OpenRouteService (ORS)

ORS est utilisé pour **le calcul d'itinéraires** dans les trajets.  
Créer un compte : https://openrouteservice.org/

Configurer :

```
/back/config/configORS.php
```

```php
<?php 
$OPEN_ROUTE_KEY = 'votre_clé_ORS';
?>
```

### ✅ Google reCAPTCHA v3

reCAPTCHA protège les formulaires du site.  
Créer une clé publique + privée : https://www.google.com/recaptcha/admin/

Configurer :

```
/back/config/configCaptcha.php
```

```php
<?php
$RECAPTCHA_PUBLIC_KEY = 'votre_clé_publique';
$RECAPTCHA_PRIVATE_KEY = 'votre_clé_privée';
?>
```

### 📊 Autres bibliothèques utilisées

- **Chart.js** : affichage des statistiques (visites, trajets, crédits…)
- **Leaflet.js** : cartes interactives (trajets)
- **Geo API** : autocomplétion des villes (https://api.gouv.fr/api/geo)

Aucune clé n’est requise pour Chart.js ou Geo API.

## Injecter des comptes de test

### 🔐 1. Désactiver `.htaccess` temporairement

Si présent dans `/database/`, commentez son contenu.

### ⚙️ 2. Modifier le fichier createUsers.php

Changez les pseudos, emails ou mots de passe si besoin.

### 🚀 3. Lancer l’injection

Accéder à :

```
http://VotreSite/database/createUsers.php
```

### 👤 Comptes créés

- Admin
- Employé
- Utilisateur simple
- Conducteur validé
- Conducteur en attente

> Le fichier `/back/uploads/test/test.jpg` doit exister pour simuler un permis.

## Envoi d’e-mails avec PHPMailer et MailHog

### 🔧 Mode local

- Télécharger MailHog : https://github.com/mailhog/MailHog/releases
- Lancer `MailHog.exe`
- Accéder à : http://localhost:8025

Configurer dans `/back/config/db_config.php` :

```php
$onLine = false;
$webAddress = 'http://localhost/nom-du-site';
```

### 🌐 Mode production

```php
$onLine = true;
$webAddress = 'https://votre-domaine.fr';
```

Configurer le SMTP réel dans `sendMail.php`.

## À venir

- Authentification sécurisée via JWT-like
- Intégration de la messagerie Firebase
- Système de badges
- Pages publiques pour les trajets

## Développeur

Projet réalisé par **Pierre Cinart** dans le cadre de la formation **TP Développeur Web & Web Mobile**.

## Mention importante

Ce site est une maquette pédagogique.  
Aucun paiement réel n’est effectué.

## Licence

Projet libre à usage éducatif.  
Toute réutilisation doit mentionner l’auteur.
