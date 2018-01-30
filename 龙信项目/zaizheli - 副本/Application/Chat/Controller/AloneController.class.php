<?php
namespace Chat\Controller;
use Think\Controller;

class AloneController extends CommonController {

	public function _initialize(){
        parent:: _initialize();
        //用户中心全局登录限制
        if(!$this->member['member_id']){
            header('location:http://www.zzlhi.com/log/action/in?refererUrl=http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            die('未登录拦截');
        }
    }

    //最近联系人列表
    public function index(){
        if($_GET['into'] == 'index') $_SESSION['SITE_URL'] = null;
        //内网进入本站显示bar
        if(isset($_GET['site_url'])) $_SESSION['SITE_URL'] = $_GET['site_url'];

      	$result = M()->table('chat_recent cr,common_member cm,common_headimg ch')->where('cr.self_member_id = '.$this->member['member_id'].' and cm.member_id = cr.recent_member_id and ch.member_id = cm.member_id')->field('cm.member_id,cm.nick_name,cr.last_content,cr.last_send_time,ch.base64_con headimg')->select();
		//var_dump($result);
		$this->assign('recent',$result);
    	$this->display('recent_contacts');
    }

    //单独聊天页面
    public function chat_content(){
    	$recent_member_id = $_GET['member_id'];
    	if (empty($recent_member_id)) {
    		echo '<script>alert("数据错误");window.history(-1);</script>';
    	}
    	//登录用户的信息
    	$self_info = array(
    			'member_id' => $this->member['member_id'],
    			'nick_name' => $this->member['nick_name'],
    			'member_head' => $this->member['base64_con']
    		);
    	//对方的信息
    	$other_info = M()->table('common_member cm,common_headimg ch')->where('cm.member_id = '.$recent_member_id.' and ch.member_id = cm.member_id')->field('cm.member_id,cm.nick_name,ch.base64_con member_head')->find();
    	//查询双方的聊天信息
    	$contents = M('alone')->where('(send_member_id = '.$self_info['member_id'].' and sendee_member_id = '.$recent_member_id.') or (send_member_id = '.$recent_member_id.' and sendee_member_id = '.$self_info['member_id'].')')->order('send_time asc')->select();
    	$this->assign('member_id',$self_info['member_id']);
    	$this->assign('self_info',$self_info);
    	$this->assign('other_info',$other_info);
    	$this->assign('contents',$contents);
    	$this->display();
    }

    //发送聊天消息
    public function add_content(){
    	$sendee_member_id = $_POST['member_id'];
    	if(empty($sendee_member_id)){
    		$ajax = array('err_code' => 1,'msg' => '没有接收人的信息！');
    		$this->ajaxReturn($ajax);
    	}

    	$old_content = $_POST['content'];
    	if(empty($old_content)){
    		$ajax = array('err_code' => 1,'msg' => '内容不能为空');
    		$this->ajaxReturn($ajax);
    	}
    	
		$content = array(
				'member_id' => $this->member['member_id'],
				'content' => htmlentities($old_content), 
				'send_time' => date('Y-m-d H:i:s'),
				'member_head' => $this->member['base64_con'],
				'nick_name' => $this->member['nick_name']
			);
		$add_data = array(
				'send_member_id' => $content['member_id'],
				'sendee_member_id' => $sendee_member_id,
				'content' => $content['content'],
				'send_time' => $content['send_time']
			);
		$add_result = M('alone')->add($add_data);
		if ($add_result) {
			//更新接收方的未读消息数
			M('recent')->where('self_member_id = '.$sendee_member_id.' and recent_member_id = '.$content['member_id'])->setInc('unread_num');
			//更新双方的最后一条信息
			M('recent')->where('(self_member_id = '.$content['member_id'].' and recent_member_id = '.$sendee_member_id.') or (self_member_id = '.$sendee_member_id.' and recent_member_id = '.$content['member_id'].')')->save(array('last_content'=>$content['content'],'last_send_time'=>$content['send_time']));

			$ajax = array('err_code' => 0,'data' => $content);
    		$this->ajaxReturn($ajax);
		}else{
			$ajax = array('err_code' => 1,'data' => '发送失败，请重发！');
    		$this->ajaxReturn($ajax);
		}

		
    }

    //获取聊天消息
    public function get_content(){
    	$last_time = $_POST['time'];
    	$recent_member_id = $_POST['other_id'];
    	if (empty($recent_member_id)) {
    		$ajax = array('err_code' => 0,'data' => '获取消息错误！');
    		$this->ajaxReturn($ajax);
    	}
    	//对方的信息
    	$other_info = M()->table('common_member cm,common_headimg ch')->where('cm.member_id = '.$recent_member_id.' and ch.member_id = cm.member_id')->field('cm.member_id,cm.nick_name,ch.base64_con member_head')->find();
    	
    	if (empty($other_info)) {
    		$ajax = array('err_code' => 0,'data' => '获取消息错误！');
    		$this->ajaxReturn($ajax);
    	}


    	$where = ' and send_time > "'.$last_time.'"';
    	if (empty($last_time)) {
    		$where = '';
    	}

    	$unread_content = M('alone')->where('send_member_id = '.$recent_member_id.' and sendee_member_id = '.$this->member['member_id'].$where)->order('send_time asc')->select();
    	
		$new_content = array();
		foreach ($unread_content as $key => $value) {
			$new_content[] = array(
					'member_id' => $other_info['member_id'],
					'content' => $value['content'],
					'send_time' => $value['send_time'],
					'member_head' => $other_info['member_head'],
					'nick_name' => $other_info['nick_name']
				);
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