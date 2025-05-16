<?php
// ======================================================
// confirmAddTrip.php — Back-end pour l'ajout de trajet
// ======================================================

// Chargement des composants essentiels (connexion, session, vérifs, sécurités...)
require_once './composants/autoload.php';

// Vérifie que seul un conducteur peut accéder à cette fonctionnalité
checkAccess(['Driver']);

// Vérifie que le formulaire a bien été envoyé en méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/account.php');
    exit();
}

// Nettoyage des données du formulaire avec la fonction sanitizeArray()
$_POST = sanitizeArray($_POST, '../front/driver/addTrip.php');

// Vérifie que l'utilisateur est bien connecté en tant que conducteur
if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof Driver)) {
    $_SESSION['error'] = "Seul un conducteur peut proposer un trajet.";
    header('Location: ../front/user/login.php');
    exit();
}

$user = $_SESSION['user'];

// Récupération et validation des champs (ville, adresse, date, heure, etc.)
$tripData = [
    'departure_city'     => $_POST['departure_city'] ?? null,
    'departure_address'  => $_POST['departure_address'] ?? null,
    'arrival_city'       => $_POST['arrival_city'] ?? null,
    'arrival_address'    => $_POST['arrival_address'] ?? null,
    'departure_date'     => $_POST['departure_date'] ?? null,
    'departure_time'     => $_POST['departure_time'] ?? null,
    'vehicle_id'         => getPostInt('vehicle_id'),
    'available_seats'    => getPostInt('available_seats'),
    'price'              => getPostInt('price'),
    'estimated_duration' => getPostInt('estimated_duration')
];

// Vérifie qu'aucune donnée essentielle n'est manquante
foreach ($tripData as $key => $value) {
    if ($value === null || $value === '') {
        $_SESSION['error'] = "Champ manquant ou invalide : " . str_replace('_', ' ', $key);
        header('Location: ../front/driver/addTrip.php');
        exit();
    }
}

try {
    // Appel de la méthode de l’objet Driver pour proposer un trajet
    $user->proposeTrip($pdo, $tripData);

    // Rafraîchit les données en session (pour mise à jour des crédits, etc.)
    $user->updateUserSession($pdo);
    $_SESSION['user'] = $user;

    // Redirection avec message de succès
    $_SESSION['success'] = "Votre trajet a été ajouté avec succès.";
    header('Location: ../front/user/account.php');
    exit();

} catch (Exception $e) {
    // Gestion des erreurs (message personnalisé ou générique)
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../front/driver/addTrip.php');
    exit();
}
