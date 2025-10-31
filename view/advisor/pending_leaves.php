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
    <title><?php echo SITE_NAME; ?> - 待审核请假</title>
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
                    <a href="index.php?controller=advisor&action=dashboard" class="list-group-item list-group-item-action">首页</a>
                    <a href="index.php?controller=leave&action=pending" class="list-group-item list-group-item-action active">待审核请假</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>待审核请假申请</h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <?php if (empty($leaves)): ?>
                            <p>暂无待审核的请假申请。</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>申请人</th>
                                            <th>请假类型</th>
                                            <th>开始时间</th>
                                            <th>结束时间</th>
                                            <th>请假原因</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leaves as $leave): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($leave['student_name']); ?></td>
                                                <td><?php echo format_leave_type($leave['type']); ?></td>
                                                <td><?php echo format_date($leave['start_date']); ?></td>
                                                <td><?php echo format_date($leave['end_date']); ?></td>
                                                <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                                <td>
                                                    <form method="POST" action="index.php?controller=leave&action=approve" style="display: inline;">
                                                        <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-sm btn-success">批准</button>
                                                    </form>
                                                    <form method="POST" action="index.php?controller=leave&action=approve" style="display: inline;">
                                                        <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-sm btn-danger">拒绝</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        
                        <a href="index.php?controller=advisor&action=dashboard" class="btn btn-secondary">返回首页</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>