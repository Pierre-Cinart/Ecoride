<?php
// back/composants/uploader.php

/**
 * Fonction uploadImage
 * Permet de convertir une image (jpg, png, gif) en WebP, de la redimensionner,
 * de la sauvegarder sur le serveur et de mettre à jour la base de données.
 *
 * @param PDO $pdo Connexion PDO active
 * @param int $userId ID utilisateur
 * @param array $image Tableau $_FILES['...']
 * @param string $typeOfPicture Type d’image : 'profil', 'vehicle', 'document'
 * @param string $backUrl Chemin de retour en cas d’erreur
 * @param int|null $vehicleId ID du véhicule si besoin
 * @param string|null $typeOfDocument 'registration' ou 'insurance' si besoin
 * @param int $width Largeur cible (défaut 512)
 * @param int $height Hauteur cible (défaut 512)
 * @return string|false Chemin WebP final ou false en cas d’échec
 */
function uploadImage(PDO $pdo, int $userId, array $image, string $typeOfPicture, string $backUrl, ?int $vehicleId = null, ?string $typeOfDocument = null, int $width = 512, int $height = 512): string|false {
    // Vérification du format
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($image['type'], $allowedTypes)) {
        $_SESSION['error'] = "Formats acceptés : JPG, PNG, GIF, WebP uniquement.";
        header("Location: $backUrl");
        exit;
    }

    // Création de l’image GD
    switch ($image['type']) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($image['tmp_name']);
            break;
        case 'image/png':
            $src = imagecreatefrompng($image['tmp_name']);
            imagepalettetotruecolor($src);
            imagealphablending($src, true);
            imagesavealpha($src, true);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($image['tmp_name']);
            break;
        case 'image/webp':
            $src = imagecreatefromwebp($image['tmp_name']);
            break;
        default:
            $_SESSION['error'] = "Type de fichier non pris en charge.";
            header("Location: $backUrl");
            exit;
    }

    if (!$src) {
        $_SESSION['error'] = "Erreur lors du traitement de l’image.";
        header("Location: $backUrl");
        exit;
    }

    // Redimensionnement
    $resized = imagescale($src, $width, $height);
    imagedestroy($src);

    // Récupération du pseudo utilisateur pour nom dossier
    $stmt = $pdo->prepare("SELECT pseudo FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $pseudo = $stmt->fetchColumn();

    $folder = __DIR__ . '/../uploads/' . $pseudo . '/' . $typeOfPicture;
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
        file_put_contents($folder . '/.htaccess', "Order Deny,Allow\nDeny from all");
    }

    $filename = uniqid() . '.webp';
    $pathToSave = $folder . '/' . $filename;
    $relativePath = 'uploads/' . $pseudo . '/' . $typeOfPicture . '/' . $filename;

    // Sauvegarde sur le disque
    if (!imagewebp($resized, $pathToSave, 80)) {
        $_SESSION['error'] = "Échec lors de l'enregistrement de l’image.";
        header("Location: $backUrl");
        exit;
    }
    imagedestroy($resized);

    // Supprimer ancienne image si existante
    $column = match ($typeOfPicture) {
        'profil' => 'profil_picture',
        'vehicle' => 'picture',
        'document' => match ($typeOfDocument) {
            'registration' => 'registration_picture',
            'insurance' => 'insurance_picture',
            default => null
        },
        default => null
    };

    if ($column) {
        if ($typeOfPicture === 'profil') {
            $stmt = $pdo->prepare("SELECT $column FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
        } elseif ($typeOfPicture === 'vehicle') {
            $stmt = $pdo->prepare("SELECT $column FROM vehicles WHERE id = :id");
            $stmt->execute([':id' => $vehicleId]);
        } elseif ($typeOfPicture === 'document') {
            $stmt = $pdo->prepare("SELECT $column FROM vehicles WHERE id = :id");
            $stmt->execute([':id' => $vehicleId]);
        }

        $oldPath = $stmt->fetchColumn();
        // Mise à jour du nouveau chemin
        if ($typeOfPicture === 'profil') {
            $stmt = $pdo->prepare("UPDATE users SET $column = :path WHERE id = :id");
            $stmt->execute([':path' => $relativePath, ':id' => $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE vehicles SET $column = :path WHERE id = :id");
            $stmt->execute([':path' => $relativePath, ':id' => $vehicleId]);
        }

        // Suppression de l’ancienne image après mise à jour réussie
        if ($oldPath && file_exists(__DIR__ . '/../' . $oldPath)) {
            unlink(__DIR__ . '/../' . $oldPath);
        }
    }

    return $relativePath;
}
?>
