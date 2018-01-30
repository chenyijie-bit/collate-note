<?php
namespace Hospitaladmin\Controller;
use Think\Controller;
class CommonController extends Controller {

	//分页公用参数
	protected $step = 10;

    public function _initialize(){
    	//唯一登录标识
    	if(!$_SESSION['hospital_admin']){
            $this->error('请先登录','/Hospitaladmin/log/in');
        }
    }
}