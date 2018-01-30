<?php
namespace Business\Controller;

use Think\Controller;

class CommonController extends Controller {

    //涉及到共享币变动的项目：充值成功、提现成功、发放共享币、推荐奖励、活动支出、返利所得

    //分页步进数
    protected $step = 10;

    //优先执行
    protected function _initialize(){
        //登录限制
        if(!$_SESSION['business']){
            header('location:/business/log/in');
            exit;
        }
        //未激活用户的全局操作限制
        $action_name = array('activate'); //激活 除外
        if(IS_POST && !in_array(ACTION_NAME,$action_name)){
            if(M('business')->where('business_id = '.$this->business_id())->getField('status') == 0){
                echo json_encode(array('error' => 1,'msg' => '提示：您是未激活用户，无法进行此操作'));
                exit;
            }
        }
    }
    
    //获取商家id
    protected function business_id()
    {
        return $_SESSION['business']['business_id'];
    }

    //安全拦截时获取商家id，仅限查子表的时候用，否则报错
    protected function safe_business_id()
    {
    	$result = M('business')->find($_SESSION['business']['business_id']);
        if($result['status'] == 1){
            return $_SESSION['business']['business_id'];
        }else{
            return 10000;
        }
    }

}