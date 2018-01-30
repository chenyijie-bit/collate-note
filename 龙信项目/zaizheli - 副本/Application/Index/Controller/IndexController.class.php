<?php
namespace Index\Controller;
use Think\Controller;
class IndexController extends CommonController {

    public function index(){
        $header = getallheaders();
        //接收MEC手机号
        $aes128_code = $header['x-up-calling-line-id'];
        if(isset($aes128_code)){
            //AES解密
            $AES = new \Index\Common\AES128_cbc_code;
            $oldPhoneNum = $AES->decode($aes128_code);
            $phone_num = substr($oldPhoneNum,2,11) ? substr($oldPhoneNum,2,11) : '0'; //没解出来的情况是0
            //临时表处理
            $latent = M('latent','common_');
            $res = $latent->where('phone_num = '.$phone_num)->find();
            if($res){
                $data = array(
                    'come_time' => date('Y-m-d H:i:s'),
                    'come_num' => $res['come_num'] + 1
                );
                $latent->where('phone_num = '.$phone_num)->save($data);
            }else{
                $data = array(
                    'come_time' => date('Y-m-d H:i:s'),
                    'come_num' => 1,
                    'phone_num' => $phone_num
                );
                $latent->add($data);
            }
            //改MEC状态
            $member = M('member','common_');
            $res = $member->where('phone_num = '.$phone_num)->find();
            if($res){
                $member->where('phone_num = '.$phone_num)->save(array('mec_status' => 1));
                //是否存过AES128码
	            if(!$res['aes128_code']){
	            	$member->where('phone_num = '.$phone_num)->save(array('aes128_code' => $aes128_code));
	            }
            }
        }
        //更新位置信息
        if($header['meculi']){
            //站点ID
            $res = M('site','common_')->where('meculi = '.$header['meculi'])->find();
            if($res){
                $_SESSION['member']['site_id'] = $res['site_id'];
                $_SESSION['member']['site_name'] = $res['name'];
            }else{
                $_SESSION['member']['site_id'] = 0;
                $_SESSION['member']['site_name'] = '未在范围';
            }
        }else{
            $_SESSION['member']['site_id'] = 0;
            $_SESSION['member']['site_name'] = '未在范围';
        }
        //是否登录
        if(!empty($this->member['member_id']))  $this->assign('member',$this->member);
    	//解析模板
    	$this->display();
    }

    public function freedata(){
        $position = M('position','common_')->select();
        //解析模板
        $this->assign('position',$position);
        $this->display();
    }

    //个人资料卡
    public function memberInfo(){
        if(empty($_GET['member_id']) || !isset($_GET['member_id']) || !is_numeric($_GET['member_id'])){
            header('location:/');
        }
        $member = M('member','common_')->find($_GET['member_id']);
        $member['base64_con'] = M('headimg','common_')->where('member_id = '.$member['member_id'])->find()['base64_con'];
    	if(!$member['base64_con']) $member['base64_con'] = '/Public/Common/Member/picture/default.jpg';
        //解析模板
        $this->assign('member',$member);
        $this->assign('thisMemberId',$this->member['member_id']);
    	$this->display();
    }

    public function getCityList(){
        if(IS_GET) header('location:/');
        $res = M('city','common_')->select();
        if($res){
            echo json_encode(array(
                'error' => 0,
                'res' => $res
            ));
        }else{
            echo json_encode(array(
                'error' => 1,
                'msg' => '该区域暂未开通任何站点'
            ));
        }
    }

    public function getSiteList(){
        if(IS_GET) header('location:/');
        $data = I('post.');
        $where = array(
            'position_id' => $data['position_id'],
            'city_id' => $data['city_id']
        );
        $res = M('site','common_')->where($where)->select();
        if($res){
            echo json_encode(array(
                'error' => 0,
                'res' => $res
            ));
        }else{
            echo json_encode(array(
                'error' => 1,
                'msg' => '该区域暂未开通任何站点'
            ));
        }
    }

