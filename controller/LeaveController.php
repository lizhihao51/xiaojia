<?php
// 请假控制器

require_once 'config/db.php';
require_once 'config/config.php';
require_once 'model/LeaveModel.php';
require_once 'model/UserModel.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

class LeaveController {
    private $leaveModel;
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->db = connect_db();
        $this->leaveModel = new LeaveModel($this->db);
        $this->userModel = new UserModel($this->db);
    }
    
    // 学生申请请假
    public function apply() {
        if (!Auth::isStudent()) {
            error_message('权限不足');
            redirect('index.php?controller=user&action=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::user();
            $type = post_param('type');
            $start_date = post_param('start_date');
            $end_date = post_param('end_date');
            $reason = post_param('reason');
            
            if (empty($type) || empty($start_date) || empty($end_date) || empty($reason)) {
                error_message('请填写完整信息');
                redirect('index.php?controller=leave&action=apply');
            }
            
            // 检查日期有效性
            if (strtotime($start_date) >= strtotime($end_date)) {
                error_message('结束时间必须晚于开始时间');
                redirect('index.php?controller=leave&action=apply');
            }
            
            $result = $this->leaveModel->createLeave($user['id'], $type, $start_date, $end_date, $reason);
            
            if ($result) {
                success_message('请假申请提交成功，等待审核');
                redirect('index.php?controller=student&action=leaves');
            } else {
                error_message('请假申请提交失败');
                redirect('index.php?controller=leave&action=apply');
            }
        } else {
            include 'view/student/apply_leave.php';
        }
    }
    
    // 辅导员审核请假申请
    public function approve() {
        if (!Auth::isAdvisor()) {
            error_message('权限不足');
            redirect('index.php?controller=user&action=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leave_id = post_param('leave_id');
            $action = post_param('action'); // approve or reject
            $user = Auth::user();
            
            if (empty($leave_id) || empty($action)) {
                error_message('参数错误');
                redirect('index.php?controller=advisor&action=pending_leaves');
            }
            
            $status = ($action === 'approve') ? LEAVE_APPROVED : LEAVE_REJECTED;
            $result = $this->leaveModel->updateLeaveStatus($leave_id, $status, $user['id']);
            
            if ($result) {
                success_message(($action === 'approve' ? '批准' : '拒绝') . '操作成功');
                redirect('index.php?controller=advisor&action=pending_leaves');
            } else {
                error_message('操作失败');
                redirect('index.php?controller=advisor&action=pending_leaves');
            }
        }
    }
    
    // 显示待审核的请假申请
    public function pending() {
        if (!Auth::isAdvisor()) {
            error_message('权限不足');
            redirect('index.php?controller=user&action=login');
        }
        
        $leaves = $this->leaveModel->getPendingLeaves();
        include 'view/advisor/pending_leaves.php';
    }
    
    // 显示所有已批准的请假（用于销假）
    public function approved() {
        if (!Auth::isStudent()) {
            error_message('权限不足');
            redirect('index.php?controller=user&action=login');
        }
        
        $user = Auth::user();
        $leaves = $this->leaveModel->getApprovedLeaves();
        // 只显示当前学生的已批准请假
        $my_leaves = array_filter($leaves, function($leave) use ($user) {
            return $leave['student_id'] == $user['id'];
        });
        include 'view/student/approved_leaves.php';
    }
}