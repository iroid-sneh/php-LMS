<?php

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Include all necessary files
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/routes/api.php';

// Handle the API request
try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    errorResponse('Server error: ' . $e->getMessage(), 500);
}
