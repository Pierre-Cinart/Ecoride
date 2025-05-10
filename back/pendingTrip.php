<?php
session_start();

// Vérifie si le trip_id est bien présent
if (!isset($_POST['trip_id']) || !is_numeric($_POST['trip_id'])) {
    header('Location: ../front/user/login.php');
    exit;
}

// Enregistre l'ID du trajet en attente
$_SESSION['tripPending'] = (int) $_POST['trip_id'];

// Redirige selon l'action souhaitée
if (isset($_POST['location'])) {
    if ($_POST['location'] === 'connect') {
        header('Location: ../front/user/login.php');
        exit;
    } elseif ($_POST['location'] === 'register') {
        header('Location: ../front/user/register.php');
        exit;
    }
}

// Si aucun cas ne correspond, on nettoie la session
unset($_SESSION['tripPending']);
header('Location: ../front/user/login.php');
exit;
