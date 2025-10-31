<?php
// 系统配置文件

// 系统基础配置
define('SITE_NAME', '销假管理系统');
define('SITE_URL', 'http://localhost/leave_system');

// 用户角色定义
define('ROLE_STUDENT', 1);
define('ROLE_ADVISOR', 2);
define('ROLE_ADMIN', 3);

// 请假状态定义
define('LEAVE_PENDING', 0);      // 待审核
define('LEAVE_APPROVED', 1);     // 已批准
define('LEAVE_REJECTED', 2);     // 已拒绝
define('LEAVE_CHECKED_IN', 3);   // 已销假

// 分页配置
define('PAGE_SIZE', 10);

// 会话配置
define('SESSION_TIMEOUT', 3600); // 1小时

?>