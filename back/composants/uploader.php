<?php
// Définir le chemin racine du projet (ex: /var/www/html/back)
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__)); // ça pointe vers /back
}

/**
 * Fonction uploadImage
 * - Convertit une image en WebP
 * - La redimensionne
 * - La sauvegarde dans un dossier structuré
 * - Supprime l'ancienne version
 * - Met à jour la base de données avec le nouveau chemin
 *
 * @param PDO $pdo
 * @param int $userId
 * @param array $image
 * @param string $typeOfPicture - profil | permit | vehicle | document
 * @param string $backUrl - Redirection en cas d'erreur
 * @param int|null $vehicleId
 * @param string|null $typeOfDocument - registration | insurance
 * @param int $width
 * @param int $height
 * @return string|false - Chemin relatif ou false si échec
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

    // Accepte uniquement certains types d'image
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($image['type'], $allowedTypes)) {
        $_SESSION['error'] = "Formats acceptés : JPG, PNG, GIF, WebP uniquement.";
        header("Location: $backUrl");
        exit;
    }

    // Convertir en ressource image selon le type
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

    // Redimensionner
    $resized = imagescale($src, $width, $height);
    imagedestroy($src);

    // Récupération du pseudo pour nommer le dossier
    $stmt = $pdo->prepare("SELECT pseudo FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $pseudo = $stmt->fetchColumn();

    // Chemin de sauvegarde final
    $folder = PROJECT_ROOT . '/uploads/' . $pseudo . '/' . $typeOfPicture;
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true); // plus de htaccess généré ici
    }

    // Générer le nom du fichier
    $filename = uniqid() . '.webp';
    $pathToSave = $folder . '/' . $filename;
    $relativePath = 'uploads/' . $pseudo . '/' . $typeOfPicture . '/' . $filename;

    // Déterminer la colonne à mettre à jour
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

    if (!$column) return $relativePath;

    // Récupérer l’ancien chemin d’image
    if (in_array($typeOfPicture, ['profil', 'permit'])) {
        $stmt = $pdo->prepare("SELECT $column FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    } else {
        $stmt = $pdo->prepare("SELECT $column FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $vehicleId]);
    }
    $oldPath = $stmt->fetchColumn();

    // Supprimer l’ancienne image si elle existe
    if ($oldPath) {
        $absoluteOldPath = PROJECT_ROOT . '/' . ltrim($oldPath, '/');
        if (file_exists($absoluteOldPath)) {
            @unlink($absoluteOldPath);
        }
    }

    // Sauvegarder l’image
    if (!imagewebp($resized, $pathToSave, 80)) {
        $_SESSION['error'] = "Échec lors de l'enregistrement de l’image.";
        header("Location: $backUrl");
        exit;
    }
    imagedestroy($resized);

    // Mise à jour de la base de données
    if (in_array($typeOfPicture, ['profil', 'permit'])) {
        $stmt = $pdo->prepare("UPDATE users SET $column = :path WHERE id = :id");
        $stmt->execute([':path' => $relativePath, ':id' => $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE vehicles SET $column = :path WHERE id = :id");
        $stmt->execute([':path' => $relativePath, ':id' => $vehicleId]);
    }

    return $relativePath;
}
