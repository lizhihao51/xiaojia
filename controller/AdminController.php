<?php
// 管理员控制器

require_once 'config/db.php';
require_once 'model/CheckinModel.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

class AdminController {
    private $checkinModel;
    private $db;
    
    public function __construct() {
        $this->db = connect_db();
        $this->checkinModel = new CheckinModel($this->db);
    }
    
    // 管理员仪表板
    public function dashboard() {
        // 权限检查已移至入口文件
        
        // 获取统计信息
        $tasks = $this->checkinModel->getAllTasks();
        $classes = $this->checkinModel->getAllClasses();
        $enrollment_years = $this->checkinModel->getAllEnrollmentYears();
        
        include 'view/admin/dashboard.php';
    }
    
    // 创建销假任务页面
    public function createTask() {
        // 权限检查已移至入口文件
        
        $classes = $this->checkinModel->getAllClasses();
        $enrollment_years = $this->checkinModel->getAllEnrollmentYears();
        $advisors = $this->checkinModel->getAllAdvisors();
        
        include 'view/admin/create_task.php';
    }
    
    // 处理创建销假任务
    public function doCreateTask() {
        // 权限检查已移至入口文件
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = post_param('title');
            $description = post_param('description');
            $target_type = post_param('target_type'); // class 或 year
            $target_classes = post_param('target_classes', []);
            $target_year = post_param('target_year');
            $assigned_advisors = post_param('assigned_advisors', []);
            
            if (empty($title)) {
                error_message('请填写任务标题');
                redirect('index.php?controller=admin&action=create_task');
            }
            
            $target_class_ids = [];
            if ($target_type === 'class' && !empty($target_classes)) {
                $target_class_ids = $target_classes;
            } else if ($target_type === 'year' && !empty($target_year)) {
                // 获取该入学年的所有班级
                $classes = $this->checkinModel->getClassesByEnrollmentYear($target_year);
                foreach ($classes as $class) {
                    $target_class_ids[] = $class['id'];
                }
            }
            
            if (empty($target_class_ids)) {
                error_message('请选择目标班级');
                redirect('index.php?controller=admin&action=create_task');
            }
            
            $user = Auth::user();
            $result = $this->checkinModel->createTask($title, $description, $user['id'], $target_class_ids, $assigned_advisors);
            
            if ($result) {
                success_message('销假任务创建成功');
                redirect('index.php?controller=admin&action=tasks');
            } else {
                error_message('销假任务创建失败');
                redirect('index.php?controller=admin&action=create_task');
            }
        } else {
            redirect('index.php?controller=admin&action=create_task');
        }
    }
    
    // 查看所有销假任务
    public function tasks() {
        // 权限检查已移至入口文件
        
        $tasks = $this->checkinModel->getAllTasks();
        include 'view/admin/tasks.php';
    }
    
    // 查看任务详情
    public function taskDetail() {
        // 权限检查已移至入口文件
        
        $task_id = get_param('id');
        if (empty($task_id)) {
            error_message('参数错误');
            redirect('index.php?controller=admin&action=tasks');
        }
        
        $task = $this->checkinModel->getTaskById($task_id);
        if (!$task) {
            error_message('任务不存在');
            redirect('index.php?controller=admin&action=tasks');
        }
        
        $records = $this->checkinModel->getRecordsByTask($task_id);
        $classes = $this->checkinModel->getAllClasses();
        $class_statistics = $this->checkinModel->getTaskClassStatistics($task_id);
        $advisors = $this->checkinModel->getAllAdvisors();
        $status_config = $this->checkinModel->getStatusConfig();
        
        include 'view/admin/task_detail.php';
    }
    
    // 查看所有销假记录
    public function allCheckinRecords() {
        // 权限检查已移至入口文件
        
        $records = $this->checkinModel->getAllCheckinRecords();
        $status_config = $this->checkinModel->getStatusConfig();
        include 'view/admin/all_checkin_records.php';
    }
    
    // 结束销假任务
    public function endTask() {
        // 权限检查已移至入口文件
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $task_id = post_param('task_id');
            
            if (empty($task_id)) {
                error_message('参数错误');
                redirect('index.php?controller=admin&action=tasks');
            }
            
            $result = $this->checkinModel->updateTaskStatus($task_id, 2); // 2表示已完成
            
            if ($result) {
                success_message('销假任务已结束');
            } else {
                error_message('结束任务失败');
            }
            
            redirect('index.php?controller=admin&action=tasks');
        } else {
            redirect('index.php?controller=admin&action=tasks');
        }
    }
    
    // 导出任务数据
    public function exportTask() {
        // 权限检查已移至入口文件
        
        $task_id = get_param('id');
        $export_type = get_param('type', 'all'); // all表示导出全部，class表示导出班级
        
        if (empty($task_id)) {
            error_message('参数错误');
            redirect('index.php?controller=admin&action=tasks');
        }
        
        $task = $this->checkinModel->getTaskById($task_id);
        if (!$task) {
            error_message('任务不存在');
            redirect('index.php?controller=admin&action=tasks');
        }
        
        $target_classes = json_decode($task['target_classes'], true);
        $class_map = [];
        $classes = $this->checkinModel->getAllClasses();
        foreach ($classes as $class) {
            $class_map[$class['id']] = $class;
        }
        
        // 设置响应头以下载CSV文件
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="task_' . $task_id . '_' . $export_type . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // 添加BOM以支持中文
        fwrite($output, "\xEF\xBB\xBF");
        
        // 获取状态配置
        $status_config = $this->checkinModel->getStatusConfig();
        $status_map = $status_config['status'];
        
        if ($export_type === 'all') {
            // 导出全部数据
            fputcsv($output, ['任务标题', '班级', '学生姓名', '状态', '备注', '销假辅导员', '销假时间']);
            
            $records = $this->checkinModel->getRecordsByTask($task_id);
            foreach ($records as $record) {
                $status_text = isset($status_map[$record['status']]) ? $status_map[$record['status']] : '未知';
                
                fputcsv($output, [
                    $task['title'],
                    $record['class_name'],
                    $record['student_name'],
                    $status_text,
                    $record['remark'],
                    $record['advisor_name'],
                    $record['updated_at']
                ]);
            }
        } else if ($export_type === 'class' && isset($_GET['class_id'])) {
            // 导出特定班级数据
            $class_id = $_GET['class_id'];
            if (!in_array($class_id, $target_classes)) {
                error_message('无效的班级ID');
                redirect('index.php?controller=admin&action=task_detail&id=' . $task_id);
            }
            
            $class_name = $class_map[$class_id]['name'];
            fputcsv($output, ['任务标题', '班级', '学生姓名', '状态', '备注', '销假辅导员', '销假时间']);
            
            $records = $this->checkinModel->getRecordsByTaskAndClass($task_id, $class_id);
            foreach ($records as $record) {
                $status_text = isset($status_map[$record['status']]) ? $status_map[$record['status']] : '未知';
                
                fputcsv($output, [
                    $task['title'],
                    $class_name,
                    $record['student_name'],
                    $status_text,
                    $record['remark'],
                    $record['advisor_name'],
                    $record['updated_at']
                ]);
            }
        }
        
        fclose($output);
        exit();
    }
}