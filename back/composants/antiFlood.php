<?php
// ============================
// Composant anti-flood avancé
// Fichier : antiflood.php
// ============================

// Configuration par défaut (modifiable via les paramètres de la fonction)
define('FLOOD_SESSION_KEY', 'flood_attempts');
define('FLOOD_BLOCK_KEY', 'flood_blocked');

/**
 * Vérifie si une action est autorisée ou bloquée temporairement.
 * @param string $action Nom de l'action à surveiller (ex: 'login', 'register', etc.)
 * @param int $maxAttempts Nombre maximal de tentatives autorisées
 * @param int $intervalSecs Fenêtre temporelle (en secondes) pendant laquelle les tentatives sont comptabilisées
 * @param int $blockDuration Durée du blocage en cas d'abus (en secondes)
 * @return bool true si l'action est autorisée, false si bloquée
 */
function checkFlood(string $action, int $maxAttempts = 3, int $intervalSecs = 60, int $blockDuration = 3600): bool {
    // Initialiser les sessions si nécessaire
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Vérifie si l'action est déjà bloquée
    if (isset($_SESSION[FLOOD_BLOCK_KEY][$action])) {
        $blockedUntil = $_SESSION[FLOOD_BLOCK_KEY][$action];
        if (time() < $blockedUntil) {
            return false; // Toujours bloqué
        } else {
            unset($_SESSION[FLOOD_BLOCK_KEY][$action]); // Périmé, on débloque
        }
    }

    // Récupération des tentatives précédentes pour cette action
    $now = time();
    $attempts = $_SESSION[FLOOD_SESSION_KEY][$action] ?? [];

    // Nettoyage : on garde uniquement les tentatives dans la fenêtre temporelle
    $recentAttempts = array_filter($attempts, fn($timestamp) => ($now - $timestamp) <= $intervalSecs);

    // Ajout de la tentative actuelle
    $recentAttempts[] = $now;

    // Enregistrement mis à jour en session
    $_SESSION[FLOOD_SESSION_KEY][$action] = $recentAttempts;

    // Si trop de tentatives -> blocage
    if (count($recentAttempts) > $maxAttempts) {
        $_SESSION[FLOOD_BLOCK_KEY][$action] = $now + $blockDuration;
        return false;
    }

    return true; // OK, tentative autorisée
}

/**
 * Réinitialise les tentatives pour une action (utile après une réussite)
 */
function clearFlood(string $action): void {
    unset($_SESSION[FLOOD_SESSION_KEY][$action]);
    unset($_SESSION[FLOOD_BLOCK_KEY][$action]);
}
