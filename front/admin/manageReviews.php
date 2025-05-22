<?php
// ==============================
// Initialisation et sécurité
// ==============================
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/paginate.php';
require_once '../composants/btnBack.php';

// Autoriser uniquement Admin et Employé
checkAccess(['Admin', 'Employee']);

// Nettoyage des paramètres GET
$_GET = sanitizeArray($_GET, './manage.php');
$status = $_GET['status'] ?? 'pending'; // par défaut : commentaires en attente

// Vérification du statut (seuls 'pending' et 'accepted' sont valides)
if (!in_array($status, ['pending', 'accepted'])) {
    header('Location: ./manageReviews.php');
    exit;
}

// Nettoyage du champ de recherche (pseudo uniquement)
$search = $_POST['search'] ?? '';

// Paramètres de pagination
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// ==============================
// Requête SQL pour affichage
// ==============================
$stmt = $pdo->prepare("
    SELECT r.id, u1.pseudo AS passenger_pseudo, u1.first_name AS passenger_first, u1.last_name AS passenger_last,
           u2.pseudo AS driver_pseudo, r.comment
    FROM ratings r
    JOIN users u1 ON r.author_id = u1.id
    JOIN trips t ON r.trip_id = t.id
    JOIN users u2 ON t.driver_id = u2.id
    WHERE r.status = :status
      AND u1.pseudo LIKE :search
    ORDER BY r.created_at DESC
    LIMIT :offset, :limit
");
$stmt->bindValue(':status', $status);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==============================
// Total pour la pagination
// ==============================
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM ratings r
    JOIN users u1 ON r.author_id = u1.id
    JOIN trips t ON r.trip_id = t.id
    JOIN users u2 ON t.driver_id = u2.id
    WHERE r.status = :status AND u1.pseudo LIKE :search
");
$countStmt->bindValue(':status', $status);
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$countStmt->execute();
$totalReviews = $countStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="icon" href="../../favicon.ico" type="image/x-icon">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gérer les avis | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
     <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<main>
  <div class="form-container manage-container">
    <h2>Gérer les avis</h2>

    <!-- ===========================
         Boutons de filtrage
    ============================ -->
    <div class="top-buttons">
      <?php btnBack('manage.php') ?>
      <button type="button" class="yellow" onclick="location.href='manageReviews.php?status=pending'">Avis en attente</button>
      <button type="button" class="green" onclick="location.href='manageReviews.php?status=accepted'">Avis validés</button>
    </div>

    <!-- ===========================
         Barre de recherche (pseudo)
    ============================ -->
    <form style="display:flex;flex-direction:row;align-items:center;padding:5px;justify-content:center;"method="post" action="./manageReview.php?status=<?= htmlspecialchars($status) ?>">
      <input style="width:220px;" type="text" name="search" placeholder="Rechercher par pseudo" value="<?= htmlspecialchars($search) ?>">
      <button style="width:40px;text-align:center;margin:10px;"type="submit">🔍</button>
    </form>

    <!-- ===========================
         Liste des avis
    ============================ -->
    <div class="avis-liste">
      <?php if (empty($reviews)): ?>
        <p>Aucun commentaire à afficher pour cette recherche.</p>
      <?php else: ?>
        <?php foreach ($reviews as $review): ?>
          <div class="avis-card">
            <p><strong>Passager :</strong> <?= htmlspecialchars($review['passenger_first']) ?> <?= htmlspecialchars($review['passenger_last']) ?> (<?= htmlspecialchars($review['passenger_pseudo']) ?>)</p>
            <p><strong>Conducteur :</strong> <?= htmlspecialchars($review['driver_pseudo']) ?></p>
            <p><strong>Commentaire :</strong> <?= nl2br(htmlspecialchars($review['comment'])) ?></p>

            <form action="../../back/manageReview.php" method="post">
              <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
              <?php if ($status === 'pending'): ?>
                <button type="submit" name="action" value="approve" class="green">Valider l'avis</button>
                <button type="submit" name="action" value="refused" class="red">Refuser l'avis</button>
              <?php else: ?>
                <button type="submit" name="action" value="delete"class="red">Supprimer</button>
              <?php endif; ?>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ===========================
         Pagination
    ============================ -->
    <?php
      renderPagination($totalReviews, $limit, $page, "manageReview.php?status=$status");
    ?>
  </div>
</main>

<?php include_once '../composants/footer.php'; ?>

</body>
</html>
