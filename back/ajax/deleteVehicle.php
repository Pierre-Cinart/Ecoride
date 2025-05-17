<?php
// Chargement de l'autoload et des dépendances
require_once '../composants/autoload.php';
checkAccess(['Driver']); // Autorise uniquement les conducteurs

// Vérifie que l'ID du véhicule est bien transmis en POST
if (!isset($_POST['vehicle_id'])) {
    http_response_code(400);
    exit("ID du véhicule manquant.");
}

// Sécurisation et cast de l'ID
require_once '../composants/sanitizeArray.php';
$vehicleId = getPostInt('vehicle_id');

// Récupération de l'objet user (driver) en session
/** @var Driver $user */
$user = $_SESSION['user'];

try {
    // Exécute la suppression du véhicule via la méthode Driver
    $user->deleteVehicle($pdo, $vehicleId);

    // Succès
    exit("OK");

} catch (Exception $e) {
    // En cas d'erreur, on loggue proprement dans la session
    $_SESSION['error'] = "Erreur suppression véhicule : " . $e->getMessage();
    http_response_code(500);
    exit("Erreur lors de la suppression : " . $e->getMessage());
}
