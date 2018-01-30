<?php
namespace Chat\Controller;
use Think\Controller;

class TaskController extends CommonController {

	//必须登录
	public function _initialize(){
		parent:: _initialize();
        //用户中心全局登录限制
        if(!$this->member['member_id']){
            header('location:http://www.zzlhi.com/log/action/in');
            die('未登录拦截');
        }
	}

	//待解决的
    public function index(){
    	//前端插件全部惰性加载
        if(IS_GET){
        	$this->assign('site_name',$this->member['site_name']);
	    	$this->display();
        }elseif(IS_POST){
        	$res = M('task_list')->query('select chat_task_list.*,common_member.nick_name from chat_task_list,common_member,common_site where chat_task_list.member_id = common_member.member_id and chat_task_list.status = 0 and common_site.site_id = '.$this->member['site_id']);
        	if(count($res) > 0){
        		echo json_encode($res);
        	}else{
        		echo json_encode('');
        	}
        }
    }

    //我参与的
    public function mypart(){
    	//前端插件全部惰性加载
        if(IS_GET){
	    	$this->display();
        }elseif(IS_POST){
        	$res = M('task_list')->query('select chat_task_list.*,common_member.nick_name from chat_task_list,chat_task_detail,common_member where chat_task_list.member_id = common_member.member_id and chat_task_list.task_id = chat_task_detail.task_id and chat_task_detail.member_id = '.$this->member["member_id"]);
        	if(count($res) > 0){
        		echo json_encode($res);
        	}else{
        		echo json_encode('');
        	}
        }
    }

    //我发布的
    public function myrelease(){
    	//前端插件全部惰性加载
        if(IS_GET){
	    	$this->display();
        }elseif(IS_POST){
        	$res = M('task_list')->query('select chat_task_list.*,common_member.nick_name from chat_task_list,common_member where chat_task_list.member_id = common_member.member_id and chat_task_list.member_id = '.$this->member["member_id"]);
        	if(count($res) > 0){
        		echo json_encode($res);
        	}else{
        		echo json_encode('');
        	}
        }
    }

    //发布任务
    public function release_task(){
    	if(IS_GET){
    		$position = M('position','common_')->select();
	        //解析模板
	        $this->assign('position',$position);
	    	$this->display();
    	}else{
    		$data = I('post.');
    		if(count($data) == 4){
    			$data['member_id'] = $this->member['member_id'];
    			$data['start_time'] = date('Y-m-d H:i:s');
    			$data['end_time'] = date('Y-m-d H:i:s',time() + 60 * 60 * 24 * $data['day']);
    			unset($data['day']);
    			$res = M('task_list')->add($data);
    			if($res){
    				echo json_encode(array('error' => 0,'msg' => '提交成功'));
    			}else{
    				echo json_encode(array('error' => 1,'msg' => '提交失败'));
    			}
    		}else{
    			echo json_encode(array('error' => 1,'msg' => '参数错误'));
    		}
    	}
    }

    //任务详情
    public function receive_task(){
    	if(IS_GET){
    		//接收
    		$task_id = I('get.task_id');
    		if(!$task_id){
    			header('location:/chat/task/index');
    			die;
    		}
    		//查回复
    		$detailRes = M('task_detail')->where('task_id = '.$task_id)->select();
    		//回复者头像&名字
    		foreach ($detailRes as $key => $value) {
    			$res = M('headimg','common_')->find($value['member_id']);
    			$detailRes[$key]['base64_con'] = $res['base64_con'];
    			$res = M('member','common_')->field('nick_name')->find($value['member_id']);
    			$detailRes[$key]['nick_name'] = $res['nick_name'];
    		}
    		//查问题
    		$theListRes = M('task_list')->find($task_id);
    		//提问者头像&名字
    		$res = M('headimg','common_')->find($theListRes['member_id']);
    		$theListRes['base64_con'] = $res['base64_con'];
    		$res = M('member','common_')->field('nick_name')->find($theListRes['member_id']);
    		$theListRes['nick_name'] = $res['nick_name'];
    		//状态
    		$this->assign('theListRes',$theListRes);
    		$this->assign('detailRes',$detailRes);
    		$this->assign('member_id',$this->member['member_id']);
    		if($theListRes['status'] > 0){
    			$this->display('receive_task_close');
    		}else{
    			$this->display('receive_task_open');
    		}
    	}elseif(IS_POST){
    		//获取or发送
    		//....
    	}
    }

}