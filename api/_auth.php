<?php
declare(strict_types=1);

/**
 * Require a valid session token provided either via an Authorization header
 * or a `session_token` cookie.
 *
 * @param PDO $db Database connection
 * @return array Authenticated user as ['id' => int, 'username' => string, 'is_admin' => int]
 */
function require_session(PDO $db): array {
    // Prefer Authorization: Bearer <token>
    $authHeader = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';
    if ($authHeader === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (stripos($authHeader, 'Bearer ') === 0) {
        $token = trim(substr($authHeader, 7));
    } else {
        $token = $_COOKIE['session_token'] ?? '';
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

