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

    // 6. Insérer un document permis validé
    $stmtDoc = $pdo->prepare("INSERT INTO documents (user_id, type, file_path, status, submitted_at) VALUES (?, ?, ?, ?, ?)");
    $stmtDoc->execute([
        $driverId1, 'permit', '../back/uploads/test/test.jpg', 'approved', date('Y-m-d H:i:s')
    ]);

    // 7. Insérer un document permis en attente
    $stmtDoc->execute([
        $driverId2, 'permit', '../back/uploads/test/test.jpg', 'pending', date('Y-m-d H:i:s')
    ]);

    $pdo->commit();
    echo "✅ Données injectées avec succès.";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Erreur : " . $e->getMessage();
}
?>
