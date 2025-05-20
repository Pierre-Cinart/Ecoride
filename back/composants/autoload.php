<?php

require_once __DIR__ .'/loadClasses.php'; // classes
require_once __DIR__ . '/db_connect.php'; // connection bdd
require_once __DIR__ . '/JWT.php'; //jwt
require_once __DIR__ . '/checkAccess.php'; //control d accés
require_once __DIR__ . '/sanitizeArray.php'; // nettoyage des données
require_once __DIR__ . '/captcha.php'; // googleRecaptcha
require_once __DIR__ . '/antiflood.php';  // protection brute force 
require_once dirname(__DIR__). '/config/configAnalytics.php'; // pour le chemin de goog

session_start();

checkToken($pdo);
?> 