<?php

// ==============================
// Nettoyage, pagination, filtre
// ==============================

$search = $_POST['search'] ?? '';
$driverLimit = 5;
$driverPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$driverOffset = ($driverPage - 1) * $driverLimit;

// ==============================
// Requête SQL pour les conducteurs
// ==============================
$driverStmt = $pdo->prepare("
  SELECT id, first_name, last_name, pseudo, email, role, created_at,
         status, driver_warnings, user_warnings
  FROM users
  WHERE role = 'driver'
    AND pseudo LIKE :search
  ORDER BY created_at DESC
  LIMIT :offset, :limit
");
$driverStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$driverStmt->bindValue(':offset', $driverOffset, PDO::PARAM_INT);
$driverStmt->bindValue(':limit', $driverLimit, PDO::PARAM_INT);
$driverStmt->execute();
$drivers = $driverStmt->fetchAll(PDO::FETCH_ASSOC);

// ==============================
// Total pour la pagination
// ==============================
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'driver' AND pseudo LIKE :search");
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$countStmt->execute();
$driverTotal = $countStmt->fetchColumn();

// ==============================
// Icônes de warning
// ==============================
function getWarningIcons(int $count): string {
  if ($count >= 20) return '⚠️⚠️⚠️';
  if ($count >= 10) return '⚠️⚠️';
  if ($count > 0)  return '⚠️';
  return '';
}
?>

<!-- ==============================
     Affichage des cartes conducteurs
============================== -->
<?php foreach ($drivers as $user): ?>
  <div class="user-card">
    <p><strong>Nom :</strong> <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></p>
    <p><strong>Pseudo :</strong> <?= htmlspecialchars($user['pseudo']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
    <p><strong>Date d'inscription :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?></p>
    <p><strong>Statut global :</strong> <?= htmlspecialchars($user['status']) ?></p>

    <hr>

    <!-- Avertissements passager -->
    <p><strong>Usager :</strong>
      <?= getWarningIcons((int)$user['user_warnings']) ?>
      <?php if (in_array($user['status'], ['blocked', 'all_blocked'])): ?>
        <span>(bloqué)</span>
        <button class="green">Débloquer usager</button>
      <?php else: ?>
        <button class="red">Bloquer usager</button>
      <?php endif; ?>
    </p>

    <!-- Avertissements conducteur -->
    <p><strong>Conducteur :</strong>
      <?= getWarningIcons((int)$user['driver_warnings']) ?>
      <?php if (in_array($user['status'], ['drive_blocked', 'all_blocked'])): ?>
        <span>(bloqué)</span>
        <button class="green">Débloquer conducteur</button>
      <?php else: ?>
        <button class="red">Bloquer conducteur</button>
      <?php endif; ?>
    </p>
  </div>
<?php endforeach; ?>

<?php if (empty($drivers)): ?>
  <p>Aucun conducteur trouvé pour cette recherche.</p>
<?php endif; ?>

<!-- ==============================
     Pagination personnalisée
============================== -->
<?php renderPagination($driverTotal, $driverLimit, $driverPage, 'php?filter=drivers'); ?>
