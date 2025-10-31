<?php
require_once 'config/config.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

if (!Auth::isAdvisor()) {
    redirect('index.php?controller=user&action=login');
}

$user = Auth::user();

// 读取状态配置
$status_config = json_decode(file_get_contents('config/status.json'), true);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - 处理销假</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><?php echo SITE_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">欢迎, <?php echo $user['name']; ?></span>
                <a class="nav-link d-inline-block" href="index.php?controller=user&action=logout">退出</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="index.php?controller=checkin&action=dashboard" class="list-group-item list-group-item-action">首页</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>处理销假 - <?php echo htmlspecialchars($task['title']); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <div class="mb-3">
                            <p><?php echo htmlspecialchars($task['description'] ?? '无'); ?></p>
                            <div>
                                <strong>任务状态:</strong> 
                                <?php if ($task['status'] == 1): ?>
                                    <span class="badge bg-primary">进行中</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">已完成</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($task['status'] == 2): ?>
                            <div class="alert alert-warning">此任务已结束，无法再进行销假操作。</div>
                        <?php else: ?>
                            <div class="mb-3">
                                <h5>导出数据</h5>
                                <a href="index.php?controller=checkin&action=export_task&id=<?php echo $task['id']; ?>&type=all" class="btn btn-success">导出全部数据</a>
                                
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-info dropdown-toggle" type="button" id="exportClassDropdown" data-bs-toggle="dropdown">
                                        导出班级数据
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportClassDropdown">
                                        <?php 
                                        $target_classes = json_decode($task['target_classes'], true);
                                        foreach ($target_classes as $class_id): ?>
                                            <?php 
                                            $class_name = '';
                                            foreach ($classes as $class) {
                                                if ($class['id'] == $class_id) {
                                                    $class_name = $class['name'];
                                                    break;
                                                }
                                            }
                                            ?>
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="index.php?controller=checkin&action=export_task&id=<?php echo $task['id']; ?>&type=class&class_id=<?php echo $class_id; ?>">
                                                    <?php echo htmlspecialchars($class_name); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <form method="POST" action="index.php?controller=checkin&action=do_checkin">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                
                                <?php if (empty($all_students)): ?>
                                    <p>目标班级暂无学生。</p>
                                <?php else: ?>
                                    <ul class="nav nav-tabs" id="classTabs" role="tablist">
                                        <?php 
                                        $first = true;
                                        $class_tabs = [];
                                        foreach ($all_students as $student) {
                                            $class_id = $student['class_id'];
                                            if (!isset($class_tabs[$class_id])) {
                                                $class_tabs[$class_id] = $class_map[$class_id];
                                            }
                                        }
                                        
                                        foreach ($class_tabs as $class_id => $class): 
                                        ?>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                                        id="class-<?php echo $class_id; ?>-tab" 
                                                        data-bs-toggle="tab" 
                                                        data-bs-target="#class-<?php echo $class_id; ?>" 
                                                        type="button" 
                                                        role="tab">
                                                    <?php echo htmlspecialchars($class['name']); ?>
                                                </button>
                                            </li>
                                        <?php 
                                            $first = false;
                                        endforeach; 
                                        ?>
                                    </ul>
                                    
                                    <div class="tab-content" id="classTabsContent">
                                        <?php 
                                        $first = true;
                                        foreach ($class_tabs as $class_id => $class): 
                                            // 筛选出该班级的学生
                                            $class_students = array_filter($all_students, function($student) use ($class_id) {
                                                return $student['class_id'] == $class_id;
                                            });
                                        ?>
                                            <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                                                 id="class-<?php echo $class_id; ?>" 
                                                 role="tabpanel">
                                                <div class="mb-3">
                                                    <button type="button" class="btn btn-lg btn-outline-primary select-all-btn" 
                                                            data-class-id="<?php echo $class_id; ?>">全选当前班级</button>
                                                    <button type="button" class="btn btn-lg btn-outline-secondary deselect-all-btn" 
                                                            data-class-id="<?php echo $class_id; ?>">取消全选</button>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-fixed-header">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>选择</th>
                                                                <th>学生姓名</th>
                                                                <th>备注</th>
                                                                <th>状态</th>
                                                                <th>详细备注</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($class_students as $student): ?>
                                                                <?php 
                                                                // 获取该学生的销假记录（如果已存在）
                                                                $existing_record = isset($record_map[$student['id']]) ? $record_map[$student['id']] : null;
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="records[<?php echo $student['id']; ?>][student_id]" value="<?php echo $student['id']; ?>" 
                                                                               data-class-id="<?php echo $class_id; ?>"
                                                                               class="student-checkbox"
                                                                               <?php echo $existing_record ? 'checked' : ''; ?>>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                                    <td><?php echo htmlspecialchars($student['remark'] ?? '无'); ?></td>
                                                                    <td>
                                                                        <select name="records[<?php echo $student['id']; ?>][status]" class="form-select status-select" 
                                                                                data-student-id="<?php echo $student['id']; ?>">
                                                                            <?php foreach ($status_config['status'] as $status_id => $status_name): ?>
                                                                                <option value="<?php echo $status_id; ?>" 
                                                                                        <?php echo ($existing_record && $existing_record['status'] == $status_id) ? 'selected' : ''; ?>
                                                                                        class="status-option-<?php echo $status_id; ?>">
                                                                                    <?php echo $status_name; ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="records[<?php echo $student['id']; ?>][remark]" class="form-control" 
                                                                               value="<?php echo $existing_record ? htmlspecialchars($existing_record['remark'] ?? '') : ''; ?>">
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php 
                                            $first = false;
                                        endforeach; 
                                        ?>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg">保存销假记录</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                        
                        <a href="index.php?controller=checkin&action=dashboard" class="btn btn-secondary mt-3">返回首页</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 全选/取消全选功能
        document.querySelectorAll('.select-all-btn').forEach(button => {
            button.addEventListener('click', function() {
                const classId = this.getAttribute('data-class-id');
                console.log('全选班级:', classId);
                document.querySelectorAll(`input[data-class-id="${classId}"]`).forEach(checkbox => {
                    checkbox.checked = true;
                });
            });
        });
        
        document.querySelectorAll('.deselect-all-btn').forEach(button => {
            button.addEventListener('click', function() {
                const classId = this.getAttribute('data-class-id');
                console.log('取消全选班级:', classId);
                document.querySelectorAll(`input[data-class-id="${classId}"]`).forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        });
        
        // 状态选择时添加颜色提示
        document.querySelectorAll('.status-select').forEach(select => {
            // 初始化颜色
            updateStatusColor(select);
            
            // 监听变化
            select.addEventListener('change', function() {
                console.log('状态选择变更:', this.value, '学生ID:', this.getAttribute('data-student-id'));
                updateStatusColor(this);
            });
        });
        
        function updateStatusColor(select) {
            const selectedValue = select.value;
            console.log('更新状态颜色:', selectedValue);
            // 移除所有可能的状态类
            select.classList.remove('status-arrived', 'status-leave', 'status-other', 'status-danger', 'status-secondary');
            // 添加对应的状态类
            switch(selectedValue) {
                case '1':
                    select.classList.add('status-arrived');
                    break;
                case '2':
                    select.classList.add('status-leave');
                    break;
                case '3':
                    select.classList.add('status-other');
                    break;
                case '4':
                    select.classList.add('status-danger');
                    break;
                case '5':
                    select.classList.add('status-secondary');
                    break;
            }
        }
        
        // 监听标签页切换事件，确保表单元素状态正确
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (e) {
                console.log('标签页切换:', e.target.getAttribute('data-bs-target'));
                // 当标签页切换完成时，重新初始化当前标签页内的所有状态选择框
                const targetPanel = document.querySelector(e.target.getAttribute('data-bs-target'));
                if (targetPanel) {
                    targetPanel.querySelectorAll('.status-select').forEach(select => {
                        updateStatusColor(select);
                    });
                }
            });
        });
        
        // 恢复为传统的表单提交方式，但添加详细的调试信息
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('=== 表单提交开始 ===');
            
            // 检查表单数据
            const formData = new FormData(this);
            console.log('表单数据条目数:', [...formData.entries()].length);
            
            // 手动检查选中的学生
            let selectedCount = 0;
            const studentCheckboxes = this.querySelectorAll('input[type="checkbox"][name^="records["]');
            studentCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedCount++;
                    console.log('选中的学生:', checkbox.name, checkbox.value);
                }
            });
            
            console.log('选中的学生总数:', selectedCount);
            
            if (selectedCount === 0) {
                alert('请至少选择一个学生进行销假');
                e.preventDefault();
                return false;
            }
            
            // 显示确认对话框
            if (!confirm('确定要提交这些销假记录吗？')) {
                e.preventDefault();
                return false;
            }
            
            console.log('表单验证通过，准备提交');
            // 不阻止默认提交，使用传统方式提交表单
        });
        
        // 页面加载完成后初始化所有状态选择框
        document.addEventListener('DOMContentLoaded', function() {
            console.log('页面加载完成，初始化所有状态选择框');
            document.querySelectorAll('.status-select').forEach(select => {
                updateStatusColor(select);
            });
        });
        
        // 添加一个函数来检查表单数据的完整性
        function checkFormData() {
            console.log("=== 检查表单数据完整性 ===");
            
            // 获取所有学生ID输入
            const studentInputs = document.querySelectorAll('input[name="student_ids[]"]');
            console.log("所有学生输入:", studentInputs.length);
            
            // 获取所有状态选择框
            const statusSelects = document.querySelectorAll('select[name="statuses[]"]');
            console.log("所有状态选择框:", statusSelects.length);
            
            // 获取所有备注输入框
            const remarkInputs = document.querySelectorAll('input[name="remarks[]"]');
            console.log("所有备注输入框:", remarkInputs.length);
            
            // 检查每个学生的数据
            studentInputs.forEach((input, index) => {
                const studentId = input.value;
                const statusSelect = statusSelects[index];
                const remarkInput = remarkInputs[index];
                
                console.log("学生 #" + index + ": ID=" + studentId + 
                           ", 状态=" + (statusSelect ? statusSelect.value : 'N/A') + 
                           ", 备注=" + (remarkInput ? remarkInput.value : 'N/A'));
            });
        }
        
        // 每隔5秒检查一次表单数据（用于调试）
        setInterval(checkFormData, 5000);
    </script>
</body>
</html>