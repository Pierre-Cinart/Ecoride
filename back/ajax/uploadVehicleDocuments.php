<?php
require_once '../composants/autoload.php';

if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof Driver)) {
    echo "Accès non autorisé.";
    exit;
}

$driver = $_SESSION['user'];


$vehicleId = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
if (!$vehicleId) {
    echo "ID de véhicule invalide.";
    exit;
}

try {
    $vehicle = new Vehicle($pdo, $vehicleId);

    // Vérifie que l'utilisateur est bien le propriétaire du véhicule
    if ($vehicle->getOwner() !== $driver->getId()) {
        $_SESSION['error'] = "Vous ne pouvez pas modifier ce véhicule.";
        exit;
    }

    $success = false;

    // Upload des fichiers un par un si envoyés
    if (!empty($_FILES['registration_document']['tmp_name'])) {
        $vehicle->uploadDocument('registration', $_FILES['registration_document']);
        $success = true;
    }

    if (!empty($_FILES['insurance_document']['tmp_name'])) {
        $vehicle->uploadDocument('insurance', $_FILES['insurance_document']);
        $success = true;
    }

    if (!empty($_FILES['picture_document']['tmp_name'])) {
        $vehicle->uploadDocument('picture', $_FILES['picture_document']);
        $success = true;
    }

    if (!$success) {
        $_SESSION["success"] = "Aucun document à traiter.";
        exit;
    }

    
    exit("OK");

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
