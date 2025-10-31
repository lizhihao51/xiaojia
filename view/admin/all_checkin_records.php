<?php
require_once 'config/config.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

if (!Auth::isAdmin()) {
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
    <title><?php echo SITE_NAME; ?> - 所有销假记录</title>
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
                    <a href="index.php?controller=admin&action=all_checkin_records" class="list-group-item list-group-item-action active">查看所有销假记录</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>所有销假记录</h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <?php if (empty($records)): ?>
                            <p>暂无销假记录。</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-fixed-header">
                                    <thead class="table-light">
                                        <tr>
                                            <th>任务标题</th>
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
                                                <td><?php echo htmlspecialchars($record['task_title']); ?></td>
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
                        
                        <a href="index.php?controller=admin&action=dashboard" class="btn btn-secondary">返回首页</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>