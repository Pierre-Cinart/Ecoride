<?php
// ============================
// MODULE : showBlockedUsers.php
// ============================

$_GET = sanitizeArray($_GET, './showUsers.php');
$search = $_POST['search'] ?? '';
$blockedLimit = 5;
$blockedPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$blockedOffset = ($blockedPage - 1) * $blockedLimit;

$stmt = $pdo->prepare("SELECT * FROM users WHERE status IN ('blocked', 'drive_blocked', 'all_blocked') AND pseudo LIKE :search ORDER BY created_at DESC LIMIT :offset, :limit");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $blockedOffset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $blockedLimit, PDO::PARAM_INT);
$stmt->execute();
$blockedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE status IN ('blocked', 'drive_blocked', 'all_blocked') AND pseudo LIKE :search");
$count->bindValue(':search', "%$search%", PDO::PARAM_STR);
$count->execute();
$blockedTotal = $count->fetchColumn();

include './userCardGenerator.php';
renderPagination($blockedTotal, $blockedLimit, $blockedPage, 'showUsers.php');


