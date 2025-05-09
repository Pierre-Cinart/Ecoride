## EcoRide – Plateforme de covoiturage écologique
EcoRide est une application web visant à promouvoir le covoiturage écologique à travers une interface moderne, intuitive et accessible.
Développée dans le cadre du TP Développeur Web & Web Mobile, elle propose différentes fonctionnalités selon le rôle de l'utilisateur : passager, conducteur, employé ou administrateur.

## Fonctionnalités principales
Recherche et réservation de trajets

Espace utilisateur personnalisé

Création et gestion de trajets en tant que conducteur

Système d'avis et de notation

Interface d'administration et de gestion

Statistiques et crédits virtuels

Intégration (prévue) de Google Maps pour les itinéraires

Paiement factice et gestion de crédits

Téléversement de permis (vérification manuelle par l’équipe)

Envoie de mails 

Arborescence du projet (extrait)
/back/
├── composants/
│ └── db_connect.php ← Fichier de connexion à la BDD
├── config/
│ └── db_config.php ← ⚠️ Fichier ignoré par Git (.gitignore)
└── api/ ← À venir pour les requêtes sécurisées

/front/
├── css/ ← Feuilles de style globales et spécifiques
├── img/ ← Images & icônes
├── user/ ← Pages accessibles aux utilisateurs classiques
├── driver/ ← Pages spécifiques aux conducteurs
├── admin/ ← Interface administrateur
└── employee/ ← Interface employés

/composants/
└── navbar.php, footer.html ← Composants réutilisables

## Installation & configuration
Prérequis
Serveur local (XAMPP, WAMP ou Laragon)

PHP 8+

MySQL 8+

phpMyAdmin (recommandé)

Git

Compte googleRECAPTCHA requiert l obtention clé V3 (https://developers.google.com/recaptcha/docs/v3?hl=fr)

phpMailer 

mailhog pour les teste de mailing (recommandé)

## Cloner le projet
git clone https://github.com/Pierre-Cinart/Ecoride.git
cd ecoride

## Base de données
Importer le fichier ecoride.sql (structure de la base relationnelle) via phpMyAdmin.

Créer manuellement les fichiers  non suivis par Git :
( config de la connexion à la base de données )
/back/config/db_config.php

<?php 
$DB_HOST = "votre addresse de site"; 
$DB_NAME = "nom de votre base de données"; 
$DB_USER = "nom d utilisateur"; 
$DB_PASS = "votre mot de passe"; ?>

( config google recaptcha )
/back/config/configCaptcha.php
<?php
// Fichier config pour clé google captcha
$RECAPTCHA_PUBLIC_KEY = 'votre clé publique';
$RECAPTCHA_PRIVATE_KEY ='votre clé privée';

Ignorer ces fichiers dans Git
Vérifier que les chemin soeint bien présents dans le fichier .gitignore :

/back/config/db_config.php

## Injecter des comptes de test (Admin, Employé, Utilisateur, Conducteurs)
Pour tester facilement votre site avec plusieurs types d’utilisateurs (admin, employé, utilisateur simple, conducteur avec permis validé ou en attente), suivez les étapes suivantes :

🔐 1. Désactiver temporairement la protection .htaccess
Si vous avez placé un fichier .htaccess dans le dossier /database/, commentez temporairement son contenu (ajoutez # en début de ligne) afin de pouvoir exécuter le script PHP.

⚙️ 2. Modifier les comptes à injecter (facultatif)
Le fichier /database/CreateUsers.php injecte automatiquement plusieurs comptes de démonstration (admin, employé, utilisateurs, conducteurs…).

Si vous souhaitez changer les pseudos, emails ou mots de passe, vous pouvez modifier directement les blocs de création dans le fichier CreateUsers.php.

🚀 3. Exécuter le script
Accédez à l’URL suivante dans votre navigateur (depuis localhost ou votre hébergement) :

arduino
Copier
Modifier
http://VotreSite/database/CreateUsers.php
Vous verrez un message de confirmation si les données ont bien été injectées.

👤 Comptes créés automatiquement
1 administrateur

1 employé

1 utilisateur simple

1 conducteur avec permis validé

1 conducteur avec permis en attente

⚠️ Le fichier test.jpg doit être présent dans le dossier suivant pour simuler un permis :

/back/uploads/test/test.jpg

Une fois terminé, remettez en place la protection .htaccess du dossier /database/ pour empêcher toute réexécution ou accès non autorisé.

Les mots de passe sont automatiquement hachés avec password_hash() avant d'être enregistrés.

Envoi d’e-mails avec PHPMailer et MailHog
Le projet utilise PHPMailer pour gérer l’envoi des e-mails (confirmation d’inscription, renouvellement de lien de vérification...).

Mode local avec MailHog
Pour tester l’envoi de mails en local :

Télécharge et lance MailHog :
https://github.com/mailhog/MailHog/releases
Lance l’exécutable MailHog.exe. L’interface de réception des e-mails est accessible via :
http://localhost:8025

Configuration du mode local :
Dans le fichier /back/config/db_config.php, assurez-vous que la variable suivante est bien définie :

$onLine = false; // indique qu'on est en local
$webAddress = 'http://localhost/nom du site';
PHPMailer enverra alors les e-mails vers MailHog via localhost:1025.

Mode production (en ligne)
Pour passer en ligne, mettez simplement :


$onLine = true;
$webAddress = 'https://votre-domaine.fr';
Dans ce mode, configurez également les paramètres SMTP réels dans le fichier sendMail.php, section else.

Le lien d’activation ou de réinitialisation sera automatiquement généré avec $webAddress comme base, ce qui garantit un fonctionnement correct en local comme en production.


## À venir
Authentification sécurisée via tokens (JWT-like)

Intégration de Google Maps (affichage et calculs d’itinéraires)

Envoi d’e-mails via PHPMailer

Stockage des statistiques avec MongoDB

Mise en place de tests fonctionnels

## Développeur
Projet réalisé par Pierre Cinart dans le cadre de la formation TP Développeur Web & Web Mobile.
Pour toute suggestion ou retour, vous pouvez me contacter via la messagerie du projet.

## Mention importante
Ce site est une maquette pédagogique.
Les systèmes de paiement sont fictifs, et aucune transaction réelle n’est effectuée.

## Licence
Projet libre à usage éducatif.
Toute réutilisation doit mentionner l’auteur.

