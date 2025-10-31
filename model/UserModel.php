<?php
// 用户模型类

class UserModel {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // 根据用户名获取用户信息
    public function getUserByUsername($username) {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // 根据用户ID获取用户信息
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // 创建新用户
    public function createUser($username, $password, $name, $role) {
        // 使用明文密码存储
        $query = "INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $username, $password, $name, $role);
        return $stmt->execute();
    }
    
    // 验证用户登录（使用明文密码对比）
    public function verifyUser($username, $password) {
        $user = $this->getUserByUsername($username);
        if ($user && $password === $user['password']) {
            return $user;
        }
        return false;
    }
    
    // 获取指定角色的所有用户
    public function getUsersByRole($role) {
        $query = "SELECT * FROM users WHERE role = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}