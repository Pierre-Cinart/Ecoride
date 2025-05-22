<?php
// Nettoyage, pagination, filtre
$_GET = sanitizeArray($_GET, './manageUsers.php');
$search = $_POST['search'] ?? '';
$bannedLimit = 5;
$bannedPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$bannedOffset = ($bannedPage - 1) * $bannedLimit;

// Requête SQL : uniquement les utilisateurs bannis
$stmt = $pdo->prepare("SELECT id, first_name, last_name, pseudo, email, role, created_at,
                              status, driver_warnings, user_warnings
                       FROM users
                       WHERE status = 'banned'
                         AND pseudo LIKE :search
                       ORDER BY created_at DESC
                       LIMIT :offset, :limit");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $bannedOffset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $bannedLimit, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total pour pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE status = 'banned' AND pseudo LIKE :search");
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$countStmt->execute();
$bannedTotalUsers = $countStmt->fetchColumn();

// Fonction d'affichage des icônes warning
function getWarningIcons(int $count): string {
  if ($count >= 20) return '⚠️⚠️⚠️';
  if ($count >= 10) return '⚠️⚠️';
  if ($count > 0)  return '⚠️';
  return '';
}
?>

<?php foreach ($users as $user): ?>
  <div class="user-card banned">
    <p><strong>Nom :</strong> <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></p>
    <p><strong>Pseudo :</strong> <?= htmlspecialchars($user['pseudo']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
    <p><strong>Date d'inscription :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?></p>
    <p><strong>Statut :</strong> <span style="color:red; font-weight:bold;">BANNI</span></p>

    <hr>

    <p><strong>Warnings usager :</strong> <?= getWarningIcons((int)$user['user_warnings']) ?></p>
    <p><strong>Warnings conducteur :</strong> <?= getWarningIcons((int)$user['driver_warnings']) ?></p>
  </div>
<?php endforeach; ?>

<?php if (empty($users)): ?>
  <p>Aucun utilisateur banni trouvé.</p>
<?php endif; ?>

<!-- Pagination -->
<?php renderPagination($bannedTotalUsers, $bannedLimit, $bannedPage, 'showUsers.php?filter=bannedUsers'); ?>
