<?php
require_once './composants/autoload.php';
checkAccess(['Driver']);

$driver = $_SESSION['user'];

try {
    // 1. Nettoyage des données
    $data = sanitizeArray($_POST);

    $brand    = $data['brand'] ?? '';
    $model    = $data['model'] ?? '';
    $fuelType = $data['fuel_type'] ?? '';
    $registrationNumber = $data['registration_number'] ?? '';
    $firstDate = $data['first_registration_date'] ?? date('Y-m-d');
    $seats    = getPostInt('seats');
    $color    = $data['color'] ?? 'non précisée';

    // 2. Conversion du carburant FR → EN pour la base de données
    $fuelType = match ($fuelType) {
        'essence'    => 'gasoline',
        'diesel'     => 'diesel',
        'electrique' => 'electric',
        'hybride'    => 'hybrid',
        default      => null
    };
    $data['fuel_type'] = $fuelType;

    // 3. Vérifications côté serveur
    $errors = "";
    if (empty($brand))  $errors .= "Marque manquante. ";
    if (empty($model))  $errors .= "Modèle manquant. ";
    if (empty($fuelType))  $errors .= "Type de carburant invalide. ";
    if ($seats <= 0 || $seats > 6) $errors .= "Nombre de places invalide. ";
    if (empty($registrationNumber)) $errors .= "Numéro d’immatriculation manquant. ";
    if ($firstDate > date('Y-m-d')) $errors .= "La date de mise en circulation ne peut pas être dans le futur. ";

    if (empty($_FILES['registration_document']['tmp_name'])) {
        $errors .= "Carte grise manquante. ";
    }

    if (empty($_FILES['insurance_document']['tmp_name'])) {
        $errors .= "Assurance manquante. ";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = trim($errors);
        header('Location: ../front/driver/addVehicle.php');
        exit;
    }

    // 4. Vérification de l'unicité de l'immatriculation
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE registration_number = :reg");
    $stmt->execute([':reg' => $registrationNumber]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $_SESSION['error'] = "Un véhicule avec ce numéro d'immatriculation existe déjà.";
        header('Location: ../front/driver/addVehicle.php');
        exit;
    }

    // 5. Tout est OK → Ajout du véhicule
    $driver->addVehicle($pdo, $data, $_FILES);

    // 6. Succès
    $_SESSION['success'] = "Votre véhicule a bien été ajouté. Il sera vérifié par nos administrateurs.";
    header('Location: ../front/user/account.php');
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: ../front/driver/addVehicle.php');
    exit;
}
