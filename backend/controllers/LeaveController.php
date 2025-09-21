<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Leave.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class LeaveController {
    private $leaveModel;
    private $userModel;
    private $authMiddleware;
    
    public function __construct() {
        $this->leaveModel = new Leave();
        $this->userModel = new User();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    public function create() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $user = $this->authMiddleware->authenticate();
            
            $input = json_decode(file_get_contents('php://input'), true);
            $input = sanitizeInput($input);
            
            // Validate required fields
            $requiredFields = ['leave_type', 'start_date', 'end_date', 'reason'];
            $errors = validateRequired($input, $requiredFields);
            
            if (!empty($errors)) {
                errorResponse(implode(', ', $errors), 400);
            }
            
            // Validate leave type
            $validTypes = ['sick', 'vacation', 'personal', 'emergency', 'other'];
            if (!in_array($input['leave_type'], $validTypes)) {
                errorResponse('Invalid leave type', 400);
            }
            
            // Validate dates
            $dateValidation = $this->leaveModel->validateLeaveDates($input['start_date'], $input['end_date']);
            if (!$dateValidation['valid']) {
                errorResponse($dateValidation['message'], 400);
            }
            
            // Validate reason length
            if (strlen($input['reason']) < 10) {
                errorResponse('Reason must be at least 10 characters', 400);
            }
            
            // Calculate duration
            $duration = $this->leaveModel->calculateDuration(
                $input['start_date'], 
                $input['end_date'], 
                $input['duration_unit'] ?? 'days'
            );
            
            $leaveData = [
                'employee_id' => $user['id'],
                'leave_type' => $input['leave_type'],
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'duration' => $duration,
                'duration_unit' => $input['duration_unit'] ?? 'days',
                'reason' => $input['reason']
            ];
            
            $leaveId = $this->leaveModel->create($leaveData);
            $leave = $this->leaveModel->findById($leaveId);
            
            successResponse($this->leaveModel->toArray($leave), 'Leave application submitted successfully');
            
        } catch (Exception $e) {
            errorResponse('Failed to submit leave application: ' . $e->getMessage(), 500);
        }
    }
    
    public function myLeaves() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $user = $this->authMiddleware->authenticate();
            $leaves = $this->leaveModel->findByEmployeeId($user['id']);
            
            $formattedLeaves = array_map([$this->leaveModel, 'toArray'], $leaves);
            successResponse($formattedLeaves);
            
        } catch (Exception $e) {
            errorResponse('Failed to fetch leaves: ' . $e->getMessage(), 500);
        }
    }
    
    public function allLeaves() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $this->authMiddleware->adminOnly(); // Only HR can access
            $leaves = $this->leaveModel->findAll();
            
            $formattedLeaves = array_map([$this->leaveModel, 'toArray'], $leaves);
            successResponse($formattedLeaves);
            
        } catch (Exception $e) {
            errorResponse('Failed to fetch leaves: ' . $e->getMessage(), 500);
        }
    }
    
    public function todayLeaves() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $this->authMiddleware->authenticate(); // Any authenticated user can access
            $leaves = $this->leaveModel->getTodayLeaves();
            
            $formattedLeaves = array_map([$this->leaveModel, 'toArray'], $leaves);
            successResponse($formattedLeaves);
            
        } catch (Exception $e) {
            errorResponse('Failed to fetch today\'s leaves: ' . $e->getMessage(), 500);
        }
    }
    
    public function approve() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $user = $this->authMiddleware->adminOnly();
            
            $leaveId = $_GET['id'] ?? null;
            if (!$leaveId) {
                errorResponse('Leave ID required', 400);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $adminComment = $input['admin_comment'] ?? null;
            
            $success = $this->leaveModel->approve($leaveId, $user['id'], $adminComment);
            
            if (!$success) {
                errorResponse('Leave request not found or already processed', 400);
            }
            
            $leave = $this->leaveModel->findById($leaveId);
            successResponse($this->leaveModel->toArray($leave), 'Leave request approved successfully');
            
        } catch (Exception $e) {
            errorResponse('Failed to approve leave: ' . $e->getMessage(), 500);
        }
    }
    
    public function reject() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $user = $this->authMiddleware->adminOnly();
            
            $leaveId = $_GET['id'] ?? null;
            if (!$leaveId) {
                errorResponse('Leave ID required', 400);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $rejectedReason = $input['rejected_reason'] ?? null;
            $adminComment = $input['admin_comment'] ?? null;
            
            if (!$rejectedReason || strlen(trim($rejectedReason)) < 5) {
                errorResponse('Rejection reason must be at least 5 characters', 400);
            }
            
            $success = $this->leaveModel->reject($leaveId, $user['id'], $rejectedReason, $adminComment);
            
            if (!$success) {
                errorResponse('Leave request not found or already processed', 400);
            }
            
            $leave = $this->leaveModel->findById($leaveId);
            successResponse($this->leaveModel->toArray($leave), 'Leave request rejected successfully');
            
        } catch (Exception $e) {
            errorResponse('Failed to reject leave: ' . $e->getMessage(), 500);
        }
    }
    
    public function getById() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $user = $this->authMiddleware->authenticate();
            
            $leaveId = $_GET['id'] ?? null;
            if (!$leaveId) {
                errorResponse('Leave ID required', 400);
            }
            
            $leave = $this->leaveModel->findById($leaveId);
            if (!$leave) {
                errorResponse('Leave request not found', 404);
            }
            
            // Check if user can access this leave request
            if ($user['role'] !== 'hr' && $leave['employee_id'] != $user['id']) {
                errorResponse('Access denied', 403);
            }
            
            successResponse($this->leaveModel->toArray($leave));
            
        } catch (Exception $e) {
            errorResponse('Failed to fetch leave: ' . $e->getMessage(), 500);
        }
    }
}
