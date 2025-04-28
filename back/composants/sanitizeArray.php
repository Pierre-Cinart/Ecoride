<?php

// Nettoie les données envoyées pour empêcher les injections
function sanitizeArray($array, $location = '../index.php') {
    $clean = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            // Si tableau détecté => stop tout et redirige vers la page précisée
            //penser à mettre un system de log sur mongoDB
            $_SESSION['error'] = "Tentative de données invalides détectée.";
            header('Location: ' . $location);
            exit();
        } else {
            $clean[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }
    return $clean;
}
?>
