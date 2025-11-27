<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/routes/api.php';

try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    errorResponse('Server error: ' . $e->getMessage(), 500);
}
