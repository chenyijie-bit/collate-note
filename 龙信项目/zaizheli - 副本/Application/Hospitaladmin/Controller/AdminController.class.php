<?php
namespace Hospitaladmin\Controller;
use Think\Controller;

class AdminController extends CommonController {

    public function repassword(){
        if(IS_GET){
            $this->display();
        }else{
            $data = I('post.');
            if($data['password'] != $data['repassword']){
            	$this->error('两次密码必须相同');
            }
            //save更新数据 
            $success = M('list')->where('hospital_id ='.$_SESSION['hospital_admin']['hospital_id'])->save(array('password' => md5($data['password'])));
            if($success){ 
                $this->success('修改成功');
            }else{ 
                $this->error('修改失败');
            }
        }
    }
}