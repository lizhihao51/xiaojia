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
    <title><?php echo SITE_NAME; ?> - 销假任务管理</title>
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
                    <a href="index.php?controller=admin&action=tasks" class="list-group-item list-group-item-action active">销假任务管理</a>
                    <a href="index.php?controller=admin&action=all_checkin_records" class="list-group-item list-group-item-action">查看所有销假记录</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>销假任务管理</h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <?php if (empty($tasks)): ?>
                            <p>暂无销假任务。</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>任务标题</th>
                                            <th>创建人</th>
                                            <th>创建时间</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tasks as $task): ?>
                                            <tr>
                                                <td><?php echo $task['id']; ?></td>
                                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                                <td><?php echo htmlspecialchars($task['creator_name']); ?></td>
                                                <td><?php echo format_date($task['created_at']); ?></td>
                                                <td>
                                                    <?php if ($task['status'] == 1): ?>
                                                        <span class="badge bg-primary">进行中</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">已完成</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?controller=admin&action=task_detail&id=<?php echo $task['id']; ?>" class="btn btn-sm btn-primary">查看详情</a>
                                                    
                                                    <?php if ($task['status'] == 1): ?>
                                                        <form method="POST" action="index.php?controller=admin&action=end_task" style="display: inline;">
                                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning" 
                                                                    onclick="return confirm('确定要结束此销假任务吗？结束后将无法再进行销假操作。')">结束任务</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
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