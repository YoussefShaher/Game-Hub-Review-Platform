<?php
// ── NexusReview — Auth API ────────────────────────────────────
// Place at: htdocs/nexusreview/api/auth.php
//
// POST /api/auth.php?action=register  → create account
// POST /api/auth.php?action=login     → log in
//   Body (JSON): { email, password, username? }

require_once __DIR__ . '/../config/db.php';

$pdo    = getDB();
$action = $_GET['action'] ?? '';
$data   = json_decode(file_get_contents('php://input'), true);

// ── REGISTER ─────────────────────────────────────────────────
if ($action === 'register') {
    $username = isset($data['username']) ? trim($data['username']) : null;
    $email    = isset($data['email'])    ? trim($data['email'])    : null;
    $password = isset($data['password']) ? $data['password']       : null;

    if (!$username || !$email || !$password) {
        http_response_code(400);
        die(json_encode(['error' => 'username, email and password required']));
    }

    // STRICT EMAIL VALIDATION
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid email address format']));
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        die(json_encode(['error' => 'Password must be at least 8 characters']));
    }

    // Check duplicates
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->execute([$email, $username]);
    if ($check->fetch()) {
        http_response_code(409);
        die(json_encode(['error' => 'Email or username already taken']));
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hash]);

    http_response_code(201);
    echo json_encode(['success' => true, 'user_id' => $pdo->lastInsertId(), 'username' => $username]);
    exit;
}

// ── LOGIN ─────────────────────────────────────────────────────
if ($action === 'login') {
    $email    = isset($data['email'])    ? trim($data['email']) : null;
    $password = isset($data['password']) ? $data['password']    : null;

    if (!$email || !$password) {
        http_response_code(400);
        die(json_encode(['error' => 'email and password required']));
    }

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        die(json_encode(['error' => 'Invalid email or password']));
    }

    // START SESSION AND REGENERATE ID FOR SECURITY
    session_start();
    session_regenerate_id(true); 

    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    echo json_encode([
        'success'  => true,
        'user_id'  => $user['id'],
        'username' => $user['username'],
        'role'     => $user['role'],
    ]);
    exit;
}

// ── LOGOUT ───────────────────────────────────────────────────
if ($action === 'logout') {
    session_start();
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action. Use: register, login, logout']);