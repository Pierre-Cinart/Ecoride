<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Début du fichier<br>";

// Teste si le fichier existe
$path = __DIR__ . '/back/composants/db_connect.php';

if (file_exists($path)) {
    echo "Fichier trouvé, on l’inclut maintenant...<br>";
    include $path;
    echo "<br>Fichier inclus avec succès.";
} else {
    echo "❌ Le fichier n’existe pas : $path";
}
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "<br>✅ Connexion à la base réussie, $count utilisateurs trouvés.";
} catch (PDOException $e) {
    echo "<br>❌ Erreur PDO : " . $e->getMessage();
}
?>
