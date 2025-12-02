<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/helpers.php';

class Leave {
    private $db;
    private $table = 'leaves';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (employee_id, leave_type, start_date, end_date, duration, duration_unit, reason, status, applied_at) 
                VALUES (:employee_id, :leave_type, :start_date, :end_date, :duration, :duration_unit, :reason, :status, :applied_at)";
        
        $params = [
            ':employee_id' => $data['employee_id'],
            ':leave_type' => $data['leave_type'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':duration' => $data['duration'],
            ':duration_unit' => $data['duration_unit'] ?? 'days',
            ':reason' => $data['reason'],
            ':status' => $data['status'] ?? 'pending',
            ':applied_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT l.*, u.name, u.email, u.employee_id as user_employee_id, u.department, u.position,
                       r.name as reviewed_by_name, r.email as reviewed_by_email
                FROM {$this->table} l 
                LEFT JOIN users u ON l.employee_id = u.id 
                LEFT JOIN users r ON l.reviewed_by = r.id
                WHERE l.id = :id";
        
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function findByEmployeeId($employeeId) {
        $sql = "SELECT l.*, u.name, u.email, u.employee_id as user_employee_id, u.department, u.position,
                       r.name as reviewed_by_name, r.email as reviewed_by_email
                FROM {$this->table} l 
                LEFT JOIN users u ON l.employee_id = u.id 
                LEFT JOIN users r ON l.reviewed_by = r.id
                WHERE l.employee_id = :employee_id 
                ORDER BY l.applied_at DESC";
        
        return $this->db->fetchAll($sql, [':employee_id' => $employeeId]);
    }
    
    public function findAll() {
        $sql = "SELECT l.*, u.name, u.email, u.employee_id as user_employee_id, u.department, u.position,
                       r.name as reviewed_by_name, r.email as reviewed_by_email
                FROM {$this->table} l 
                LEFT JOIN users u ON l.employee_id = u.id 
                LEFT JOIN users r ON l.reviewed_by = r.id
                ORDER BY l.applied_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getTodayLeaves() {
        $sql = "SELECT l.*, u.name, u.email, u.employee_id as user_employee_id, u.department, u.position
                FROM {$this->table} l 
                JOIN users u ON l.employee_id = u.id 
                WHERE l.status = 'approved' 
                AND CURDATE() BETWEEN l.start_date AND l.end_date
                ORDER BY l.start_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getActiveLeaves() {
        $sql = "SELECT l.*, u.name, u.email, u.employee_id as user_employee_id, u.department, u.position
                FROM {$this->table} l 
                JOIN users u ON l.employee_id = u.id 
                WHERE l.status = 'approved' 
                AND CURDATE() BETWEEN l.start_date AND l.end_date
                ORDER BY l.start_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getPendingLeaves() {
        $sql = "SELECT l.*, u.name, u.email, u.employee_id as user_employee_id, u.department, u.position
                FROM {$this->table} l 
                JOIN users u ON l.employee_id = u.id 
                WHERE l.status = 'pending'
                ORDER BY l.applied_at ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function approve($id, $reviewedBy, $adminComment = null) {
        $sql = "UPDATE {$this->table} 
                SET status = 'approved', 
                    reviewed_by = :reviewed_by, 
                    reviewed_at = :reviewed_at,
                    admin_comment = :admin_comment
                WHERE id = :id AND status = 'pending'";
        
        $params = [
            ':id' => $id,
            ':reviewed_by' => $reviewedBy,
            ':reviewed_at' => date('Y-m-d H:i:s'),
            ':admin_comment' => $adminComment
        ];
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    public function reject($id, $reviewedBy, $rejectedReason, $adminComment = null) {
        $sql = "UPDATE {$this->table} 
                SET status = 'rejected', 
                    reviewed_by = :reviewed_by, 
                    reviewed_at = :reviewed_at,
                    rejected_reason = :rejected_reason,
                    admin_comment = :admin_comment
                WHERE id = :id AND status = 'pending'";
        
        $params = [
            ':id' => $id,
            ':reviewed_by' => $reviewedBy,
            ':reviewed_at' => date('Y-m-d H:i:s'),
            ':rejected_reason' => $rejectedReason,
            ':admin_comment' => $adminComment
        ];
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    public function update($id, $data, $employeeId) {
        $sql = "UPDATE {$this->table} 
                SET leave_type = :leave_type, 
                    start_date = :start_date, 
                    end_date = :end_date,
                    duration = :duration,
                    duration_unit = :duration_unit,
                    reason = :reason
                WHERE id = :id AND employee_id = :employee_id";
        
        $params = [
            ':id' => $id,
            ':employee_id' => $employeeId,
            ':leave_type' => $data['leave_type'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':duration' => $data['duration'],
            ':duration_unit' => $data['duration_unit'] ?? 'days',
            ':reason' => $data['reason']
        ];
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    public function delete($id, $employeeId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE id = :id AND employee_id = :employee_id AND status = 'pending'";
        
        $params = [
            ':id' => $id,
            ':employee_id' => $employeeId
        ];
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    public function validateLeaveDates($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $start->setTime(0, 0, 0);
        
        if ($start >= $end) {
            return ['valid' => false, 'message' => 'End date must be after start date'];
        }
        
        if ($start < $today) {
            return ['valid' => false, 'message' => 'Cannot apply for leave in the past'];
        }
        
        return ['valid' => true];
    }
    
    public function calculateDuration($startDate, $endDate, $unit = 'days') {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end);
        
        if ($unit === 'hours') {
            return $diff->days * 24 + $diff->h;
        }
        
        return $diff->days + 1;
    }
    
    public function toArray($leave) {
        if (!$leave) return null;
        
        return [
            'id' => $leave['id'],
            'employee_id' => $leave['employee_id'],
            'leave_type' => $leave['leave_type'],
            'start_date' => $leave['start_date'],
            'end_date' => $leave['end_date'],
            'duration' => (float)$leave['duration'],
            'duration_unit' => $leave['duration_unit'],
            'reason' => $leave['reason'],
            'status' => $leave['status'],
            'admin_comment' => $leave['admin_comment'],
            'rejected_reason' => $leave['rejected_reason'],
            'applied_at' => $leave['applied_at'],
            'reviewed_at' => $leave['reviewed_at'],
            'employee' => [
                'id' => $leave['employee_id'],
                'name' => $leave['name'],
                'email' => $leave['email'],
                'employee_id' => $leave['user_employee_id'],
                'department' => $leave['department'],
                'position' => $leave['position']
            ],
            'reviewed_by' => $leave['reviewed_by_name'] ? [
                'name' => $leave['reviewed_by_name'],
                'email' => $leave['reviewed_by_email']
            ] : null
        ];
    }
}
