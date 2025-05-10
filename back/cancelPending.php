<?php
session_start();
unset($_SESSION['tripPending']);

// Retour à la page précédente
$previous = $_SERVER['HTTP_REFERER'] ?? '/front/user/home.php';
header("Location: $previous");
exit;
