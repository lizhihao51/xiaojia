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
    <title><?php echo SITE_NAME; ?> - 创建销假任务</title>
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
                    <a href="index.php?controller=admin&action=create_task" class="list-group-item list-group-item-action active">创建销假任务</a>
                    <a href="index.php?controller=admin&action=tasks" class="list-group-item list-group-item-action">销假任务管理</a>
                    <a href="index.php?controller=admin&action=all_checkin_records" class="list-group-item list-group-item-action">查看所有销假记录</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>创建销假任务</h4>
                    </div>
                    <div class="card-body">
                        <?php show_error_message(); ?>
                        <?php show_success_message(); ?>
                        
                        <form method="POST" action="index.php?controller=admin&action=do_create_task">
                            <div class="mb-4">
                                <label for="title" class="form-label">任务标题</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="description" class="form-label">任务描述</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">分配辅导员</label>
                                <div class="row">
                                    <?php foreach ($advisors as $advisor): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="assigned_advisors[]" value="<?php echo $advisor['id']; ?>" id="advisor_<?php echo $advisor['id']; ?>">
                                                <label class="form-check-label" for="advisor_<?php echo $advisor['id']; ?>">
                                                    <?php echo htmlspecialchars($advisor['name']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">目标选择方式</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="target_type_class" value="class" checked>
                                        <label class="form-check-label" for="target_type_class">按班级选择</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="target_type_year" value="year">
                                        <label class="form-check-label" for="target_type_year">按入学年选择</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4" id="class_selection">
                                <label class="form-label">选择班级</label>
                                <div class="row">
                                    <?php foreach ($classes as $class): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_classes[]" value="<?php echo $class['id']; ?>" id="class_<?php echo $class['id']; ?>">
                                                <label class="form-check-label" for="class_<?php echo $class['id']; ?>">
                                                    <?php echo htmlspecialchars($class['name']); ?> (<?php echo $class['enrollment_year']; ?>年入学)
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4 d-none" id="year_selection">
                                <label for="target_year" class="form-label">选择入学年</label>
                                <select class="form-select" id="target_year" name="target_year">
                                    <option value="">请选择入学年</option>
                                    <?php foreach ($enrollment_years as $year): ?>
                                        <option value="<?php echo $year['id']; ?>"><?php echo $year['year']; ?>年入学</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">创建任务</button>
                            <a href="index.php?controller=admin&action=dashboard" class="btn btn-secondary">返回首页</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 切换选择方式
        document.getElementById('target_type_class').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('class_selection').classList.remove('d-none');
                document.getElementById('year_selection').classList.add('d-none');
            }
        });
        
        document.getElementById('target_type_year').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('class_selection').classList.add('d-none');
                document.getElementById('year_selection').classList.remove('d-none');
            }
        });
    </script>
</body>
</html>