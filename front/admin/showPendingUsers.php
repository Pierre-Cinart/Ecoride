<?php
// ============================
// MODULE : showPendingUsers.php
// ============================

// $_GET = sanitizeArray($_GET, './showUsers.php');
// $search = $_POST['search'] ?? '';
// $pendingLimit = 5;
// $pendingPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// $pendingOffset = ($pendingPage - 1) * $pendingLimit;

// $stmt = $pdo->prepare("SELECT * FROM users WHERE (permit_status = 'pending' OR EXISTS (SELECT 1 FROM vehicles WHERE vehicles.user_id = users.id AND (vehicles.registration_status = 'pending' OR vehicles.insurance_status = 'pending'))) AND pseudo LIKE :search ORDER BY created_at DESC LIMIT :offset, :limit");
// $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $stmt->bindValue(':offset', $pendingOffset, PDO::PARAM_INT);
// $stmt->bindValue(':limit', $pendingLimit, PDO::PARAM_INT);
// $stmt->execute();
// $pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (permit_status = 'pending' OR EXISTS (SELECT 1 FROM vehicles WHERE vehicles.user_id = users.id AND (vehicles.registration_status = 'pending' OR vehicles.insurance_status = 'pending'))) AND pseudo LIKE :search");
// $count->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $count->execute();
// $pendingTotal = $count->fetchColumn();

// include './userCardGenerator.php';
// renderPagination($pendingTotal, $pendingLimit, $pendingPage, 'showUsers.php');
