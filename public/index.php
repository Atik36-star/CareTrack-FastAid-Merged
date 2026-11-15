<?php
require_once __DIR__ . '/../includes/auth.php';

if (!is_logged_in()) {
    header('Location: /login.php');
    exit;
}

switch (current_role()) {
    case 'care_staff':
    case 'admin':
        $destination = '/caretrack/index.php';
        break;
    case 'patient':
        $destination = '/fastaid/patient-dashboard.html';
        break;
    case 'volunteer':
        $destination = '/fastaid/volunteer-dashboard.html';
        break;
    default:
        $destination = '/caretrack/index.php';
}

header('Location: ' . $destination);
exit;
