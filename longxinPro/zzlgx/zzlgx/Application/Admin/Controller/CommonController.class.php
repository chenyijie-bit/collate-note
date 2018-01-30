<?php
namespace Admin\Controller;

use Think\Controller;

class CommonController extends Controller {

    //分页步进数
    protected $step = 10;

    //优先执行
    protected function _initialize(){
        //登录限制
        if(!$_SESSION['admin']){
            header('location:/admin/log/in');
            exit;
        }
    }
    
    //获取管理员id
    protected function admin_id()
    {
        return $_SESSION['admin']['admin_id'];
    }
}