<?php
$_GET = sanitizeArray($_GET, './manageUsers.php');
$search = $_POST['search'] ?? '';
$allLimit = 5;
$allPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($allPage - 1) * $allLimit;

$stmt = $pdo->prepare("SELECT id, first_name, last_name, pseudo, email, role, created_at, status, driver_warnings, user_warnings, permit_status, permit_picture FROM users WHERE pseudo LIKE :search AND role NOT IN ('Admin', 'Employee') ORDER BY created_at DESC LIMIT :offset, :limit");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $allLimit, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE pseudo LIKE :search");
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$countStmt->execute();
$allTotalUsers = $countStmt->fetchColumn();

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
    <p><strong>Date d'inscription :</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
    <p><strong>Statut global :</strong> <?= htmlspecialchars($user['status']) ?></p>

    <hr>

    <!-- Section Usager -->
    <p><strong>Usager :</strong> <?= getWarningIcons((int)$user['user_warnings']) ?></p>
    <form method="post" action="../../back/managerLockUser.php">
      <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
      <input type="hidden" name="status" value="<?= htmlspecialchars($user['status']) ?>">
      <?php if (in_array($user['status'], ['blocked', 'all_blocked'])): ?>
        <span>(bloqué)</span>
        <button class="green" type="submit" name="action" value="unblock_user">Débloquer usager</button>
      <?php else: ?>
        <button class="red" type="submit" name="action" value="block">Bloquer usager</button>
      <?php endif; ?>
    </form>

    <!-- Section Conducteur -->
    <?php if ($user['role'] === 'driver' || $user['status'] === 'drive_blocked' || $user['driver_warnings'] > 0): ?>
      <p><strong>Conducteur :</strong> <?= getWarningIcons((int)$user['driver_warnings']) ?></p>
      <form method="post" action="../../back/managerLockUser.php">
        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
        <input type="hidden" name="status" value="<?= htmlspecialchars($user['status']) ?>">
        <?php if (in_array($user['status'], ['drive_blocked', 'all_blocked'])): ?>
          <span>(bloqué)</span>
          <button class="green" type="submit" name="action" value="unblock_driver">Débloquer conducteur</button>
        <?php else: ?>
          <button class="red" type="submit" name="action" value="block">Bloquer conducteur</button>
        <?php endif; ?>
      </form>
    <?php endif; ?>

    <!-- Permis en attente -->
    <?php if ($user['permit_status'] === 'pending' && $user['permit_picture']): ?>
      <div class="pending-docs">
        <ul>
          <li><strong>Permis en attente :</strong>
            <form method="post" action="../../back/viewDocument.php" target="_blank" style="display:inline;">
              <input type="hidden" name="image_path" value="<?= htmlspecialchars($user['permit_picture']) ?>">
              <button type="submit">Voir</button>
            </form>
            <button class="green">Valider</button>
            <button class="red">Refuser</button>
          </li>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Documents véhicule en attente -->
    <?php
      $vehicleStmt = $pdo->prepare("SELECT id, model, registration_document, insurance_document FROM vehicles WHERE user_id = :uid AND documents_status = 'pending'");
      $vehicleStmt->execute([':uid' => $user['id']]);
      $pendingVehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (!empty($pendingVehicles)): ?>
      <div class="pending-docs">
        <?php foreach ($pendingVehicles as $v): ?>
          <ul>
            <li><strong>Documents pour : <?= htmlspecialchars($v['model']) ?></strong></li>
            <?php if (!empty($v['registration_document'])): ?>
              <li>Carte grise :
                <form method="post" action="../../back/viewDocument.php" target="_blank" style="display:inline;">
                  <input type="hidden" name="image_path" value="<?= htmlspecialchars($v['registration_document']) ?>">
                  <button type="submit">Voir</button>
                </form>
                <button class="green">Valider</button>
                <button class="red">Refuser</button>
              </li>
            <?php endif; ?>
            <?php if (!empty($v['insurance_document'])): ?>
              <li>Assurance :
                <form method="post" action="../../back/viewDocument.php" target="_blank" style="display:inline;">
                  <input type="hidden" name="image_path" value="<?= htmlspecialchars($v['insurance_document']) ?>">
                  <button type="submit">Voir</button>
                </form>
                <button class="green">Valider</button>
                <button class="red">Refuser</button>
              </li>
            <?php endif; ?>
          </ul>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
<?php endforeach; ?>

<?php if (empty($users)): ?>
  <p>Aucun utilisateur trouvé pour cette recherche.</p>
<?php endif; ?>

<?php renderPagination($allTotalUsers, $allLimit, $allPage, 'manageUsers.php'); ?>
