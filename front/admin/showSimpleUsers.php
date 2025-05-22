<?php
// Nettoyage et pagination
$_GET = sanitizeArray($_GET, './manageUsers.php');
$search = $_POST['search'] ?? '';
$simpleLimit = 5;
$simplePage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$simpleOffset = ($simplePage - 1) * $simpleLimit;

// Requête pour les usagers simples (non conducteurs, non employés, non admins)
$stmt = $pdo->prepare("SELECT id, first_name, last_name, pseudo, email, role, created_at,
                              status, driver_warnings, user_warnings
                       FROM users
                       WHERE pseudo LIKE :search
                         AND role = 'user'
                       ORDER BY created_at DESC
                       LIMIT :offset, :limit");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $simpleOffset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $simpleLimit, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total pour pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE pseudo LIKE :search AND role = 'simpleUser'");
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$countStmt->execute();
$simpleTotalUsers = $countStmt->fetchColumn();

// Fonction pour icône de warning
function getWarningIcons(int $count): string {
  if ($count >= 20) return '⚠️⚠️⚠️';
  if ($count >= 10) return '⚠️⚠️';
  if ($count > 0)  return '⚠️';
  return '';
}
?>

<?php foreach ($users as $user): ?>
  <div class="user-card">
    <p><strong>Nom :</strong> <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></p>
    <p><strong>Pseudo :</strong> <?= htmlspecialchars($user['pseudo']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
    <p><strong>Date d'inscription :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?></p>
    <p><strong>Statut global :</strong> <?= htmlspecialchars($user['status']) ?></p>

    <hr>

    <p><strong>Usager :</strong>
      <?= getWarningIcons((int)$user['user_warnings']) ?>
      <?php if (in_array($user['status'], ['blocked', 'all_blocked'])): ?>
        <span>(bloqué)</span>
        <button class="green">Débloquer usager</button>
      <?php else: ?>
        <button class="red">Bloquer usager</button>
      <?php endif; ?>
    </p>
  </div>
<?php endforeach; ?>

<?php if (empty($users)): ?>
  <p>Aucun usager trouvé pour cette recherche.</p>
<?php endif; ?>

<!-- Pagination -->
<?php renderPagination($simpleTotalUsers, $simpleLimit, $simplePage, 'manageUsers.php?filter=simpleUsers'); ?>
