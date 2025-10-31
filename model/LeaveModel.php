<?php
// 请假模型类

class LeaveModel {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // 创建请假申请
    public function createLeave($student_id, $type, $start_date, $end_date, $reason, $attachment = null) {
        $query = "INSERT INTO leaves (student_id, type, start_date, end_date, reason, attachment) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sissss", $student_id, $type, $start_date, $end_date, $reason, $attachment);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }
    
    // 获取所有请假申请
    public function getAllLeaves() {
        $query = "SELECT l.*, u.name as student_name FROM leaves l JOIN users u ON l.student_id = u.id ORDER BY l.created_at DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 根据学生ID获取请假申请
    public function getLeavesByStudent($student_id) {
        $query = "SELECT * FROM leaves WHERE student_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 根据ID获取请假申请详情
    public function getLeaveById($id) {
        $query = "SELECT l.*, u.name as student_name FROM leaves l JOIN users u ON l.student_id = u.id WHERE l.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // 更新请假申请状态
    public function updateLeaveStatus($id, $status, $approver_id = null) {
        $query = "UPDATE leaves SET status = ?, approver_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $status, $approver_id, $id);
        return $stmt->execute();
    }
    
    // 获取待审核的请假申请
    public function getPendingLeaves() {
        $query = "SELECT l.*, u.name as student_name FROM leaves l JOIN users u ON l.student_id = u.id WHERE l.status = ? ORDER BY l.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $status = LEAVE_PENDING;
        $stmt->bind_param("i", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取已批准但未销假的请假申请
    public function getApprovedLeaves() {
        $query = "SELECT l.*, u.name as student_name FROM leaves l JOIN users u ON l.student_id = u.id WHERE l.status = ? ORDER BY l.start_date ASC";
        $stmt = $this->conn->prepare($query);
        $status = LEAVE_APPROVED;
        $stmt->bind_param("i", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}