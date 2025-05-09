<?php
require_once '../back/composants/db_connect.php';

try {
    $pdo->beginTransaction();

    // 1. Créer un admin
    $stmt = $pdo->prepare("INSERT INTO users (pseudo, first_name, last_name, email, password, phone_number, role, is_verified_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'admin', 'José', 'admin', 'admin@ecoride.fr',
        password_hash('mot2passe', PASSWORD_DEFAULT), '0100000001',
        'admin', 1
    ]);

    // 2. Créer un employé
    $stmt->execute([
        'employe1', 'Employé', 'Test', 'employe@ecoride.fr',
        password_hash('mot2passe', PASSWORD_DEFAULT), '0100000002',
        'employee', 1
    ]);

    // 3. Créer un utilisateur simple
    $stmt->execute([
        'user1', 'User', 'Test', 'user@ecoride.fr',
        password_hash('mot2passe', PASSWORD_DEFAULT), '0100000003',
        'user', 1
    ]);

    // 4. Créer un conducteur avec permis validé
    $stmt->execute([
        'driver1', 'Conducteur', 'Valide', 'driver.valide@ecoride.fr',
        password_hash('mot2passe', PASSWORD_DEFAULT), '0100000004',
        'driver', 1
    ]);
    $driverId1 = $pdo->lastInsertId();

    // 5. Créer un conducteur en attente de validation
    $stmt->execute([
        'driver2', 'Conducteur', 'Attente', 'driver.attente@ecoride.fr',
        password_hash('mot2passe', PASSWORD_DEFAULT), '0100000005',
        'driver', 1
    ]);
    $driverId2 = $pdo->lastInsertId();

    // 6. Insérer les documents permis
    $stmtDoc = $pdo->prepare("INSERT INTO documents (user_id, type, file_path, status, submitted_at) VALUES (?, ?, ?, ?, ?)");
    $stmtDoc->execute([
        $driverId1, 'permit', '../back/uploads/test/test.jpg', 'approved', date('Y-m-d H:i:s')
    ]);
    $stmtDoc->execute([
        $driverId2, 'permit', '../back/uploads/test/test.jpg', 'pending', date('Y-m-d H:i:s')
    ]);

    // 7. Ajouter un véhicule pour driver1
    $stmtVeh = $pdo->prepare("INSERT INTO vehicles (user_id, brand, model, color, fuel_type, registration_number, first_registration_date, seats, picture) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtVeh->execute([
        $driverId1,
        'Renault',
        'ZOE',
        'Blanc',
        'electric',
        'AB-123-CD',
        '2023-05-15',
        4,
        '../back/uploads/driver1/vehicule-test.jpg' // ⚠️ Ce fichier doit exister
    ]);
    $vehicleId = $pdo->lastInsertId();

    // 8. Ajouter 3 voyages après le 15 mai 2025 pour driver1
    $stmtTrip = $pdo->prepare("INSERT INTO trips 
    (driver_id, vehicle_id, departure_city, arrival_city, departure_date, departure_time, price, is_ecological, available_seats, status, departure_address, arrival_address) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $trips = [
        ['Lille', 'Paris', '2025-05-16', '08:30:00'],
        ['Paris', 'Lyon', '2025-05-18', '09:30:00'],
        ['Lyon', 'Marseille', '2025-05-20', '10:30:00'],
    ];

    foreach ($trips as $trip) {
        $stmtTrip->execute([
            $driverId1,
            $vehicleId,
            $trip[0], // ville départ
            $trip[1], // ville arrivée
            $trip[2], // date
            $trip[3], // heure
            15.00,
            1,
            3,
            'planned',
            "Adresse de départ à $trip[0]",
            "Adresse d'arrivée à $trip[1]"
        ]);
    }

    $pdo->commit();
    echo "✅ Données injectées avec succès.";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Erreur : " . $e->getMessage();
}
?>
