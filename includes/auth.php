<?php
require_once __DIR__ . '/db.php';

// Start the session securely (idempotent).
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * @param string|string[] $roles Allowed role(s).
 */
function require_role($roles): void
{
    require_login();

    $allowed = is_array($roles) ? $roles : [$roles];

    if (!in_array(current_role(), $allowed, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

/**
 * Verify credentials and log the user in.
 *
 * @return bool True on success.
 */
function login(string $email, string $password): bool
{
    $db = get_db();

    $stmt = $db->prepare(
        'SELECT id, password_hash, role FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role']    = $user['role'];
    $_SESSION['logged_in_at'] = time();

    return true;
}

function logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]
        );
    }

    session_regenerate_id(true);
    session_destroy();
}
