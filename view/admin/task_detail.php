<?php
require_once 'config/config.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

if (!Auth::isAdmin()) {
    redirect('index.php?controller=user&action=login');
}

$user = Auth::user();

// 解析目标班级
$target_classes = json_decode($task['target_classes'], true);
$class_names = [];
foreach ($target_classes as $class_id) {
    foreach ($classes as $class) {
        if ($class['id'] == $class_id) {
            $class_names[] = $class['name'];
            break;
        }
    }
}

// 解析分配的辅导员
$assigned_advisors = json_decode($task['assigned_advisors'], true);
$advisor_names = [];
if (!empty($assigned_advisors)) {
    $advisor_map = [];
    foreach ($advisors as $advisor) {
        $advisor_map[$advisor['id']] = $advisor['name'];
    }
    
    foreach ($assigned_advisors as $advisor_id) {
        if (isset($advisor_map[$advisor_id])) {
            $advisor_names[] = $advisor_map[$advisor_id];
        }
    }
}

// 读取状态配置
$status_config = json_decode(file_get_contents('config/status.json'), true);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - 任务详情</title>
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
                    <a href="index.php?controller=admin&action=dashboard" class="list-group-item list-group-item-action">首页</a>
                    <a href="index.php?controller=admin&action=create_task" class="list-group-item list-group-item-action">创建销假任务</a>
                    <a href="index.php?controller=admin&action=tasks" class="list-group-item list-group-item-action">销假任务管理</a>
                    <a href="index.php?controller=admin&action=all_checkin_records" class="list-group-item list-group-item-action">查看所有销假记录</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>任务详情</h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <div class="mb-4">
                            <h5><?php echo htmlspecialchars($task['title']); ?></h5>
                            <p><?php echo htmlspecialchars($task['description'] ?? '无'); ?></p>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>创建人:</strong> <?php echo htmlspecialchars($task['creator_name']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>创建时间:</strong> <?php echo format_date($task['created_at']); ?>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <strong>目标班级:</strong> <?php echo implode(', ', array_map('htmlspecialchars', $class_names)); ?>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <strong>分配辅导员:</strong> 
                                    <?php echo empty($advisor_names) ? '未分配' : implode(', ', array_map('htmlspecialchars', $advisor_names)); ?>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <strong>任务状态:</strong> 
                                    <?php if ($task['status'] == 1): ?>
                                        <span class="badge bg-primary">进行中</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">已完成</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>导出数据</h5>
                            <a href="index.php?controller=admin&action=export_task&id=<?php echo $task['id']; ?>&type=all" class="btn btn-success">导出全部数据</a>
                            
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-info dropdown-toggle" type="button" id="exportClassDropdown" data-bs-toggle="dropdown">
                                    导出班级数据
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportClassDropdown">
                                    <?php foreach ($target_classes as $class_id): ?>
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
                                               href="index.php?controller=admin&action=export_task&id=<?php echo $task['id']; ?>&type=class&class_id=<?php echo $class_id; ?>">
                                                <?php echo htmlspecialchars($class_name); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h5>班级统计</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-fixed-header">
                                <thead class="table-light">
                                    <tr>
                                        <th>班级名称</th>
                                        <th>总人数</th>
                                        <th>到课</th>
                                        <th>请假</th>
                                        <th>其他</th>
                                        <th>未销假</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($class_statistics as $stat): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($stat['class_name']); ?></td>
                                            <td><?php echo $stat['total_students']; ?></td>
                                            <td class="status-arrived"><?php echo $stat['arrived_count']; ?></td>
                                            <td class="status-leave"><?php echo $stat['leave_count']; ?></td>
                                            <td class="status-other"><?php echo $stat['other_count']; ?></td>
                                            <td><?php echo $stat['total_students'] - $stat['arrived_count'] - $stat['leave_count'] - $stat['other_count']; ?></td>
                                            <td>
                                                <a href="index.php?controller=admin&action=export_task&id=<?php echo $task['id']; ?>&type=class&class_id=<?php echo $stat['class_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">导出</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <hr>
                        
                        <h5>详细销假记录</h5>
                        <?php if (empty($records)): ?>
                            <p>暂无销假记录。</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-fixed-header">
                                    <thead class="table-light">
                                        <tr>
                                            <th>学生姓名</th>
                                            <th>班级</th>
                                            <th>状态</th>
                                            <th>备注</th>
                                            <th>销假辅导员</th>
                                            <th>销假时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    $status_text = isset($status_config['status'][$record['status']]) ? 
                                                        $status_config['status'][$record['status']] : '未知';
                                                    $status_class = isset($status_config['status_colors'][$record['status']]) ? 
                                                        'bg-' . $status_config['status_colors'][$record['status']] : 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['remark'] ?? '无'); ?></td>
                                                <td><?php echo htmlspecialchars($record['advisor_name']); ?></td>
                                                <td><?php echo format_date($record['updated_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        
                        <a href="index.php?controller=admin&action=tasks" class="btn btn-secondary">返回任务列表</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>