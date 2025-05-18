<?php
// back/composants/uploader.php

/**
 * Fonction uploadImage
 * Convertit une image (jpg, png, gif, webp) en WebP, la redimensionne,
 * la sauvegarde sur le serveur et met à jour la base de données avec le chemin.
 *
 * @param PDO $pdo Connexion PDO
 * @param int $userId ID utilisateur
 * @param array $image Fichier $_FILES['...']
 * @param string $typeOfPicture 'profil', 'vehicle', 'document', 'permit'
 * @param string $backUrl Redirection si erreur
 * @param int|null $vehicleId ID du véhicule si besoin
 * @param string|null $typeOfDocument Type de document : 'registration' ou 'insurance'
 * @param int $width Largeur cible (défaut 512px)
 * @param int $height Hauteur cible (défaut 512px)
 * @return string|false Chemin WebP (commençant par "uploads/") ou false si échec
 */
function uploadImage(
    PDO $pdo,
    int $userId,
    array $image,
    string $typeOfPicture,
    string $backUrl,
    ?int $vehicleId = null,
    ?string $typeOfDocument = null,
    int $width = 512,
    int $height = 512
): string|false {

    // Vérifier les formats autorisés
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($image['type'], $allowedTypes)) {
        $_SESSION['error'] = "Formats acceptés : JPG, PNG, GIF, WebP uniquement.";
        header("Location: $backUrl");
        exit;
    }

    // Créer une image GD selon le type
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

    // Redimensionner l’image
    $resized = imagescale($src, $width, $height);
    imagedestroy($src);

    // Récupérer le pseudo utilisateur
    $stmt = $pdo->prepare("SELECT pseudo FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $pseudo = $stmt->fetchColumn();

    // Créer dossier de destination
    $folder = __DIR__ . '/../uploads/' . $pseudo . '/' . $typeOfPicture;
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
        file_put_contents($folder . '/.htaccess', "Order Deny,Allow\nDeny from all");
    }

    // Nom et chemin final
    $filename = uniqid() . '.webp';
    $pathToSave = $folder . '/' . $filename;
    $relativePath = 'uploads/' . $pseudo . '/' . $typeOfPicture . '/' . $filename;

    // Sauvegarde WebP
    if (!imagewebp($resized, $pathToSave, 80)) {
        $_SESSION['error'] = "Échec lors de l'enregistrement de l’image.";
        header("Location: $backUrl");
        exit;
    }
    imagedestroy($resized);

    // Déterminer la colonne à mettre à jour en base
    $column = match ($typeOfPicture) {
        'profil' => 'profil_picture',
        'permit' => 'permit_picture',
        'vehicle' => 'picture',
        'document' => match ($typeOfDocument) {
            'registration' => 'registration_document',
            'insurance' => 'insurance_document',
            default => null
        },
        default => null
    };

    // Si aucune colonne à mettre à jour, on retourne simplement le chemin
    if (!$column) return $relativePath;

    // Récupérer l'ancienne image pour la supprimer
    if (in_array($typeOfPicture, ['profil', 'permit'])) {
        $stmt = $pdo->prepare("SELECT $column FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    } else {
        $stmt = $pdo->prepare("SELECT $column FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $vehicleId]);
    }
    $oldPath = $stmt->fetchColumn();

    // Mise à jour en base de données
    if (in_array($typeOfPicture, ['profil', 'permit'])) {
        $stmt = $pdo->prepare("UPDATE users SET $column = :path WHERE id = :id");
        $stmt->execute([':path' => $relativePath, ':id' => $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE vehicles SET $column = :path WHERE id = :id");
        $stmt->execute([':path' => $relativePath, ':id' => $vehicleId]);
    }

    // Supprimer l’ancienne image
    if ($oldPath && file_exists(__DIR__ . '/../' . $oldPath)) {
        unlink(__DIR__ . '/../' . $oldPath);
    }

    return $relativePath;
}
