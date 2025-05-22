<?php

// Nettoyage et pagination
$_GET = sanitizeArray($_GET, './manageUsers.php');
$search = $_POST['search'] ?? '';
$allLimit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $allLimit;

// Requête pour tous les utilisateurs avec pseudo filtré
$stmt = $pdo->prepare("
  SELECT id, first_name, last_name, pseudo, email, role, created_at,
         status, driver_warnings, user_warnings
  FROM users
  WHERE pseudo LIKE :search
  ORDER BY created_at DESC
  LIMIT :offset, :limit
");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $allLimit, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total pour pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE pseudo LIKE :search");
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$countStmt->execute();
$allTotalUsers = $countStmt->fetchColumn();

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

    <?php if ($user['role'] === 'driver' || $user['status'] === 'drive_blocked' || $user['driver_warnings'] > 0): ?>
      <p><strong>Conducteur :</strong>
        <?= getWarningIcons((int)$user['driver_warnings']) ?>
        <?php if (in_array($user['status'], ['drive_blocked', 'all_blocked'])): ?>
          <span>(bloqué)</span>
          <button class="green">Débloquer conducteur</button>
        <?php else: ?>
          <button class="red">Bloquer conducteur</button>
        <?php endif; ?>
      </p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php if (empty($users)): ?>
  <p>Aucun utilisateur trouvé pour cette recherche.</p>
<?php endif; ?>

<!-- Pagination -->
<?php renderPagination($allTotalUsers, $allLimit, $page, 'showUsers.php'); ?>
