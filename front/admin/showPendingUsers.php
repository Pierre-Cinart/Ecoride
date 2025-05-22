<?php
$_GET = sanitizeArray($_GET, './manageUsers.php');
$search = $_POST['search'] ?? '';
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// 1. Récupération de tous les utilisateurs non admin/employé
$stmt = $pdo->prepare("
  SELECT *
  FROM users
  WHERE pseudo LIKE :search
    AND role NOT IN ('admin', 'employee')
");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->execute();
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. On filtre ceux ayant un permis ou des documents de véhicule en attente
$users = [];
foreach ($allUsers as $user) {
  $user['pending_docs'] = [];

  if ($user['permit_status'] === 'pending' && !empty($user['permit_picture'])) {
    $user['pending_docs'][] = [
      'type' => 'Permis de conduire',
      'path' => $user['permit_picture']
    ];
  }

  $stmtVeh = $pdo->prepare("
    SELECT model, registration_document, insurance_document
    FROM vehicles
    WHERE user_id = :uid
      AND documents_status = 'pending'
  ");
  $stmtVeh->execute([':uid' => $user['id']]);
  $vehicles = $stmtVeh->fetchAll(PDO::FETCH_ASSOC);

  foreach ($vehicles as $v) {
    if (!empty($v['registration_document'])) {
      $user['pending_docs'][] = [
        'type' => "Carte grise ({$v['model']})",
        'path' => $v['registration_document']
      ];
    }
    if (!empty($v['insurance_document'])) {
      $user['pending_docs'][] = [
        'type' => "Assurance ({$v['model']})",
        'path' => $v['insurance_document']
      ];
    }
  }

  // Ne garder que ceux avec documents en attente
  if (!empty($user['pending_docs'])) {
    $users[] = $user;
  }
}

$total = count($users);
$users = array_slice($users, $offset, $limit);

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

    <p><strong>Usager :</strong>
      <?= getWarningIcons((int)$user['user_warnings']) ?>
      <?php if (in_array($user['status'], ['blocked', 'all_blocked'])): ?>
        <span>(bloqué)</span>
        <button class="green">Débloquer usager</button>
      <?php else: ?>
        <button class="red">Bloquer usager</button>
      <?php endif; ?>
    </p>

    <?php if ($user['role'] === 'driver' || $user['driver_warnings'] > 0 || $user['status'] === 'drive_blocked' || $user['status'] === 'all_blocked'): ?>
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

    <hr>
    <p><strong>Documents en attente :</strong></p>
    <ul>
      <?php foreach ($user['pending_docs'] as $doc): ?>
        <li><?= htmlspecialchars($doc['type']) ?> :
          <form method="post" action="../../back/viewDocument.php" target="_blank" style="display:inline;">
            <input type="hidden" name="image_path" value="<?= htmlspecialchars($doc['path']) ?>">
            <button type="submit">Voir</button>
          </form>
          <button class="green">Valider</button>
          <button class="red">Refuser</button>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endforeach; ?>

<?php if (empty($users)): ?>
  <p>Aucun document en attente pour des utilisateurs/passagers ou conducteurs.</p>
<?php endif; ?>

<?php renderPagination($total, $limit, $page, 'showPendingUsers.php'); ?>
