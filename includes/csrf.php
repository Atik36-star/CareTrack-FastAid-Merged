<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get (or generate once) the CSRF token for the current session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 *
 * @throws RuntimeException When the token is missing, malformed, or does not match.
 */
function csrf_verify($token): void
{
    $expected = $_SESSION['csrf_token'] ?? '';

    if (!is_string($token) || $token === '' || $expected === ''
        || !hash_equals($expected, $token)) {
        throw new RuntimeException('Invalid or missing CSRF token.');
    }
}
