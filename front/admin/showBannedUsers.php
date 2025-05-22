<?php
// ============================
// MODULE : showBannedUsers.php
// ============================

// $_GET = sanitizeArray($_GET, './showUsers.php');
// $search = $_POST['search'] ?? '';
// $bannedLimit = 5;
// $bannedPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// $bannedOffset = ($bannedPage - 1) * $bannedLimit;

// $stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'banned' AND pseudo LIKE :search ORDER BY created_at DESC LIMIT :offset, :limit");
// $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $stmt->bindValue(':offset', $bannedOffset, PDO::PARAM_INT);
// $stmt->bindValue(':limit', $bannedLimit, PDO::PARAM_INT);
// $stmt->execute();
// $bannedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE status = 'banned' AND pseudo LIKE :search");
// $count->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $count->execute();
// $bannedTotal = $count->fetchColumn();

// include './userCardGenerator.php';
// renderPagination($bannedTotal, $bannedLimit, $bannedPage, 'showUsers.php');