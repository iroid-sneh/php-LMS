<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {
    private $userModel;
    private $authMiddleware;
    
    public function __construct() {
        $this->userModel = new User();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    public function register() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Method not allowed', 405);
            return;
        }
        
        $jsonData = file_get_contents('php://input');
        $input = json_decode($jsonData, true);
        $input = sanitizeInput($input);
        
        $requiredFields = ['name', 'email', 'password', 'department', 'position', 'employee_id'];
        $errors = validateRequired($input, $requiredFields);
        
        if (!empty($errors)) {
            $errorMessage = implode(', ', $errors);
            errorResponse($errorMessage, 400);
            return;
        }
        
        $email = $input['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            errorResponse('Invalid email format', 400);
            return;
        }
        
        $password = $input['password'];
        if (strlen($password) < 6) {
            errorResponse('Password must be at least 6 characters', 400);
            return;
        }
        
        $emailExists = $this->userModel->exists($input['email'], $input['employee_id']);
        if ($emailExists) {
            errorResponse('User with this email or employee ID already exists', 400);
            return;
        }
        
        try {
            $userId = $this->userModel->create($input);
            $user = $this->userModel->findById($userId);
            
            setSessionUser($user);
            
            $responseData = array();
            $responseData['user'] = $this->userModel->toArray($user);
            
            successResponse($responseData, 'User registered successfully');
            
        } catch (Exception $e) {
            $errorMsg = 'Registration failed: ' . $e->getMessage();
            errorResponse($errorMsg, 500);
        }
    }
    
    public function login() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Method not allowed', 405);
            return;
        }
        
        $jsonData = file_get_contents('php://input');
        $input = json_decode($jsonData, true);
        $input = sanitizeInput($input);
        
        $requiredFields = ['email', 'password'];
        $errors = validateRequired($input, $requiredFields);
        
        if (!empty($errors)) {
            $errorMessage = implode(', ', $errors);
            errorResponse($errorMessage, 400);
            return;
        }
        
        $email = $input['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            errorResponse('Invalid email format', 400);
            return;
        }
        
        try {
            $user = $this->userModel->findByEmail($input['email']);
            
            if (!$user) {
                errorResponse('Invalid credentials', 400);
                return;
            }
            
            $passwordMatch = $this->userModel->verifyPassword($input['password'], $user['password']);
            if (!$passwordMatch) {
                errorResponse('Invalid credentials', 400);
                return;
            }
            
            setSessionUser($user);
            
            $responseData = array();
            $responseData['user'] = $this->userModel->toArray($user);
            
            successResponse($responseData, 'Login successful');
            
        } catch (Exception $e) {
            $errorMsg = 'Login failed: ' . $e->getMessage();
            errorResponse($errorMsg, 500);
        }
    }
    
    public function me() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
            return;
        }
        
        try {
            $user = $this->authMiddleware->authenticate();
            
            $responseData = array();
            $responseData['user'] = $this->userModel->toArray($user);
            
            successResponse($responseData);
            
        } catch (Exception $e) {
            $errorMsg = 'Authentication failed: ' . $e->getMessage();
            errorResponse($errorMsg, 401);
        }
    }
    
    public function logout() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Method not allowed', 405);
            return;
        }
        
        clearSession();
        successResponse([], 'Logged out successfully');
    }
}
