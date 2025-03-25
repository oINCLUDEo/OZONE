<?php
require_once '../config.php';

$lastId = (int)($_GET['last_id'] ?? 0);
$limit = (int)($_GET['limit'] ?? 6);
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

$sql = "SELECT * FROM products WHERE id > ?";
$params = [$lastId];

if ($categoryId) {
    $sql .= " AND category_id = ?";
    $params[] = $categoryId;
}

$sql .= " ORDER BY id LIMIT ?";
$params[] = $limit;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));