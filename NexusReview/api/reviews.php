<?php
// ── NexusReview — Reviews API ─────────────────────────────────
// Place at: htdocs/nexusreview/api/reviews.php
//
// GET  /api/reviews.php?game_id=1   → reviews for a game
// POST /api/reviews.php             → submit a review
//   Body (JSON): { game_id, user_id, score, body, platform }

require_once __DIR__ . '/../config/db.php';

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: fetch reviews ────────────────────────────────────────
if ($method === 'GET') {
    $game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;

    if ($game_id) {
        // Fetch reviews for a SPECIFIC game
        $stmt = $pdo->prepare("
            SELECT r.id, r.game_id, r.score, r.body, r.platform, r.helpful, r.created_at,
                   u.username, u.avatar
            FROM reviews r
            JOIN users u ON u.id = r.user_id
            WHERE r.game_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$game_id]);
    } else {
        // Fetch the latest reviews for ALL games (Global Feed)
        $stmt = $pdo->prepare("
            SELECT r.id, r.game_id, r.score, r.body, r.platform, r.helpful, r.created_at,
                   u.username, u.avatar
            FROM reviews r
            JOIN users u ON u.id = r.user_id
            ORDER BY r.created_at DESC
            LIMIT 12
        ");
        $stmt->execute();
    }
    
    echo json_encode(['reviews' => $stmt->fetchAll()]);
    exit;
}

// ── POST: submit a review ─────────────────────────────────────
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $game_id  = isset($data['game_id'])  ? (int)$data['game_id']       : null;
    $user_id  = isset($data['user_id'])  ? (int)$data['user_id']       : null;
    $score    = isset($data['score'])    ? (int)$data['score']         : null;
    $body     = isset($data['body'])     ? trim($data['body'])          : null;
    $platform = isset($data['platform']) ? trim($data['platform'])      : null;

    // Basic validation
    if (!$game_id || !$user_id || !$score || !$body) {
        http_response_code(400);
        die(json_encode(['error' => 'game_id, user_id, score, and body are required']));
    }
    if ($score < 1 || $score > 100) {
        http_response_code(400);
        die(json_encode(['error' => 'Score must be between 1 and 100']));
    }

    $stmt = $pdo->prepare("
        INSERT INTO reviews (user_id, game_id, score, body, platform)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $game_id, $score, $body, $platform]);

    http_response_code(201);
    echo json_encode(['success' => true, 'review_id' => $pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
