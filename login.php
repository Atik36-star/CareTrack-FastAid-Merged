<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

/**
 * Map a role to its dashboard path.
 */
function role_destination(?string $role): string
{
    switch ($role) {
        case 'care_staff':
        case 'admin':
            return '/caretrack/index.html';
        case 'patient':
            return '/fastaid/patient-dashboard.html';
        case 'volunteer':
            return '/fastaid/volunteer-dashboard.html';
        default:
            return '/caretrack/index.html';
    }
}

$error = '';

// Redirect already-logged-in users to their dashboard.
if (is_logged_in()) {
    header('Location: ' . role_destination(current_role()));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;

    try {
        csrf_verify($token);
    } catch (RuntimeException $e) {
        $error = 'Invalid or missing CSRF token. Please reload the page and try again.';
    }

    if ($error === '') {
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Please enter both your email and password.';
        } elseif (login($email, $password)) {
            header('Location: ' . role_destination(current_role()));
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$csrfToken = csrf_token();
$emailValue = isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '';
$errorHtml = $error !== ''
    ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - CareTrack / FastAid</title>
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; margin: 0; }
    .login-card { max-width: 380px; margin: 80px auto; background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,.1); }
    .login-card h1 { font-size: 22px; margin: 0 0 20px; text-align: center; }
    .form-group { margin-bottom: 16px; }
    label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 4px; }
    input[type="email"], input[type="password"] { width: 100%; box-sizing: border-box; padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; }
    button { width: 100%; padding: 10px; font-size: 15px; font-weight: 600; color: #fff; background: #dc3545; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #bb2d3b; }
    .alert { padding: 10px 12px; border-radius: 4px; font-size: 14px; margin-bottom: 16px; }
    .alert-danger { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
  </style>
</head>
<body>
  <div class="login-card">
    <h1>Login</h1>
    <?php echo $errorHtml; ?>
    <form method="post" action="login.php">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo $emailValue; ?>" required autocomplete="email">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit">Log In</button>
    </form>
  </div>
</body>
</html>
