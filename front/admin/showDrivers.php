<?php
// ============================
// MODULE : showDrivers.php
// ============================

// $_GET = sanitizeArray($_GET, './showUsers.php');
// $search = $_POST['search'] ?? '';
// $driversLimit = 5;
// $driversPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// $driversOffset = ($driversPage - 1) * $driversLimit;

// $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'driver' AND pseudo LIKE :search ORDER BY created_at DESC LIMIT :offset, :limit");
// $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $stmt->bindValue(':offset', $driversOffset, PDO::PARAM_INT);
// $stmt->bindValue(':limit', $driversLimit, PDO::PARAM_INT);
// $stmt->execute();
// $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'driver' AND pseudo LIKE :search");
// $count->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $count->execute();
// $driversTotal = $count->fetchColumn();

// include './userCardGenerator.php';
// renderPagination($driversTotal, $driversLimit, $driversPage, 'showUsers.php');


