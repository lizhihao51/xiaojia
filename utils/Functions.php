<?php
// 通用函数工具类

// 重定向函数
function redirect($url) {
    header("Location: $url");
    exit();
}

// 输出成功消息
function success_message($message, $redirect_url = null) {
    $_SESSION['success_message'] = $message;
    if ($redirect_url) {
        redirect($redirect_url);
    }
}

// 输出错误消息
function error_message($message, $redirect_url = null) {
    $_SESSION['error_message'] = $message;
    if ($redirect_url) {
        redirect($redirect_url);
    }
}

// 显示成功消息
function show_success_message() {
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
}

// 显示错误消息
function show_error_message() {
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
}

// 获取GET参数
function get_param($name, $default = null) {
    return isset($_GET[$name]) ? $_GET[$name] : $default;
}

// 获取POST参数
function post_param($name, $default = null) {
    return isset($_POST[$name]) ? $_POST[$name] : $default;
}

// 格式化日期
function format_date($date) {
    return date('Y-m-d H:i:s', strtotime($date));
}

// 格式化状态显示
function format_leave_status($status) {
    switch ($status) {
        case LEAVE_PENDING:
            return '<span class="badge badge-warning">待审核</span>';
        case LEAVE_APPROVED:
            return '<span class="badge badge-success">已批准</span>';
        case LEAVE_REJECTED:
            return '<span class="badge badge-danger">已拒绝</span>';
        case LEAVE_CHECKED_IN:
            return '<span class="badge badge-info">已销假</span>';
        default:
            return '<span class="badge badge-secondary">未知</span>';
    }
}

// 格式化请假类型
function format_leave_type($type) {
    switch ($type) {
        case 1:
            return '事假';
        case 2:
            return '病假';
        case 3:
            return '公假';
        case 4:
            return '其他';
        default:
            return '未知';
    }
}