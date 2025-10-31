<?php
// 辅导员销假控制器

require_once 'config/db.php';
require_once 'config/config.php';
require_once 'model/CheckinModel.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

class CheckinController {
    private $checkinModel;
    private $db;
    
    public function __construct() {
        $this->db = connect_db();
        $this->checkinModel = new CheckinModel($this->db);
    }
    
    // 辅导员仪表板
    public function dashboard() {
        // 权限检查已移至入口文件
        
        $user = Auth::user();
        // 辅导员可以查看所有销假任务
        $tasks = $this->checkinModel->getAllTasks();
        
        include 'view/advisor/dashboard.php';
    }
    
    // 查看任务详情并进行销假操作
    public function taskDetail() {
        // 权限检查已移至入口文件
        
        $task_id = get_param('id');
        if (empty($task_id)) {
            error_message('参数错误');
            redirect('index.php?controller=checkin&action=dashboard');
        }
        
        $task = $this->checkinModel->getTaskById($task_id);
        if (!$task) {
            error_message('任务不存在');
            redirect('index.php?controller=checkin&action=dashboard');
        }
        
        // 检查当前辅导员是否被分配到此任务
        $assigned_advisors = json_decode($task['assigned_advisors'], true);
        $user = Auth::user();
        if (!empty($assigned_advisors) && !in_array($user['id'], $assigned_advisors)) {
            error_message('您未被分配到此任务');
            redirect('index.php?controller=checkin&action=dashboard');
        }
        
        // 获取任务目标班级
        $target_classes = json_decode($task['target_classes'], true);
        
        // 获取所有目标班级的学生列表
        $all_students = [];
        foreach ($target_classes as $class_id) {
            $students = $this->checkinModel->getStudentsByClass($class_id);
            $all_students = array_merge($all_students, $students);
        }
        
        error_log("获取到的学生列表: " . print_r($all_students, true));
        
        // 获取已有的销假记录
        $records = $this->checkinModel->getRecordsByTask($task_id);
        error_log("获取到的销假记录: " . print_r($records, true));
        
        // 将记录转换为以学生ID为键的数组，方便查找
        $record_map = [];
        foreach ($records as $record) {
            $record_map[$record['student_id']] = $record;
        }
        
        error_log("记录映射表: " . print_r($record_map, true));
        
        // 获取所有班级信息用于显示
        $classes = $this->checkinModel->getAllClasses();
        $class_map = [];
        foreach ($classes as $class) {
            $class_map[$class['id']] = $class;
        }
        
        include 'view/advisor/task_detail.php';
    }
    
    // 处理销假操作
    public function doCheckin() {
        // 权限检查已移至入口文件
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("=== 销假记录提交开始 ===");
            error_log("完整_POST数据: " . print_r($_POST, true));
            
            $task_id = post_param('task_id');
            $records_data = post_param('records', []);
            
            error_log("任务ID: " . $task_id);
            error_log("记录数据: " . print_r($records_data, true));
            
            if (empty($task_id)) {
                error_message('参数错误');
                error_log("错误: 任务ID为空");
                redirect('index.php?controller=checkin&action=dashboard');
                return;
            }
            
            // 构建销假记录数组
            $records = [];
            
            // 检查是否有记录数据
            if (!empty($records_data)) {
                foreach ($records_data as $student_id => $record_data) {
                    // 只处理被选中的学生（有student_id字段）
                    if (isset($record_data['student_id'])) {
                        $status = isset($record_data['status']) ? $record_data['status'] : '1'; // 默认为1（到课）
                        $remark = isset($record_data['remark']) ? $record_data['remark'] : '';
                        
                        error_log("处理记录: 学生ID=$student_id, 状态=$status, 备注=$remark");
                        
                        $records[] = [
                            'student_id' => $student_id,
                            'status' => $status,
                            'remark' => $remark
                        ];
                    } else {
                        error_log("跳过记录: 学生ID=$student_id, 缺少student_id字段");
                    }
                }
            } else {
                error_log("没有records数据");
            }
            
            error_log("最终记录数组: " . print_r($records, true));
            
            if (empty($records)) {
                error_message('请选择学生进行销假');
                error_log("错误: 没有选择学生进行销假");
                redirect('index.php?controller=checkin&action=task_detail&id=' . $task_id);
                return;
            }
            
            // 批量保存销假记录
            $user = Auth::user();
            error_log("辅导员信息: " . print_r($user, true));
            
            $result = $this->checkinModel->createRecordsBatch($task_id, $user['id'], $records);
            
            if ($result) {
                success_message('销假记录保存成功');
                error_log("销假记录保存成功");
            } else {
                error_message('销假记录保存失败');
                error_log("销假记录保存失败");
            }
            
            error_log("=== 销假记录提交结束 ===");
            redirect('index.php?controller=checkin&action=task_detail&id=' . $task_id);
        } else {
            redirect('index.php?controller=checkin&action=dashboard');
        }
    }
    
    // 导出任务数据（辅导员版）
    public function exportTask() {
        // 权限检查已移至入口文件
        
        $task_id = get_param('id');
        $export_type = get_param('type', 'all'); // all表示导出全部，class表示导出班级
        
        if (empty($task_id)) {
            error_message('参数错误');
            redirect('index.php?controller=checkin&action=dashboard');
        }
        
        $task = $this->checkinModel->getTaskById($task_id);
        if (!$task) {
            error_message('任务不存在');
            redirect('index.php?controller=checkin&action=dashboard');
        }
        
        // 检查当前辅导员是否被分配到此任务
        $assigned_advisors = json_decode($task['assigned_advisors'], true);
        $user = Auth::user();
        if (!empty($assigned_advisors) && !in_array($user['id'], $assigned_advisors)) {
            error_message('您未被分配到此任务');
            redirect('index.php?controller=checkin&action=dashboard');
        }
        
        $target_classes = json_decode($task['target_classes'], true);
        $class_map = [];
        $classes = $this->checkinModel->getAllClasses();
        foreach ($classes as $class) {
            $class_map[$class['id']] = $class;
        }
        
        // 设置响应头以下载CSV文件
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="task_' . $task_id . '_' . $export_type . '_advisor.csv"');
        
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
                redirect('index.php?controller=checkin&action=task_detail&id=' . $task_id);
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