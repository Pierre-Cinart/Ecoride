<?php
// Définir le chemin racine du projet
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__)); // ça pointe vers /back
}

/**
 * Fonction uploadImage
 * Convertit une image en WebP, la redimensionne,
 * la sauvegarde sur le serveur et met à jour la base de données avec le chemin.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param array $image
 * @param string $typeOfPicture
 * @param string $backUrl
 * @param int|null $vehicleId
 * @param string|null $typeOfDocument
 * @param int $width
 * @param int $height
 * @return string|false
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

    // Vérifier le type MIME
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($image['type'], $allowedTypes)) {
        $_SESSION['error'] = "Formats acceptés : JPG, PNG, GIF, WebP uniquement.";
        header("Location: $backUrl");
        exit;
    }

    // Convertir l’image source en ressource GD
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

    // Créer le dossier de destination
    $folder = PROJECT_ROOT . '/uploads/' . $pseudo . '/' . $typeOfPicture;
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
        file_put_contents($folder . '/.htaccess', "Order Deny,Allow\nDeny from all");
    }

    // Générer le nom et chemin final
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

    // Récupérer le chemin de l’ancien fichier
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

    // Enregistrer la nouvelle image
    if (!imagewebp($resized, $pathToSave, 80)) {
        $_SESSION['error'] = "Échec lors de l'enregistrement de l’image.";
        header("Location: $backUrl");
        exit;
    }
    imagedestroy($resized);

    // Mise à jour BDD
    if (in_array($typeOfPicture, ['profil', 'permit'])) {
        $stmt = $pdo->prepare("UPDATE users SET $column = :path WHERE id = :id");
        $stmt->execute([':path' => $relativePath, ':id' => $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE vehicles SET $column = :path WHERE id = :id");
        $stmt->execute([':path' => $relativePath, ':id' => $vehicleId]);
    }

    return $relativePath;
}
