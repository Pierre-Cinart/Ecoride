<?php
// insertAdmin.php

// Connexion à la base de données
require_once '../back/composants/db_connect.php'; // adapte le chemin si besoin

try {
    // Données de l'administrateur
    $pseudo = 'admin';
    $firstName = 'José';
    $lastName = 'Admin';
    $email = 'admin@ecoride.fr';
    $phone = '0101010101';
    $role = 'admin';
    $password = 'Mot2Passe'; 

    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Vérification si l'admin existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR pseudo = :pseudo");
    $stmt->execute([
        ':email' => $email,
        ':pseudo' => $pseudo
    ]);

    if ($stmt->fetchColumn() > 0) {
        echo "❌ Un administrateur existe déjà avec cet email ou ce pseudo.";
        exit();
    }

    // Insertion en base
    $insert = $pdo->prepare("INSERT INTO users (pseudo, first_name, last_name, email, password, phone_number, role, is_verified) 
                             VALUES (:pseudo, :first_name, :last_name, :email, :password, :phone_number, :role, 1)");

    $insert->execute([
        ':pseudo' => $pseudo,
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':phone_number' => $phone,
        ':role' => $role
    ]);

    echo "✅ Administrateur ajouté avec succès.";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
