<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/helpers.php';

class User {
    private $db;
    private $table = 'users';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, email, password, role, department, position, employee_id, phone, joining_date) 
                VALUES (:name, :email, :password, :role, :department, :position, :employee_id, :phone, :joining_date)";
        
        $params = [
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => hashPassword($data['password']),
            ':role' => $data['role'] ?? 'employee',
            ':department' => $data['department'],
            ':position' => $data['position'],
            ':employee_id' => $data['employee_id'],
            ':phone' => $data['phone'] ?? null,
            ':joining_date' => $data['joining_date'] ?? date('Y-m-d H:i:s')
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        return $this->db->fetch($sql, [':email' => $email]);
    }
    
    public function findByEmployeeId($employeeId) {
        $sql = "SELECT * FROM {$this->table} WHERE employee_id = :employee_id";
        return $this->db->fetch($sql, [':employee_id' => $employeeId]);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function findAll($role = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if ($role) {
            $sql .= " WHERE role = :role";
            $params[':role'] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function exists($email, $employeeId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email OR employee_id = :employee_id";
        $result = $this->db->fetch($sql, [':email' => $email, ':employee_id' => $employeeId]);
        return $result['count'] > 0;
    }
    
    public function getStats($userId) {
        $sql = "SELECT 
                    COUNT(*) as total_leaves,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_leaves,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_leaves,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_leaves
                FROM leaves WHERE employee_id = :user_id";
        
        return $this->db->fetch($sql, [':user_id' => $userId]);
    }
    
    public function getAdminStats() {
        $sql = "SELECT 
                    COUNT(*) as total_employees,
                    (SELECT COUNT(*) FROM leaves) as total_leaves,
                    (SELECT COUNT(*) FROM leaves WHERE status = 'pending') as pending_leaves,
                    (SELECT COUNT(*) FROM leaves WHERE status = 'approved') as approved_leaves,
                    (SELECT COUNT(*) FROM leaves WHERE status = 'rejected') as rejected_leaves,
                    (SELECT COUNT(*) FROM leaves WHERE status = 'approved' 
                     AND CURDATE() BETWEEN start_date AND end_date) as today_leaves
                FROM users WHERE role = 'employee'";
        
        return $this->db->fetch($sql);
    }
    
    public function getTodayLeavesDetails() {
        $sql = "SELECT l.*, u.name, u.email, u.employee_id, u.department, u.position 
                FROM leaves l 
                JOIN users u ON l.employee_id = u.id 
                WHERE l.status = 'approved' 
                AND CURDATE() BETWEEN l.start_date AND l.end_date
                ORDER BY l.start_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function verifyPassword($password, $hash) {
        return verifyPassword($password, $hash);
    }
    
    public function toArray($user) {
        if (!$user) return null;
        
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'department' => $user['department'],
            'position' => $user['position'],
            'employee_id' => $user['employee_id'],
            'phone' => $user['phone'],
            'joining_date' => $user['joining_date']
        ];
    }
}
