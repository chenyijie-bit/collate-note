<?php
namespace Admin\Controller;

use Think\Controller;

class LogController extends Controller {
    
    public function index()
    {
        header('location:/admin/log/in');
    }

    //登录
    public function in()
    {   
        if(IS_POST){
            $username = I('post.username');
            $password = I('post.password');
            $res = M('admin')->where(array('username' => $username,'password' => md5($password)))->find();
            if($res){
                $_SESSION['admin'] = $res;
                echo json_encode(array('error' => 0,'msg' => '登录成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '登录失败，账号或密码错误'));
                exit;
            }
        }
        $this->display();
    }

    //退出
    public function out()
    {
        unset($_SESSION['admin']);
        header('location:/admin/log/in');
    }

}