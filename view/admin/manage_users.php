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
    <title><?php echo SITE_NAME; ?> - 用户管理</title>
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
                    <a href="index.php?controller=admin&action=dashboard" class="list-group-item list-group-item-action">首页</a>
                    <a href="index.php?controller=admin&action=manage_users" class="list-group-item list-group-item-action active">用户管理</a>
                    <a href="index.php?controller=checkin&action=all" class="list-group-item list-group-item-action">所有销假记录</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>用户管理</h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <!-- 创建用户表单 -->
                        <div class="mb-4">
                            <h5>创建新用户</h5>
                            <form method="POST" action="index.php?controller=admin&action=create_user">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">用户名</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">密码</label>
                                            <input type="text" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">姓名</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">角色</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="">请选择角色</option>
                                                <option value="1">学生</option>
                                                <option value="2">辅导员</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">创建用户</button>
                            </form>
                        </div>
                        
                        <hr>
                        
                        <!-- 学生列表 -->
                        <h5>学生列表</h5>
                        <?php if (empty($students)): ?>
                            <p>暂无学生用户。</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>用户名</th>
                                            <th>姓名</th>
                                            <th>创建时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo $student['id']; ?></td>
                                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><?php echo format_date($student['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 辅导员列表 -->
                        <h5>辅导员列表</h5>
                        <?php if (empty($advisors)): ?>
                            <p>暂无辅导员用户。</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>用户名</th>
                                            <th>姓名</th>
                                            <th>创建时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($advisors as $advisor): ?>
                                            <tr>
                                                <td><?php echo $advisor['id']; ?></td>
                                                <td><?php echo htmlspecialchars($advisor['username']); ?></td>
                                                <td><?php echo htmlspecialchars($advisor['name']); ?></td>
                                                <td><?php echo format_date($advisor['created_at']); ?></td>
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