<?php
namespace Log\Controller;
use Think\Controller;

class ActionController extends Controller {

    public function _initialize(){
        //分站跳来的地址
        $refererUrl = isset($_GET['refererUrl']) ? $_GET['refererUrl'] : '/';
        $this->assign('refererUrl',$refererUrl);
        //全局网站配置
        $this->assign('webConfig',M('config','common_')->find(1));
    }

    public function reg(){
    	$this->display();
    }

    public function in(){
    	if(IS_GET){
            $this->display();
        }else{
            parse_str($_POST['data'],$data);
            if(empty($data['password']) || empty($data['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '请输入账号密码'));
                return false;
            }
            $member = M('member','common_');
            $theMember = $member->where(array('phone_num' => $data['phone_num'],'password' => md5($data['password'])))->find();
            if($theMember){
                $_SESSION['member'] = $theMember;
                //返回用户token加密
                $token = base64_encode($theMember['member_id'].'#'.$theMember['password']);
                echo json_encode(array('error' => 0,'token' => $token));
                return false;
            }else{
                echo json_encode(array('error' => 1,'msg' => '账号或密码错误'));
                return false;
            }
        }
    }

    //找回密码
    public function findPassword(){
        $this->display();
    }

    //重设密码
    public function reSetPassword(){
        if(IS_GET){
            $phone_num = I('get.phone_num');
            //安全校验
            if(!isset($phone_num) || empty($phone_num)){
                header('location:'.$refererUrl);
            }else{
                $isHave = M('member','common_')->where('phone_num = '.$phone_num)->find();
                if(!$isHave) header('location:'.$refererUrl);
            }
            if(!isset($_SESSION['reg'][$phone_num])) header('location:'.$refererUrl);
            $this->display();
        }else{
            parse_str($_POST['data'],$data);
            //密码必填
            if(empty($data['password']) || empty($data['repassword'])){
                echo json_encode(array('error' => 1,'msg' => '密码为必填项'));
                return false;
            }
            //密码必须相同
            if($data['password'] != $data['repassword']){
                echo json_encode(array('error' => 1,'msg' => '两次密码必须相同'));
                return false;
            }
            $data['password'] = md5($data['password']);
            unset($data['repassword']);
            $bool = M('member','common_')->where('phone_num = '.$data['phone_num'])->save($data);
            if($bool){
                unset($_SESSION['reg'][$data['phone_num']]);
                echo json_encode(array('error' => 0,'msg' => '重设密码成功！请牢记'));
                return false;
            }else{
                echo json_encode(array('error' => 1,'msg' => '系统错误'));
                return false;
            }
        }
    }

    //完善资料
    public function perfect(){
        if(IS_GET){
            $phone_num = I('get.phone_num');
            //安全校验
            if(!isset($phone_num) || empty($phone_num)){
                header('location:'.$refererUrl);
            }else{
                $isHave = M('member','common_')->where('phone_num = '.$phone_num)->find();
                if(!$isHave) header('location:'.$refererUrl);
            }
            if(!isset($_SESSION['reg'][$phone_num])) header('location:'.$refererUrl);
            $this->display();
        }else{
            parse_str($_POST['data'],$data);
            //密码必填
            if(empty($data['password']) || empty($data['repassword'])){
                echo json_encode(array('error' => 1,'msg' => '密码为必填项'));
                return false;
            }
            //密码必须相同
            if($data['password'] != $data['repassword']){
                echo json_encode(array('error' => 1,'msg' => '两次密码必须相同'));
                return false;
            }
            $data['password'] = md5($data['password']);
            unset($data['repassword']);
            $bool = M('member','common_')->where('phone_num = '.$data['phone_num'])->save($data);
            if($bool){
                unset($_SESSION['reg'][$data['phone_num']]);
                echo json_encode(array('error' => 0,'msg' => '注册成功'));
                return false;
            }else{
                echo json_encode(array('error' => 1,'msg' => '系统错误'));
                return false;
            }
        }
    }

    //公用获取验证码
    public function getSmsCode(){
        $phone_num = $_POST['phone_num'];
        //检测
        if(!preg_match('/^1[0-9]{10}$/',$phone_num)){
            echo json_encode(array('error' => 1,'msg' => '请输入正确的手机号'));
            return false;
        }
        //查占用
        if((!isset($_GET['do'])) || (@$_GET['do'] != 'findPassword')){
            $isHave = M('member','common_')->where('phone_num = '.$phone_num)->count();
            if($isHave){
                echo json_encode(array('error' => 1,'msg' => '该手机号已被占用'));
                return false;
            }
        }
        //时间限制
        $phone_num = strval($phone_num);
        if(!isset($_SESSION['reg'][$phone_num]['smsStartTime'])){
            $_SESSION['reg'][$phone_num]['smsStartTime'] = time();
            //首次生成验证码
            $authnum_session = ''; 
            $str = '1234567890'; 
            $l = strlen($str);
            for($i=1;$i<=4;$i++){ 
                $num = rand(0,$l-1); 
                $authnum_session .= $str[$num];
            }
            $_SESSION['reg'][$phone_num]['smsCode'] = $authnum_session;
        }elseif(intval(date('i',(time() - $_SESSION['reg'][$phone_num]['smsStartTime']))) >= 5){
            $_SESSION['reg'][$phone_num]['smsStartTime'] = time();
            //更替生成验证码
            $authnum_session = '';
            $str = '1234567890';
            $l = strlen($str);
            for($i=1;$i<=4;$i++){ 
                $num=rand(0,$l-1); 
                $authnum_session .= $str[$num];
            }
            $_SESSION['reg'][$phone_num]['smsCode'] = $authnum_session;
        }
        //发送短信
        $url = 'http://qxt.1166.com.cn/qxtsys/recv_center';
        $CpName = 'lxzb_yzm';
        $CpPassword = 'aa123456';
        $content = "【在这里】您的验证码为：{$_SESSION['reg'][$phone_num]['smsCode']}，有效期5分钟。";
        $data = 'CpName='.$CpName.'&CpPassword='.$CpPassword.'&DesMobile='.$phone_num.'&Content='.$content;
        $res = json_decode(CurlRequest($url,false,'post',$data),true);
        //判断
        if(isset($res['smsid']) && $res['code'] == 0){
            echo json_encode(array('error' => 0,'msg' => '短信已经发送！请您注意查收'));
            return false;
        }else{
            echo json_encode(array('error' => 1,'msg' => '短信发送失败！请重试'));
            return false;
        }
    }

    //检验验证码并注册or找回密码
    public function checkSmsCode(){
        $do = I('get.do');
        $sms_code = $_POST['sms_code'];
        $phone_num = $_POST['phone_num'];
        if(empty($sms_code) || empty($phone_num)){
            echo json_encode(array('error' => 1,'msg' => '请填写手机号或验证码'));
            return false;
        }
        if($sms_code == $_SESSION['reg'][$phone_num]['smsCode']){
            //注册or找回密码
            switch ($do) {
                case 'reg':
                    $added = M('member','common_')->add(array('phone_num' => $phone_num,'create_time' => date('Y-m-d H:i:s')));
                    if($added){
                        echo json_encode(array('error' => 0,'phone_num' => $phone_num));
                        return false;
                    }else{
                        echo json_encode(array('error' => 1,'msg' => '系统错误'));
                        return false;
                    }
                    break;
                case 'findPassword':
                    echo json_encode(array('error' => 0,'phone_num' => $phone_num));
                    return false;
                    break;
            }
        }else{
            echo json_encode(array('error' => 1,'msg' => '短信验证码错误'));
            return false;
        }
    }
}