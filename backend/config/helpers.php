<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

loadEnv(__DIR__ . '/../config.env');

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

function response($data, $status = 200) {
    // Clear any output that might have been sent before
    if (ob_get_level() > 0) {
        ob_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function errorResponse($message, $status = 400) {
    response(['message' => $message], $status);
}

function successResponse($data, $message = null) {
    $response = ['data' => $data];
    if ($message) {
        $response['message'] = $message;
    }
    response($response);
}

function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    return $errors;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function setSessionUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = $user;
}

function getSessionUser() {
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

function clearSession() {
    session_unset();
    session_destroy();
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function cors() {
    header("Access-Control-Allow-Origin: http://localhost");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
