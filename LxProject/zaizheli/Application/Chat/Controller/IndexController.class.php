<?php
namespace Chat\Controller;
use Think\Controller;

class IndexController extends CommonController {

    public function index(){
        if($_GET['into'] == 'index') $_SESSION['SITE_URL'] = null;
        //内网进入本站显示bar
        if(isset($_GET['site_url'])) $_SESSION['SITE_URL'] = $_GET['site_url'];

      	$redis = new \Redis;
	 	$result = $redis->connect('127.0.0.1', '6379'); 
 
		$all_content = $redis->lgetrange('test',0,-1);
		//var_dump($all_content);
		//$redis->delete('test');
		$this->assign('member_id',$this->member['member_id']);
		$this->assign('content',$all_content);
    	$this->display();
    }

    public function add_content(){
    	if(empty($this->member['member_id'])){
    		$ajax = array('err_code' => 2,'msg' => '需要登录后才可以聊天');
    		$this->ajaxReturn($ajax);
    	}
    	$old_content = $_POST['content'];
    	if(empty($old_content)){
    		$ajax = array('err_code' => 1,'msg' => '内容不能为空');
    		$this->ajaxReturn($ajax);
    	}
    	$redis = new \Redis;
	 	$result = $redis->connect('127.0.0.1', '6379'); 
		$content = array(
				'member_id' => $this->member['member_id'],
				'content' => htmlentities($old_content), 
				'send_time' => date('Y-m-d H:i:s'),
				'member_head' => $this->member['base64_con'],
				'nick_name' => $this->member['nick_name']
			);
		$len = $redis->rpush("test",json_encode($content)); 
		//$redis->delete('test'); 
		if ($len > 15) {
		 	$redis->lpop('test');
		} 
		$ajax = array('err_code' => 0,'data' => $content);
    	$this->ajaxReturn($ajax);
    }

    public function get_content(){
    	$last_time = $_POST['time'];
    	$last_id = $_POST['other_id'];
    	if (empty($last_time)) {
    		$last_time = date('Y-m-d H:i');
    		$last_id = 0;
    	}
    	$redis = new \Redis;
	 	$result = $redis->connect('127.0.0.1', '6379'); 
 
		$all_content = $redis->lgetrange('test',0,-1);
		$new_content = array();
		foreach ($all_content as $key => $value) {

			if (json_decode($value,1)['send_time'] >= $last_time && json_decode($value,1)['member_id'] != $this->member['member_id']) {
				if (!(json_decode($value,1)['send_time'] == $last_time && json_decode($value,1)['member_id'] == $last_id)) {
					$new_content[] = json_decode($value);
				}
			}
		}
		if (count($new_content) > 0) {
			$ajax = array('err_code' => 0,'data' => $new_content);
    		$this->ajaxReturn($ajax);
		}else{
			$ajax = array('err_code' => 1,'data' => $new_content);
    		$this->ajaxReturn($ajax);
		}
		
    }

}