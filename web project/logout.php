<?php
require_once __DIR__ . '/includes/init.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

// Start a fresh session for the goodbye message
session_start();
set_flash('success', 'You have been successfully signed out.');
redirect('index.php');
