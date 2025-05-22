<?php 
// ============================
// MODULE : showSimpleUsers.php
// ============================

// $_GET = sanitizeArray($_GET, './showUsers.php');
// $search = $_POST['search'] ?? '';
// $simpleLimit = 5;
// $simplePage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// $simpleOffset = ($simplePage - 1) * $simpleLimit;

// $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'simple' AND pseudo LIKE :search ORDER BY created_at DESC LIMIT :offset, :limit");
// $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $stmt->bindValue(':offset', $simpleOffset, PDO::PARAM_INT);
// $stmt->bindValue(':limit', $simpleLimit, PDO::PARAM_INT);
// $stmt->execute();
// $simpleUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'simple' AND pseudo LIKE :search");
// $count->bindValue(':search', "%$search%", PDO::PARAM_STR);
// $count->execute();
// $simpleTotal = $count->fetchColumn();

// include './userCardGenerator.php';
// renderPagination($simpleTotal, $simpleLimit, $simplePage, 'showUsers.php');


