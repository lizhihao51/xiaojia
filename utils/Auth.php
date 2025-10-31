<?php
// 认证工具类

class Auth {
    // 检查是否已登录
    public static function check() {
        session_start();
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
    
    // 获取当前登录用户信息
    public static function user() {
        if (self::check()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'name' => $_SESSION['name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    
    // 用户登录
    public static function login($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        return true;
    }
    
    // 用户登出
    public static function logout() {
        session_start();
        session_destroy();
        return true;
    }
    
    // 检查用户角色
    public static function checkRole($role) {
        $user = self::user();
        if ($user) {
            return $user['role'] == $role;
        }
        return false;
    }
    
    // 检查是否为管理员
    public static function isAdmin() {
        return self::checkRole(ROLE_ADMIN);
    }
    
    // 检查是否为辅导员
    public static function isAdvisor() {
        return self::checkRole(ROLE_ADVISOR);
    }
    
    // 检查是否为学生
    public static function isStudent() {
        return self::checkRole(ROLE_STUDENT);
    }
}