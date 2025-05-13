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
        } else { //si non efface les espaces inutiles et échappe les données
            $clean[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }
    return $clean;
}
// retour cast int
function getPostInt(string $key): ?int {
    return (isset($_POST[$key]) && is_numeric($_POST[$key])) ? (int) $_POST[$key] : null;
}
// retour cast float
function getPostFloat(string $key, int $decimal = 1): ?float {
    return (isset($_POST[$key]) && is_numeric($_POST[$key])) 
        ? round((float) $_POST[$key], $decimal) 
        : null;
}

?>
