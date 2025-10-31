<?php
require_once 'config/config.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

if (!Auth::isAdvisor()) {
    redirect('index.php?controller=user&action=login');
}

$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - 辅导员首页</title>
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
                    <a href="index.php?controller=checkin&action=dashboard" class="list-group-item list-group-item-action active">首页</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>辅导员首页</h4>
                    </div>
                    <div class="card-body">
                        <h5>欢迎使用销假管理系统</h5>
                        <p>作为辅导员，您可以处理分配给您的销假任务。</p>
                        
                        <?php if (empty($tasks)): ?>
                            <div class="alert alert-info">暂无分配的销假任务。</div>
                        <?php else: ?>
                            <h5 class="mt-4">销假任务列表</h5>
                            <div class="row">
                                <?php foreach ($tasks as $task): ?>
                                    <div class="col-md-12 mb-3">
                                        <div class="card task-card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h5>
                                                    <div>
                                                        <?php if ($task['status'] == 1): ?>
                                                            <span class="badge bg-primary">进行中</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">已完成</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <p class="card-text"><?php echo htmlspecialchars($task['description'] ?? '无'); ?></p>
                                                <p class="text-muted">创建时间: <?php echo format_date($task['created_at']); ?></p>
                                                <a href="index.php?controller=checkin&action=task_detail&id=<?php echo $task['id']; ?>" class="btn btn-primary">处理销假</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>