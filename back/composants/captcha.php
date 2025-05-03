<?php
//verification google - Recaptcha
$recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
$secret = $RECAPTCHA_PRIVATE_KEY;

$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$recaptchaToken");
$result = json_decode($response, true);

if (!$result['success'] || $result['score'] < 0.5) {
    $_SESSION['error'] = "Échec de vérification reCAPTCHA.";
    header('Location: ../../front/user/login.php');
    exit();
}
?>