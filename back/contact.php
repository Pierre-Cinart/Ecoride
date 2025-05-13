<?php
session_start();
require_once './composants/phpMailer/src/sendMail.php';
require_once './composants/sanitizeArray.php';
require_once './composants/captcha.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// verification googleCaptcha
verifyCaptcha('contact', '../front/user/contact.php'); // ← action + redirection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données postées (et sécurité contre les tableaux malicieux)
    $_POST = sanitizeArray($_POST, '../front/user/contact.php');
    $nom     = $_POST['nom'] ?? '';
    $prenom  = $_POST['prenom'] ?? '';
    $email   = $_POST['email'] ?? '';
    $objet   = $_POST['objet'] ?? '';
    $message = $_POST['message'] ?? '';

    if (
        empty($nom) || empty($prenom) || empty($email) ||
        empty($objet) || empty($message)
    ) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header('Location: ../user/contact.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Adresse email invalide.";
        header('Location: ../user/contact.php');
        exit;
    }

    $sujet = "Nouveau message de contact - $objet";
    $contenu = "
        <h3>Message reçu via le formulaire de contact</h3>
        <p><strong>Nom :</strong> $nom</p>
        <p><strong>Prénom :</strong> $prenom</p>
        <p><strong>Email :</strong> $email</p>
        <p><strong>Objet :</strong> $objet</p>
        <p><strong>Message :</strong><br>$message</p>
    ";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;

        $mail->setFrom('no-reply@ecoride.fr', 'EcoRide');
        $mail->addAddress('contact@ecoride.fr', 'Support EcoRide');
        $mail->addReplyTo($email, "$prenom $nom");

        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body    = $contenu;

        $mail->send();
        $_SESSION['success'] = "Votre message a bien été envoyé. Nous vous répondrons rapidement.";
    } catch (Exception $e) {
        error_log("Erreur mail PHPMailer : " . $mail->ErrorInfo);
        $_SESSION['error'] = "Une erreur est survenue lors de l'envoi du message.";
    }
    
    header('Location: ../front/user/home.php');
    exit;
} else {
    header('Location: ../front/user/contact.php');
    exit;
}