    public function checkToUse(){
        //非法访问
    	if(IS_GET) header('location:/');
        //未登录
        if(!$this->member){
            echo json_encode(array(
                'error' => 1,
                'msg' => '必须先进行登录，是否去首页登录'
            ));
            return false;
        }
        //检测当前MEC开通状态
        $newStatus = M('member','common_')->field('mec_status,mec_open,aes128_code')->where('member_id = '.$this->member['member_id'])->find();
        $this->member['mec_status'] = $_SESSION['member']['mec_status'] = $newStatus['mec_status'];
        $this->member['mec_open'] = $_SESSION['member']['mec_open'] = $newStatus['mec_open'];
        //如果在MEC覆盖范围
    	if($this->member['mec_status'] == 1){
            //MEC实例化
            $MEC = new \Index\Common\Hw_MECService;
            //如果已开通
            if($this->member['mec_open'] == 1){
                echo json_encode(array(
                    'error' => 0,
                    'msg' => '欢迎继续免流量使用',
                    'logedMemberToken' => base64_encode($this->member['member_id'].'#'.$this->member['password'])
                ));
            }else{
                //开通MEC服务
                $params = array(
                    'member_id' => $this->member['member_id'],
                    'phone_num' => $newStatus['aes128_code']
                );
                $res = $MEC->applyMECServiceRequest($params);
                $openSuccess = json_decode($res,true)['ResultCode'];
                if($openSuccess == '0000000'){
                    //记录登录时间
                    $data = array(
                        'member_id' => $this->member['member_id'],
                        'last_time' => date('Y-m-d H:i:s'),
                        'is_login' => 1,
                        'phone_num' => $this->member['phone_num'],
                        'aes128_code' => $newStatus['aes128_code']
                    );
                    $privateLoged = M('private_loged','video_');
                    $bool = $privateLoged->where('member_id = '.$this->member['member_id'])->find();
                    if(!$bool){
                        $privateLoged->add($data);
                    }else{
                        $privateLoged->where('member_id = '.$this->member['member_id'])->save(array('last_time' => date('Y-m-d H:i:s'),'is_login' => 1));
                    }
                    //更改开通状态
                    M('member','common_')->where('member_id = '.$this->member['member_id'])->save(array('mec_open' => 1));
                        echo json_encode(array(
                            'error' => 0,
                            'msg' => '恭喜！您已是免流量使用',
                            'logedMemberToken' => base64_encode($this->member['member_id'].'#'.$this->member['password'])
                        ));
                }else{
                    echo json_encode(array(
                        'error' => 1,
                        'msg' => '错误！MEC服务处理失败，是否进入首页'
                    ));
                }
            }
    	}else{
    		echo json_encode(array(
    			'error' => 1,
    			'msg' => '抱歉！您不在免流量覆盖范围，是否进入首页'
    		));
    	}
    }

    //关闭华为MEC服务
    public function Close_HwMEC_script(){
        $safe_code = isset($_GET['safe_code']) ? $_GET['safe_code'] : '';
        $key = md5('www.zzlhi.com');
        if($key != $safe_code){
            header('location:http://www.zzlhi.com');
        }else{
            //登录状态表操作
            $private_loged = M('private_loged','video_');
            $time = date('Y-m-d H:i:s',time() - (60*15));
            $close_members = $private_loged->where('last_time < "'.$time.'" and is_login = 1')->select();
            foreach ($close_members as $key => $value) {
                //关闭MEC服务
                $MEC = new \Index\Common\Hw_MECService;
                $params = array(
                    'member_id' => $value['member_id'],
                    'phone_num' => $value['aes128_code']
                );
                $res = $MEC->removeMECServiceRequest($params);
                $closeSuccess = json_decode($res,true)['ResultCode'];
                if($closeSuccess == '0000000'){
                    $private_loged->where(array('member_id' => $value['member_id']))->save(array('is_login' => 0));
                    M('member','common_')->where('member_id = '.$value['member_id'])->save(array('mec_open' => 0,'mec_status' => 0));
                    file_put_contents('D:/wamp/www/WWW/zaizheli/Application/Index/Common/Close_HwMEC_script.log',date('Y-m-d H:i:s').' ['.$value['phone_num'].'#'.$value['member_id'].'] [success]'.PHP_EOL,FILE_APPEND);
                }
            }
        }
    }
}