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

## Cloner le projet
git clone https://github.com/Pierre-Cinart/Ecoride.git
cd ecoride

## Base de données
Importer le fichier ecoride.sql (structure de la base relationnelle) via phpMyAdmin.

Créer manuellement un fichier non suivi par Git :
/back/config/db_config.php

Exemple de contenu de ce fichier :

<?php $DB_HOST = "localhost"; $DB_NAME = "ecoride"; $DB_USER = "root"; $DB_PASS = ""; ?>
Ignorer ce fichier dans Git
Vérifier que le chemin est bien présent dans le fichier .gitignore :

/back/config/db_config.php

## À venir
Authentification sécurisée via tokens (JWT-like)

Intégration de Google Maps (affichage et calculs d’itinéraires)

Envoi d’e-mails via PHPMailer

Stockage des statistiques avec MongoDB

Mise en place de tests fonctionnels

## Développeur
Projet réalisé par Wampawat dans le cadre de la formation TP Développeur Web & Web Mobile.
Pour toute suggestion ou retour, vous pouvez me contacter via la messagerie du projet.

## Mention importante
Ce site est une maquette pédagogique.
Les systèmes de paiement sont fictifs, et aucune transaction réelle n’est effectuée.

## Licence
Projet libre à usage éducatif.
Toute réutilisation doit mentionner l’auteur.

