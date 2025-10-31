<?php
// 用户控制器

require_once 'config/db.php';
require_once 'model/UserModel.php';
require_once 'utils/Auth.php';

class UserController {
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->db = connect_db();
        $this->userModel = new UserModel($this->db);
    }
    
    // 显示登录页面
    public function showLogin() {
        // 如果已经登录，则重定向到相应主页
        if (Auth::check()) {
            $user = Auth::user();
            switch ($user['role']) {
                case ROLE_ADMIN:
                    redirect('index.php?controller=admin&action=dashboard');
                    break;
                case ROLE_ADVISOR:
                    redirect('index.php?controller=checkin&action=dashboard');
                    break;
                case ROLE_STUDENT:
                    redirect('index.php?controller=student&action=dashboard');
                    break;
                default:
                    include 'view/common/login.php';
                    break;
            }
        } else {
            include 'view/common/login.php';
        }
    }
    
    // 处理登录请求
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = post_param('username');
            $password = post_param('password');
            
            if (empty($username) || empty($password)) {
                error_message('用户名和密码不能为空');
                redirect('index.php?controller=user&action=login');
            }
            
            $user = $this->userModel->verifyUser($username, $password);
            
            if ($user) {
                Auth::login($user);
                
                // 根据角色重定向到不同页面
                switch ($user['role']) {
                    case ROLE_ADMIN:
                        redirect('index.php?controller=admin&action=dashboard');
                        break;
                    case ROLE_ADVISOR:
                        redirect('index.php?controller=checkin&action=dashboard');
                        break;
                    case ROLE_STUDENT:
                        redirect('index.php?controller=student&action=dashboard');
                        break;
                    default:
                        redirect('index.php?controller=user&action=login');
                }
            } else {
                error_message('用户名或密码错误');
                redirect('index.php?controller=user&action=login');
            }
        } else {
            $this->showLogin();
        }
    }
    
    // 用户登出
    public function logout() {
        Auth::logout();
        success_message('您已成功退出登录');
        redirect('index.php?controller=user&action=login');
    }
}