<?php
namespace Videoadmin\Controller;
use Think\Controller;

class LogController extends Controller {

    public function in(){
        if(IS_GET){
            $this->display();
        }else{
            $data['username'] = $_POST['username'];
            $data['password'] = md5($_POST['password']);
            $res = M('admin')->where($data)->find();
            if($res){
                $_SESSION['video_admin'] = $res;
                $this->redirect('/Videoadmin/Index/index');
            }else{
                $this->error('账号或密码错误');
            }
        }
    }

    public function out(){
        unset($_SESSION['video_admin']);
        $this->success('已退出','/Videoadmin/log/in');
    }
}