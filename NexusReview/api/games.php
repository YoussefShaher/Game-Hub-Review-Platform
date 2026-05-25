<?php

require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$id      = isset($_GET['id'])      ? (int)$_GET['id']           : null;
$genre   = isset($_GET['genre'])   ? trim($_GET['genre'])        : null;
$console = isset($_GET['console']) ? trim($_GET['console'])      : null;

// ── Single game ───────────────────────────────────────────────
if ($id) {
    $stmt = $pdo->prepare("
        SELECT g.*,
               ROUND(AVG(r.score)) AS avg_score,
               COUNT(r.id)         AS review_count,
               GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ',') AS consoles
        FROM games g
        LEFT JOIN reviews r ON r.game_id = g.id
        LEFT JOIN game_consoles gc ON gc.game_id = g.id
        LEFT JOIN consoles c ON c.id = gc.console_id
        WHERE g.id = ?
        GROUP BY g.id
    ");
    $stmt->execute([$id]);
    $game = $stmt->fetch();
    if (!$game) { http_response_code(404); die(json_encode(['error' => 'Game not found'])); }
    $game['consoles'] = $game['consoles'] ? explode(',', $game['consoles']) : [];
    echo json_encode($game);
    exit;
}

// ── List (with optional filters) ─────────────────────────────
$where  = [];
$params = [];

if ($genre && $genre !== 'All') {
    $where[]  = 'g.genre = ?';
    $params[] = $genre;
}
if ($console && $console !== 'All') {
    $where[]  = 'EXISTS (SELECT 1 FROM game_consoles gc2 JOIN consoles c2 ON c2.id = gc2.console_id WHERE gc2.game_id = g.id AND c2.name = ?)';
    $params[] = $console;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
 SELECT g.id, g.title, g.developer, g.genre, g.cover_image, g.release_date, g.icon, g.bg_class,
           ROUND(AVG(r.score))  AS avg_score,
           COUNT(r.id)          AS review_count,
           GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ',') AS consoles
    FROM games g
    LEFT JOIN reviews r ON r.game_id = g.id
    LEFT JOIN game_consoles gc ON gc.game_id = g.id
    LEFT JOIN consoles c ON c.id = gc.console_id
    $whereSQL
    GROUP BY g.id
    ORDER BY avg_score DESC
");
$stmt->execute($params);
$games = $stmt->fetchAll();

foreach ($games as &$game) {
    $game['consoles'] = $game['consoles'] ? explode(',', $game['consoles']) : [];
}

echo json_encode(['games' => $games, 'total' => count($games)]);