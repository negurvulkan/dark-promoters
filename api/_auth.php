<?php
declare(strict_types=1);

/**
 * Require a valid session based on Authorization header or session_token parameter.
 *
 * @param PDO $db Database connection
 * @return array Authenticated user as ['id' => int, 'username' => string, 'is_admin' => int]
 */
function require_session(PDO $db): array {
    $token = '';

    // Check Authorization: Bearer <token> header
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (stripos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }

    // Fallback to session_token parameter (GET/POST)
    if ($token === '') {
        $param = $_REQUEST['session_token'] ?? '';
        if (is_string($param)) {
            $token = $param;
        }
    }

    if ($token === '') {
        http_response_code(401);
        echo json_encode(['error' => 'missing session token'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $db->prepare('SELECT u.id, u.username, u.is_admin FROM sessions s JOIN users u ON s.user_id = u.id WHERE s.session_token = ? AND s.expires_at > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'invalid session'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'is_admin' => (int)$user['is_admin'],
    ];
}

/**
 * Require that the current session belongs to an admin user.
 *
 * @param PDO $db Database connection
 * @return array Authenticated admin user
 */
function require_admin(PDO $db): array {
    $user = require_session($db);
    if ($user['is_admin'] === 1) {
        return $user;
    }
    http_response_code(403);
    echo json_encode(['error' => 'admin required'], JSON_UNESCAPED_UNICODE);
    exit;
}

