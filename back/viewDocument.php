<?php
require_once './composants/autoload.php';
checkAccess(['Admin', 'Employee']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Méthode non autorisée.");
}

if (empty($_POST['image_path'])) {
    die("Chemin d'image non fourni.");
}

// Récupère le chemin de l'image
$relative = $_POST['image_path'];

// Recompose l'URL complète en mode accessible depuis le navigateur
$imageUrl = $webAddress . '/back/' . $relative;

// Redirige vers l'image
header("Location: $imageUrl");
exit;
