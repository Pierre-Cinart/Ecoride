<?php
// Chargements dépendances et vérications + session_start()
require_once '../../back/composants/autoload.php';

checkAccess(['Driver']); // Vérifie que c’est bien un conducteur

$_SESSION['testajax'] = "passage ajax";
// Récupération du véhicule depuis POST
if (!isset($_POST['vehicle_id'])) {
    http_response_code(400);
    exit('ID du véhicule manquant');
}

//nettoyage et caste de l id du vehicule -- fonction de '../composants/sanityzeArray.php '
$vehicleId = getPostInt('vehicle_id');

$user = $_SESSION['user'];

try {
    $user->deleteVehicule($pdo, $vehicleId);   // Appelle ta méthode
    $user->updateUserSession($pdo);            // Met à jour la session
    exit();
} catch (Exception $e) {
    http_response_code(500);
    $_SESSION['error'] = "'Erreur : ' . $e->getMessage()";
    exit('OK');
}
