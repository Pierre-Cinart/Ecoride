<?php
session_start();
require_once '../back/composants/db_connect.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    $_SESSION['error'] = "Lien de confirmation invalide ou manquant.";
    header('Location: ../front/user/login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT id, is_verified_email, email_token_expires_at FROM users WHERE email_verification_token = :token AND role = 'employee'");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "Ce lien est invalide.";
    header('Location: ../front/user/login.php');
    exit();
}

$currentDate = new DateTime();
$expirationDate = new DateTime($user['email_token_expires_at']);

if ($currentDate > $expirationDate) {
    $_SESSION['error'] = "Ce lien a expiré.";
    header('Location: ../front/user/login.php');
    exit();
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm-password'] ?? '';

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($password !== $confirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = :pwd, is_verified_email = 1, email_verification_token = NULL, email_token_expires_at = NULL WHERE id = :id");
        $update->execute([
            ':pwd' => $hashedPassword,
            ':id' => $user['id']
        ]);

        $_SESSION['success'] = "Mot de passe créé avec succès. Vous pouvez maintenant vous connecter.";
        header('Location: ../front/user/login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Création du mot de passe | EcoRide</title>
  <link rel="stylesheet" href="../front/css/style.css">
</head>
<body>
  <main class="form-container">
    <h2>Création de votre mot de passe</h2>
    <?php if (!empty($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <form method="post">
      <label for="password">Nouveau mot de passe :</label>
      <input type="password" name="password" id="password" required>

      <label for="confirm-password">Confirmer le mot de passe :</label>
      <input type="password" name="confirm-password" id="confirm-password" required>

      <button type="submit">Valider</button>
    </form>
  </main>
</body>
</html>
