<?php
// 数据库配置文件

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '15829931165');
define('DB_NAME', 'leave_system');

// 创建数据库连接
function connect_db() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // 检查连接
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    
    // 设置字符集
    $conn->set_charset("utf8");
    
    return $conn;
}