<?php
// 销假任务模型类

class CheckinModel {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // 创建销假任务
    public function createTask($title, $description, $creator_id, $target_classes, $assigned_advisors = []) {
        $query = "INSERT INTO checkin_tasks (title, description, creator_id, target_classes, assigned_advisors) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $target_classes_json = json_encode($target_classes);
        $assigned_advisors_json = json_encode($assigned_advisors);
        $stmt->bind_param("ssiss", $title, $description, $creator_id, $target_classes_json, $assigned_advisors_json);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }
    
    // 获取所有销假任务
    public function getAllTasks() {
        $query = "SELECT ct.*, u.name as creator_name FROM checkin_tasks ct JOIN users u ON ct.creator_id = u.id ORDER BY ct.created_at DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 根据ID获取销假任务详情
    public function getTaskById($id) {
        $query = "SELECT ct.*, u.name as creator_name FROM checkin_tasks ct JOIN users u ON ct.creator_id = u.id WHERE ct.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // 更新任务状态
    public function updateTaskStatus($id, $status) {
        $query = "UPDATE checkin_tasks SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $status, $id);
        return $stmt->execute();
    }
    
    // 获取指定班级的销假任务
    public function getTasksByClass($class_id) {
        $query = "SELECT * FROM checkin_tasks WHERE JSON_CONTAINS(target_classes, ?)";
        $stmt = $this->conn->prepare($query);
        $class_id_json = json_encode($class_id);
        $stmt->bind_param("s", $class_id_json);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取指定入学年的销假任务
    public function getTasksByEnrollmentYear($year_id) {
        // 先获取该入学年的所有班级
        $query = "SELECT id FROM classes WHERE enrollment_year_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $year_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $class_ids = [];
        while ($row = $result->fetch_assoc()) {
            $class_ids[] = $row['id'];
        }
        
        if (empty($class_ids)) {
            return [];
        }
        
        // 构建查询条件
        $conditions = [];
        foreach ($class_ids as $class_id) {
            $conditions[] = "JSON_CONTAINS(target_classes, '" . json_encode($class_id) . "')";
        }
        $condition_str = implode(' OR ', $conditions);
        
        $query = "SELECT ct.*, u.name as creator_name FROM checkin_tasks ct JOIN users u ON ct.creator_id = u.id WHERE " . $condition_str . " ORDER BY ct.created_at DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 创建销假记录
    public function createRecord($task_id, $student_id, $advisor_id, $status, $remark = null) {
        error_log("创建销假记录: task_id=$task_id, student_id=$student_id, advisor_id=$advisor_id, status=$status, remark=$remark");
        
        $query = "INSERT INTO checkin_records (task_id, student_id, advisor_id, status, remark) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), remark = VALUES(remark), advisor_id = VALUES(advisor_id)";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("预处理语句失败: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("iiiss", $task_id, $student_id, $advisor_id, $status, $remark);
        
        if (!$stmt->execute()) {
            error_log("执行SQL失败: " . $stmt->error);
            return false;
        }
        
        error_log("销假记录创建成功");
        return true;
    }
    
    // 批量创建销假记录
    public function createRecordsBatch($task_id, $advisor_id, $records) {
        error_log("开始批量创建销假记录: task_id=$task_id, advisor_id=$advisor_id");
        error_log("记录详情: " . print_r($records, true));
        
        $this->conn->begin_transaction();
        try {
            foreach ($records as $record) {
                error_log("处理记录: " . print_r($record, true));
                $result = $this->createRecord($task_id, $record['student_id'], $advisor_id, $record['status'], $record['remark']);
                if (!$result) {
                    throw new Exception("创建销假记录失败: " . print_r($record, true));
                }
            }
            $this->conn->commit();
            error_log("批量创建销假记录成功");
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("批量创建销假记录失败: " . $e->getMessage());
            return false;
        }
    }
    
    // 获取任务的销假记录
    public function getRecordsByTask($task_id) {
        $query = "SELECT cr.*, s.name as student_name, c.name as class_name, u.name as advisor_name
                  FROM checkin_records cr 
                  JOIN students s ON cr.student_id = s.id 
                  JOIN classes c ON s.class_id = c.id 
                  JOIN users u ON cr.advisor_id = u.id
                  WHERE cr.task_id = ? 
                  ORDER BY c.id, s.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取指定任务和班级的销假记录
    public function getRecordsByTaskAndClass($task_id, $class_id) {
        $query = "SELECT cr.*, s.name as student_name, u.name as advisor_name
                  FROM checkin_records cr 
                  JOIN students s ON cr.student_id = s.id 
                  JOIN users u ON cr.advisor_id = u.id
                  WHERE cr.task_id = ? AND s.class_id = ? 
                  ORDER BY s.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $task_id, $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取学生列表通过班级ID
    public function getStudentsByClass($class_id) {
        $query = "SELECT * FROM students WHERE class_id = ? ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取所有班级
    public function getAllClasses() {
        $query = "SELECT c.*, ey.year as enrollment_year 
                  FROM classes c 
                  JOIN enrollment_years ey ON c.enrollment_year_id = ey.id 
                  ORDER BY ey.year DESC, c.name";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取所有入学年
    public function getAllEnrollmentYears() {
        $query = "SELECT * FROM enrollment_years ORDER BY year DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 根据入学年获取班级
    public function getClassesByEnrollmentYear($year_id) {
        $query = "SELECT * FROM classes WHERE enrollment_year_id = ? ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $year_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取所有销假记录（用于管理员查看）
    public function getAllCheckinRecords() {
        $query = "SELECT cr.*, s.name as student_name, c.name as class_name, ct.title as task_title, u.name as advisor_name
                  FROM checkin_records cr
                  JOIN students s ON cr.student_id = s.id
                  JOIN classes c ON s.class_id = c.id
                  JOIN checkin_tasks ct ON cr.task_id = ct.id
                  JOIN users u ON cr.advisor_id = u.id
                  ORDER BY cr.created_at DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取所有辅导员
    public function getAllAdvisors() {
        $query = "SELECT * FROM users WHERE role = 2 ORDER BY name";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取任务的班级统计信息
    public function getTaskClassStatistics($task_id) {
        $query = "SELECT c.name as class_name, c.id as class_id,
                  SUM(CASE WHEN cr.status = 1 THEN 1 ELSE 0 END) as arrived_count,
                  SUM(CASE WHEN cr.status = 2 THEN 1 ELSE 0 END) as leave_count,
                  SUM(CASE WHEN cr.status = 3 THEN 1 ELSE 0 END) as other_count,
                  COUNT(s.id) as total_students
                  FROM classes c
                  LEFT JOIN students s ON c.id = s.class_id
                  LEFT JOIN checkin_records cr ON s.id = cr.student_id AND cr.task_id = ?
                  WHERE c.id IN (SELECT JSON_EXTRACT(target_classes, '$[*]') FROM checkin_tasks WHERE id = ?)
                  GROUP BY c.id, c.name
                  ORDER BY c.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $task_id, $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // 获取状态配置
    public function getStatusConfig() {
        $config_file = 'config/status.json';
        if (file_exists($config_file)) {
            return json_decode(file_get_contents($config_file), true);
        }
        return [
            'status' => [
                1 => '到课',
                2 => '请假',
                3 => '其他'
            ],
            'status_colors' => [
                1 => 'success',
                2 => 'warning',
                3 => 'info'
            ]
        ];
    }
}