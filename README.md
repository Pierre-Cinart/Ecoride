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

## Créer un compte admin 
mettez le contenu du fichier /database/.htacess en commentaire
configurez vos nom  d utilisateur et mot de passe dans le fichier : database/CreateAdmin.php'
exemples : 
    $pseudo = 'admin';
    $firstName = 'José';
    $lastName = 'Admin';
    $email = 'admin@ecoride.fr';
    $phone = '0101010101';
    $role = 'admin';
    $password = 'Mot2Passe'; 

puis rendez vous à l ' adresse 'VotreSite/database/CreateAdmin.php' pour executer le php
vous pouvez maintenant vous connecter en tant qu administrateur

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

