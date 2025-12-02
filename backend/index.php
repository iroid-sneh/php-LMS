<?php

// Set error reporting but don't display errors as HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

date_default_timezone_set('UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set output buffering to catch any unexpected output
ob_start();

require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/routes/api.php';

try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    ob_clean(); // Clear any output before sending error
    errorResponse('Server error: ' . $e->getMessage(), 500);
} catch (Error $e) {
    ob_clean(); // Clear any output before sending error
    errorResponse('Fatal error: ' . $e->getMessage(), 500);
}
