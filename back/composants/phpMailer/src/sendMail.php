<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/Exception.php';

/**
 * Envoie un email via PHPMailer (compatible MailHog en dev ou serveur SMTP réel en prod)
 *
 * @param string $toEmail   Adresse du destinataire
 * @param string $toName    Nom du destinataire
 * @param string $subject   Sujet de l'email
 * @param string $body      Contenu HTML de l'email
 * @param bool $useLocalSMTP true = utilise localhost:1025 (MailHog), false = config SMTP réel
 * @return bool
 */
function sendMail($toEmail, $toName, $subject, $body, $useLocalSMTP = true) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        if ($useLocalSMTP) {
            $mail->Host = 'localhost';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
        } else {
            // Config SMTP réel
            $mail->Host = 'smtp.votresite.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->Username = 'votre@email.com';
            $mail->Password = 'motdepasse';
            $mail->SMTPSecure = 'tls';
        }

        $mail->setFrom('no-reply@ecoride.fr', 'EcoRide');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = mb_encode_mimeheader($subject, 'UTF-8', 'B');
        $mail->Body    = $body;

        return $mail->send();

    } catch (Exception $e) {
        error_log("Erreur mail PHPMailer : " . $mail->ErrorInfo);
        return false;
    }
}
?>
