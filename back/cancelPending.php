<?php
session_start();
require_once './composants/JWT.php';
require_once './composant/db_connect';
checkToken($pdo);
unset($_SESSION['tripPending']);

// Retour à la page précédente
$previous = $_SERVER['HTTP_REFERER'] ?? '/front/user/home.php';
header("Location: $previous");
exit;
