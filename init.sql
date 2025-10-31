-- 创建数据库
CREATE DATABASE IF NOT EXISTS leave_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 使用数据库
USE leave_system;

-- 创建入学年表
CREATE TABLE IF NOT EXISTS enrollment_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 创建班级表
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    enrollment_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_year_id) REFERENCES enrollment_years(id) ON DELETE CASCADE
);

-- 创建学生表
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    remark TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- 创建用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role TINYINT NOT NULL COMMENT '1-学生, 2-辅导员, 3-管理员',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 创建销假任务表
CREATE TABLE IF NOT EXISTS checkin_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    creator_id INT NOT NULL,
    target_classes JSON NOT NULL COMMENT '目标班级ID列表',
    assigned_advisors JSON NOT NULL COMMENT '分配的辅导员ID列表',
    status TINYINT DEFAULT 1 COMMENT '1-进行中, 2-已完成',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 创建销假记录表
CREATE TABLE IF NOT EXISTS checkin_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    student_id INT NOT NULL,
    advisor_id INT NOT NULL COMMENT '执行销假的辅导员ID',
    status TINYINT NOT NULL COMMENT '1-到课, 2-请假, 3-分团委, 4-缺勤, 5-其他',
    remark TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES checkin_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_task_student (task_id, student_id)
);

-- 插入默认入学年
INSERT IGNORE INTO enrollment_years (year) VALUES 
(2021), (2022), (2023), (2024);

-- 插入默认班级
INSERT IGNORE INTO classes (name, enrollment_year_id) VALUES 
('计算机科学与技术1班', 1),
('计算机科学与技术2班', 1),
('软件工程1班', 2),
('软件工程2班', 2);

-- 插入默认学生
INSERT IGNORE INTO students (name, class_id, remark) VALUES 
('张三', 1, '优秀学生'),
('李四', 1, ''),
('王五', 2, '需要关注'),
('赵六', 2, '');

-- 插入默认管理员用户 (使用明文密码)
INSERT IGNORE INTO users (username, password, name, role) VALUES 
('admin', 'admin123', '管理员', 3),
('advisor1', 'advisor123', '辅导员1', 2),
('advisor2', 'advisor123', '辅导员2', 2);