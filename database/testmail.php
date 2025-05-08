<?php
session_start();

require_once '../back/composants/phpMailer/src/PHPMailer.php';
require_once '../back/composants/phpMailer/src/SMTP.php';
require_once '../back/composants/phpMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Envoi d'un email test vers MailHog
$mail = new PHPMailer(true);

try {
    // Configuration SMTP locale (MailHog)
    $mail->isSMTP();
    $mail->Host = 'localhost';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;

    $mail->setFrom('no-reply@ecoride.fr', 'EcoRide');
    $mail->addAddress('test@ecoride.fr', 'Utilisateur test');

    $mail->isHTML(true);
    $mail->Subject = 'Test PHPMailer sans dépendance';
    $mail->Body = '<b>Ceci est un message de test intercepté par MailHog.</b>';

    $mail->send();
    echo "✅ Email envoyé avec succès !";
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi : " . $mail->ErrorInfo;
}
