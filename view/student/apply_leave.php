<?php
require_once 'config/config.php';
require_once 'utils/Auth.php';
require_once 'utils/Functions.php';

if (!Auth::isStudent()) {
    redirect('index.php?controller=user&action=login');
}

$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - 申请请假</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a href="index.php?controller=student&action=dashboard" class="list-group-item list-group-item-action">首页</a>
                    <a href="index.php?controller=leave&action=apply" class="list-group-item list-group-item-action active">申请请假</a>
                    <a href="index.php?controller=student&action=leaves" class="list-group-item list-group-item-action">我的请假</a>
                    <a href="index.php?controller=student&action=checkins" class="list-group-item list-group-item-action">我的销假记录</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>申请请假</h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <form method="POST" action="index.php?controller=leave&action=apply">
                            <div class="mb-3">
                                <label for="type" class="form-label">请假类型</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">请选择请假类型</option>
                                    <option value="1">事假</option>
                                    <option value="2">病假</option>
                                    <option value="3">公假</option>
                                    <option value="4">其他</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="start_date" class="form-label">开始时间</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">结束时间</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="reason" class="form-label">请假原因</label>
                                <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">提交申请</button>
                            <a href="index.php?controller=student&action=dashboard" class="btn btn-secondary">返回首页</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>