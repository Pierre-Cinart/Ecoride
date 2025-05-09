<?php
// Inclure la configuration de la base de données
require_once __DIR__ . '/../config/db_config.php';

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);

    // Définir les attributs PDO pour une gestion propre des erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // // Message de succès (pour debug uniquement)
    // echo "✅ Connexion à la base de données réussie.";
} catch (PDOException $e) {
    // Message d'erreur en cas d'échec
    echo "❌ Erreur de connexion : " . $e->getMessage();
}
?>

