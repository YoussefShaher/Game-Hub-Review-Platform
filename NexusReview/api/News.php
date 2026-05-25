<?php

require_once __DIR__ . '/../config/db.php';

$pdo      = getDB();
$slug     = isset($_GET['slug'])     ? trim($_GET['slug'])     : null;
$category = isset($_GET['category']) ? trim($_GET['category']) : null;

// ── Single article ────────────────────────────────────────────
if ($slug) {
    $stmt = $pdo->prepare("
        SELECT n.*, n.author_name AS author
        FROM news n
        WHERE n.slug = ? AND n.published = 1
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    if (!$article) { http_response_code(404); die(json_encode(['error' => 'Article not found'])); }
    echo json_encode($article);
    exit;
}

// ── List ──────────────────────────────────────────────────────
$where  = ['n.published = 1'];
$params = [];

if ($category) {
    $where[]  = 'n.category = ?';
    $params[] = $category;
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT n.id, n.title, n.slug, n.category, n.image, n.created_at, n.author_name AS author
    FROM news n
    $whereSQL
    ORDER BY n.created_at DESC
");
$stmt->execute($params);
echo json_encode(['articles' => $stmt->fetchAll()]);