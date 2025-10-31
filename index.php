<?php
// 系统入口文件

// 引入配置文件
require_once 'config/config.php';
require_once 'utils/Functions.php';
require_once 'utils/Auth.php';

// 启动会话
session_start();

// 获取控制器和操作参数
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'user';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

// 根据控制器参数加载对应控制器
switch ($controller) {
    case 'user':
        require_once 'controller/UserController.php';
        $userController = new UserController();
        
        switch ($action) {
            case 'login':
                $userController->login();
                break;
            case 'logout':
                $userController->logout();
                break;
            default:
                $userController->showLogin();
        }
        break;
        
    case 'admin':
        if (!Auth::check() || !Auth::isAdmin()) {
            redirect('index.php?controller=user&action=login');
        }
        
        require_once 'controller/AdminController.php';
        $adminController = new AdminController();
        
        switch ($action) {
            case 'dashboard':
                $adminController->dashboard();
                break;
            case 'create_task':
                $adminController->createTask();
                break;
            case 'do_create_task':
                $adminController->doCreateTask();
                break;
            case 'tasks':
                $adminController->tasks();
                break;
            case 'task_detail':
                $adminController->taskDetail();
                break;
            case 'all_checkin_records':
                $adminController->allCheckinRecords();
                break;
            case 'end_task':
                $adminController->endTask();
                break;
            case 'export_task':
                $adminController->exportTask();
                break;
            default:
                redirect('index.php?controller=admin&action=dashboard');
        }
        break;
        
    case 'checkin':
        if (!Auth::check() || !Auth::isAdvisor()) {
            redirect('index.php?controller=user&action=login');
        }
        
        require_once 'controller/CheckinController.php';
        $checkinController = new CheckinController();
        
        switch ($action) {
            case 'dashboard':
                $checkinController->dashboard();
                break;
            case 'task_detail':
                $checkinController->taskDetail();
                break;
            case 'do_checkin':
                $checkinController->doCheckin();
                break;
            case 'export_task':
                $checkinController->exportTask();
                break;
            default:
                redirect('index.php?controller=checkin&action=dashboard');
        }
        break;
        
    case 'student':
        if (!Auth::check() || !Auth::isStudent()) {
            redirect('index.php?controller=user&action=login');
        }
        
        // 学生功能暂时保留
        switch ($action) {
            case 'dashboard':
                include 'view/student/dashboard.php';
                break;
            default:
                redirect('index.php?controller=student&action=dashboard');
        }
        break;
        
    default:
        redirect('index.php?controller=user&action=login');
}