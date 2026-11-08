<?php
/* ========================================================
   NO CAP - CONFIGURATION
   Simple settings file - easy to edit and explain in viva
   ======================================================== */

// Database Settings (Standard Linux MySQL / MariaDB / XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 'nocap_store');
define('DB_USER', 'root');
define('DB_PASS', ''); // If MySQL root has a password, enter it here

// Store Settings
define('SITE_NAME', 'NO CAP');
define('CURRENCY', 'Rs. ');
define('SHIPPING_FEE', 200);
define('FREE_SHIPPING_ABOVE', 5000); // Free shipping on orders over this amount

// AI Assistant Settings (Optional)
// If you leave this empty, the built-in smart catalog search is used automatically.
define('ANTHROPIC_API_KEY', '');
define('ANTHROPIC_MODEL', 'claude-3-haiku-20240307');
define('AI_SSL_VERIFY', false); // Set to false for local testing

// Auto-detect project URL path (e.g. /nocap or empty if root)
$docRoot = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$appRoot = str_replace('\\', '/', (string)realpath(__DIR__ . '/..'));
$base = ($docRoot !== '' && stripos($appRoot, $docRoot) === 0) ? substr($appRoot, strlen($docRoot)) : '';
define('BASE_URL', rtrim($base, '/'));
