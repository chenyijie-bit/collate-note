<?php

namespace Exchange\Controller;

use Think\Controller;

class LogController extends Controller {
    
    //登录
    public function in()
    {
        if (IS_POST) {
            if (empty(I('post.phone_num'))) {
                echo json_encode(array('error' => 1,'msg' => '请输入手机号'));
                exit;
            }
            //检测手机号
            if(!preg_match('/^1[0-9]{10}$/',I('post.phone_num'))){
                echo json_encode(array('error' => 1,'msg' => '请输入正确的手机号'));
                exit;
            }
            if (empty(I('post.password'))) {
                echo json_encode(array('error' => 1,'msg' => '请输入密码'));
                exit;
            }
            
            $result = M('member')->where(array('phone_num'=>I('post.phone_num'),'password'=>md5(I('post.password'))))->find();
            if ($result) {
                $_SESSION['exchange']['phone_num'] = $result['phone_num'];
                $_SESSION['exchange']['member_name'] = $result['name'];
                if ($result['pay_pass']) {
                    $_SESSION['exchange']['pay_pass'] = 'yes';
                }
                echo json_encode(array('error' => 0,'msg' => '登录成功'));
                exit;
            }else{
                $is_password = M('member')->where(array('phone_num'=>I('post.phone_num')))->getField('password');
                if (!$is_password) {
                    echo json_encode(array('error' => 1,'msg' => '手机号未注册'));
                    exit;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '用户名或密码错误'));
                    exit;
                }
            }
        }
        $this->display();
    }

    //注册
    public function reg()
    {   
        //注册的时候先看带的商户id表里是否有此用户，否则加到主member表，再分配到此商户表，加标识
        //如果主member表有，就加到此商户表
        if (IS_POST) {
            if (empty($_POST['phone_num'])) {
                echo json_encode(array('error'=>1,'msg'=>'请填写手机号'));
                exit;
            }
            if(!preg_match('/^1[0-9]{10}$/',$_POST['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '必须输入11位的手机号'));
                exit;
            }
            if (empty($_POST['phone_code'])) {
                echo json_encode(array('error'=>1,'msg'=>'请填写手机验证码'));
                exit;
            }
            if (empty($_POST['password'])) {
                echo json_encode(array('error'=>1,'msg'=>'请填写密码'));
                exit;
            }
            if ($_POST['password'] != $_POST['repassword']) {
                echo json_encode(array('error'=>1,'msg'=>'两次密码不一致'));
                exit;
            }
            if ($_POST['phone_code'] != $_SESSION['reg'][$_POST['phone_num']]['sms_code']) {
                echo json_encode(array('error'=>1,'msg'=>'手机验证码错误'));
                exit;
            }
            if (time() > $_SESSION['reg'][$_POST['phone_num']]['sms_time']) {
                echo json_encode(array('error'=>1,'msg'=>'手机验证码已过期'));
                exit;
            }
            $is_reg = M('member')->field('password,phone_num,name')->where(array('phone_num'=>$_POST['phone_num']))->find();
            if ($is_reg['password']) {
                echo json_encode(array('error'=>1,'msg'=>'手机号已注册'));
                exit;
            }
            if (empty($is_reg['password']) && $is_reg['phone_num']) {
                $result = M('member')->where(array('phone_num'=>$_POST['phone_num']))->setField('password',md5($_POST['password']));
                if ($result) {
                    $is_my_vip = M('member_'.$_SESSION['business']['business_id'])->where(array('phone_num'=>$_POST['phone_num']))->find();
                    if (!$is_my_vip) {
                        $data['phone_num'] = $_POST['phone_num'];
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $data['join_time'] = date('Y-m-d');
                        $data['is_formal'] = 2;
                        $data['level'] = 'D';
                        M('member_'.$_SESSION['business']['business_id'])->add($data);
                    }
                    $_SESSION['exchange']['phone_num'] = $is_reg['phone_num'];
                    $_SESSION['exchange']['member_name'] = $is_reg['name'];

                    echo json_encode(array('error' => 0,'msg' => '注册成功'));
                    exit;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '注册失败'));
                    exit;
                }
            }elseif(empty($is_reg['password']) && empty($is_reg['phone_num'])){
                $data['phone_num'] = $_POST['phone_num'];
                $data['add_time'] = date('Y-m-d H:i:s');
                $data['join_time'] = date('Y-m-d');
                $data['is_formal'] = 2;
                $data['level'] = 'D';
                //事务开启，查子表用安全id
                M('member_'.$_SESSION['business']['business_id'])->startTrans();
                //添加子表，查子表用安全id
                M('member_'.$_SESSION['business']['business_id'])->add($data);
                $data['password'] = md5($_POST['password']);
                $sql = <<<INSERT
insert into `member` (`phone_num`,`add_time`,`password`) values ('{$data['phone_num']}','{$data['add_time']}','{$data['password']}')
INSERT;
                M('member')->delete($data['phone_num']);
                $is_add = M()->execute($sql);
                if($is_add){ //查子表用安全id
                    M('member_'.$_SESSION['business']['business_id'])->commit();
                    $_SESSION['exchange']['phone_num'] = $data['phone_num'];
                    echo json_encode(array('error' => 0,'msg' => '注册成功'));
                    exit;
                }else{ //查子表用安全id
                    M('member_'.$_SESSION['business']['business_id'])->rollback();
                    echo json_encode(array('error' => 1,'msg' => '注册失败'));
                    exit;
                }
            }
        }else{
            $this->display();
        }
    }

    //生成短信验证码并发送
    public function send_code(){
        $phone_num = $_POST['phone_num'];
        if (empty($phone_num)) {
            echo json_encode(array('error' => 1,'msg' => '手机号不能为空'));
            exit;
        }
        //验证手机号是否已注册
        $is_reg = M('member')->where(array('phone_num' => $phone_num))->find();
        if (isset($_POST['find_pass'])) {
            if (empty($is_reg['password'])) {
                echo json_encode(array('error' => 1,'msg' => '手机号未注册'));
                exit;
            }
        }elseif (isset($_POST['pay_pass'])) {
            if (empty($is_reg['password'])) {
                echo json_encode(array('error' => 1,'msg' => '参数错误'));
                exit;
            }
        }else{
            if ($is_reg['password']) {
                echo json_encode(array('error' => 1,'msg' => '手机号已注册'));
                exit;
            }
        }
        
        //生成4位的验证码
        $sms_code = rand(1000,9999);
        //发送短信
        $is_send = sendSMS($phone_num,$sms_code);
        if ($is_send) {
            //保存生成的短信验证码
            if ($_POST['find_pass']) {
                $_SESSION['find_pass'][$phone_num]['sms_code'] = $sms_code;
                $_SESSION['find_pass'][$phone_num]['sms_time'] = time()+300;
            }elseif ($_POST['pay_pass']) {
                $_SESSION['pay_pass'][$phone_num]['sms_code'] = $sms_code;
                $_SESSION['pay_pass'][$phone_num]['sms_time'] = time()+300;
            }else{
                $_SESSION['reg'][$phone_num]['sms_code'] = $sms_code;
                $_SESSION['reg'][$phone_num]['sms_time'] = time()+300;
            }
            echo json_encode(array('error' => 0,'msg' => '发送成功'.$sms_code));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '发送失败'));
            exit;
        }
    }

    //退出
    public function out()
    {
        $business_id = $_SESSION['exchange']['business_id'];
        unset($_SESSION['exchange']);
        header('location:/exchange/?b='.$business_id);
    }

    //找回密码
    public function findpassword()
    {
        if (IS_POST) {
            if (empty($_POST['phone_num'])) {
                echo json_encode(array('error'=>1,'msg'=>'请填写手机号'));
                exit;
            }
            if(!preg_match('/^1[0-9]{10}$/',$_POST['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '必须输入11位的手机号'));
                exit;
            }
            if (empty($_POST['phone_code'])) {
                echo json_encode(array('error'=>1,'msg'=>'请填写手机验证码'));
                exit;
            }
            if (empty($_POST['password'])) {
                echo json_encode(array('error'=>1,'msg'=>'请填写密码'));
                exit;
            }
            if ($_POST['password'] != $_POST['repassword']) {
                echo json_encode(array('error'=>1,'msg'=>'两次密码不一致'));
                exit;
            }
            if ($_POST['phone_code'] != $_SESSION['find_pass'][$_POST['phone_num']]['sms_code']) {
                echo json_encode(array('error'=>1,'msg'=>'手机验证码错误'));
                exit;
            }
            if (time() > $_SESSION['find_pass'][$_POST['phone_num']]['sms_time']) {
                echo json_encode(array('error'=>1,'msg'=>'手机验证码已过期'));
                exit;
            }
            $is_reg = M('member')->field('password,phone_num,name')->where(array('phone_num'=>$_POST['phone_num']))->find();
            if (!$is_reg['password']) {
                echo json_encode(array('error'=>1,'msg'=>'手机号未注册'));
                exit;
            }
            $result = M('member')->where(array('phone_num'=>$_POST['phone_num']))->setField('password',md5($_POST['password']));
            if (is_int($result)) {
                echo json_encode(array('error'=>0,'msg'=>'重置密码成功'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'重置密码失败'));
                exit;
            }
        }
    	$this->display();
    }

    //补全信息
    public function completion()
    {
        if (IS_POST) {
            if (!$_POST['name']) {
                echo json_encode(array('error'=>1,'msg'=>'请输入您的姓名'));
                exit;
            }
            if (!$_POST['birthday']) {
                echo json_encode(array('error'=>1,'msg'=>'请选择您的生日'));
                exit;
            }
            if (!$_POST['sex']) {
                echo json_encode(array('error'=>1,'msg'=>'请选择您的性别'));
                exit;
            }
            if (!$_POST['pay_pass'] || strlen($_POST['pay_pass']) != 6) {
                echo json_encode(array('error'=>1,'msg'=>'请输入6位支付密码'));
                exit;
            }
            if ($_POST['pay_pass'] != $_POST['repay_pass']) {
                echo json_encode(array('error'=>1,'msg'=>'两次密码不一致'));
                exit;
            }
            if (md5($_POST['pay_pass']) == M('member')->where(array('phone_num'=>$_SESSION['exchange']['phone_num']))->getField('password')) {
                echo json_encode(array('error'=>1,'msg'=>'支付密码与登录密码相同'));
                exit;
            }
            $data['name'] = $_POST['name'];
            $data['birth_time'] = $_POST['birthday'];
            $data['sex'] = $_POST['sex']=='男'?0:1;
            $data['pay_pass'] = md5($_POST['pay_pass']);
            $result = M('member')->where(array('phone_num'=>$_SESSION['exchange']['phone_num']))->save($data);
            unset($data['pay_pass']);
            //M('member_10001')->where(array('phone_num'=>$_SESSION['exchange']['phone_num']))->save($data);
            if ($result) {
                $_SESSION['exchange']['member_name'] = $data['name'];
                $_SESSION['exchange']['pay_pass'] = 'yes';
                echo json_encode(array('error'=>0,'msg'=>'提交成功'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'提交失败'));
                exit;
            }
        }
        $this->assign('phone_num',$_SESSION['exchange']['phone_num']);
        $this->display();
    }

    //补全支付密码
    public function pay_pass()
    {
        if (IS_POST) {
            if (!$_POST['pay_pass'] || strlen($_POST['pay_pass']) != 6) {
                echo json_encode(array('error'=>1,'msg'=>'请输入6位支付密码'));
                exit;
            }
            if ($_POST['pay_pass'] != $_POST['repay_pass']) {
                echo json_encode(array('error'=>1,'msg'=>'两次密码不一致'));
                exit;
            }
            if (md5($_POST['pay_pass']) == M('member')->where(array('phone_num'=>$_SESSION['exchange']['phone_num']))->getField('password')) {
                echo json_encode(array('error'=>1,'msg'=>'支付密码与登录密码相同'));
                exit;
            }
            $data['pay_pass'] = md5($_POST['pay_pass']);
            $result = M('member')->where(array('phone_num'=>$_SESSION['exchange']['phone_num']))->save($data);
            if ($result) {
                $_SESSION['exchange']['pay_pass'] = 'yes';
                echo json_encode(array('error'=>0,'msg'=>'提交成功'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'提交失败'));
                exit;
            }
        }
        $this->assign('phone_num',$_SESSION['exchange']['phone_num']);
        $this->display();
    }
}