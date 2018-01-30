<?php
namespace Hospitaladmin\Controller;
use Think\Controller;

class LogController extends Controller {

    public function in(){
        if(IS_GET){
            $this->display();
        }else{
            $data['admin'] = $_POST['admin'];
            $data['password'] = md5($_POST['password']);
            $res = M('list')->where($data)->find();
            if($res){
                $_SESSION['hospital_admin'] = $res;
                $this->redirect('/Hospitaladmin/Index/index');
            }else{
                $this->error('账号或密码错误');
            }
        }
    }

    public function out(){
        unset($_SESSION['hospital_admin']);
        $this->success('已退出','/Hospitaladmin/log/in');
    }
}