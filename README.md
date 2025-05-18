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
- Intégration (prévue) de Google Maps pour les itinéraires
- Paiement factice et gestion de crédits
- Téléversement de permis (vérification manuelle par l’équipe)
- Envoi de mails

## Arborescence du projet (extrait)

## Arborescence du projet (mise à jour)

```
## Arborescence du projet (simplifiée avec commentaires)

```
Ecoride/
├── index.html                     ← Page d'accueil temporaire
├── README.md                      ← Fichier de documentation
├── back/                          ← Traitements serveur (PHP)
│   ├── ajax/                      ← Fichiers appelés en JS (AJAX)
│   ├── api/                       ← Requêtes API internes sécurisées (à venir)
│   ├── classes/                   ← Définition des classes utilisateurs, véhicules...
│   ├── composants/                ← Fonctions réutilisables (autoloader, sanitize, PHPMailer...)
│   ├── config/                    ← Fichiers de configuration (DB, captcha, ORS, MongoDB)
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
- Compte Google (reCAPTCHA v3 requis : https://developers.google.com/recaptcha/docs/v3?hl=fr)
- PHPMailer
- MailHog pour les tests de mails (recommandé)

## Cloner le projet

```bash
git clone https://github.com/Pierre-Cinart/Ecoride.git
cd ecoride
```

## Base de données relationnelle

Importer le fichier `ecoride.sql` (structure de la base MySQL) via phpMyAdmin.

Créer manuellement les fichiers non suivis par Git :

### (config de la connexion à la base de données)

/back/config/db_config.php

```php
<?php 
$DB_HOST = "votre adresse de site"; 
$DB_NAME = "nom de votre base de données"; 
$DB_USER = "nom d'utilisateur"; 
$DB_PASS = "votre mot de passe"; 
?>
```

### (config Google reCAPTCHA)

/back/config/configCaptcha.php

```php
<?php
$RECAPTCHA_PUBLIC_KEY = 'votre clé publique';
$RECAPTCHA_PRIVATE_KEY = 'votre clé privée';
```

### Vérifiez que les chemins suivants sont bien dans `.gitignore` :

```
/back/config/db_config.php
/back/config/configCaptcha.php
```

## Base de données NoSQL avec MongoDB Atlas

Le projet utilise **MongoDB Atlas** comme base de données NoSQL pour certaines fonctionnalités (statistiques, messagerie interne...).

- La base est hébergée sur **MongoDB Atlas Free Tier**
- Les données sont accessibles via la **Data API** fournie par MongoDB
- Les identifiants d'accès (clé API + URL de l'app) sont stockés dans :

`/back/config/mongodb_config.php`

```php
<?php
$MONGODB_API_KEY = 'votre_clé_api';
$MONGODB_ENDPOINT = 'https://data.mongodb-api.com/app/...';
$MONGODB_DATABASE = 'ecoride_db';
```

⚠️ Ce fichier est ignoré par Git (voir `.gitignore`).

### Reproduire la base MongoDB (pour test ou évaluation)

1. Créez un compte MongoDB Atlas : https://www.mongodb.com/cloud/atlas
2. Créez un cluster gratuit (M0)
3. Activez la Data API via App Services
4. Générez votre propre clé API + URL
5. Créez le fichier `/back/config/mongodb_config.php` comme montré ci-dessus

Toutes les instructions sont également disponibles dans `README.md`.  
Si vous souhaitez injecter des documents de test, utilisez l'import JSON depuis l'interface Atlas ou un script local.

## Injecter des comptes de test (Admin, Employé, Utilisateur, Conducteurs)

### 🔐 1. Désactiver temporairement la protection .htaccess

Si vous avez placé un fichier `.htaccess` dans le dossier `/database/`, commentez temporairement son contenu (ajoutez `#` en début de ligne) afin de pouvoir exécuter le script PHP.

### ⚙️ 2. Modifier les comptes à injecter (facultatif)

Le fichier `/database/CreateUsers.php` injecte automatiquement plusieurs comptes de démonstration (admin, employé, utilisateurs, conducteurs…).

Si vous souhaitez changer les pseudos, emails ou mots de passe, vous pouvez modifier directement les blocs de création dans ce fichier.

### 🚀 3. Exécuter le script

Accédez à l’URL suivante dans votre navigateur (depuis localhost ou votre hébergement) :

```
http://VotreSite/database/CreateUsers.php
```

Vous verrez un message de confirmation si les données ont bien été injectées.

### 👤 Comptes créés automatiquement

- 1 administrateur  
- 1 employé  
- 1 utilisateur simple  
- 1 conducteur avec permis validé  
- 1 conducteur avec permis en attente  

⚠️ Le fichier `test.jpg` doit être présent dans le dossier suivant pour simuler un permis :

```
/back/uploads/test/test.jpg
```

Une fois terminé, remettez en place la protection `.htaccess` du dossier `/database/` pour empêcher toute réexécution ou accès non autorisé.

Les mots de passe sont automatiquement hachés avec `password_hash()` avant d'être enregistrés.

## Envoi d’e-mails avec PHPMailer et MailHog

Le projet utilise PHPMailer pour gérer l’envoi des e-mails (confirmation d’inscription, renouvellement de lien de vérification...).

### Mode local avec MailHog

Pour tester l’envoi de mails en local :

1. Téléchargez et lancez MailHog :  
   https://github.com/mailhog/MailHog/releases

2. Lancez l’exécutable `MailHog.exe`.  
   L’interface de réception des e-mails est accessible via :  
   http://localhost:8025

### Configuration du mode local

Dans le fichier `/back/config/db_config.php`, assurez-vous que la variable suivante est bien définie :

```php
$onLine = false; // indique qu'on est en local
$webAddress = 'http://localhost/nom-du-site';
```

PHPMailer enverra alors les e-mails vers MailHog via `localhost:1025`.

### Mode production (en ligne)

Pour passer en ligne, mettez simplement :

```php
$onLine = true;
$webAddress = 'https://votre-domaine.fr';
```

Dans ce mode, configurez également les paramètres SMTP réels dans le fichier `sendMail.php`, section `else`.

Le lien d’activation ou de réinitialisation sera automatiquement généré avec `$webAddress` comme base, ce qui garantit un fonctionnement correct en local comme en production.

## À venir

- Authentification sécurisée via tokens (JWT-like)
- Intégration de Google Maps (affichage et calculs d’itinéraires)
- Envoi d’e-mails via PHPMailer
- Stockage des statistiques avec MongoDB (via Data API)
- Mise en place de tests fonctionnels

## Développeur

Projet réalisé par **Pierre Cinart** dans le cadre de la formation **TP Développeur Web & Web Mobile**.  
Pour toute suggestion ou retour, vous pouvez me contacter via la messagerie du projet.

## Mention importante

Ce site est une maquette pédagogique.  
Les systèmes de paiement sont fictifs, et aucune transaction réelle n’est effectuée.

## Licence

Projet libre à usage éducatif.  
Toute réutilisation doit mentionner l’auteur.
