<?php
require_once 'config/config.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

if (!Auth::isAdmin()) {
    redirect('index.php?controller=user&action=login');
}

$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - 管理员首页</title>
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
                    <a href="index.php?controller=admin&action=dashboard" class="list-group-item list-group-item-action active">首页</a>
                    <a href="index.php?controller=admin&action=create_task" class="list-group-item list-group-item-action">创建销假任务</a>
                    <a href="index.php?controller=admin&action=tasks" class="list-group-item list-group-item-action">销假任务管理</a>
                    <a href="index.php?controller=admin&action=all_checkin_records" class="list-group-item list-group-item-action">查看所有销假记录</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>管理员首页</h4>
                    </div>
                    <div class="card-body">
                        <h5>欢迎使用销假管理系统</h5>
                        <p>作为管理员，您可以：</p>
                        <ul>
                            <li>创建销假任务</li>
                            <li>管理销假任务</li>
                            <li>查看销假统计信息</li>
                            <li>查看所有销假记录</li>
                        </ul>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <h5 class="card-title">快速操作</h5>
                                        <a href="index.php?controller=admin&action=create_task" class="btn btn-primary">创建销假任务</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo count($tasks); ?></h5>
                                        <p class="card-text">当前销假任务数</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>