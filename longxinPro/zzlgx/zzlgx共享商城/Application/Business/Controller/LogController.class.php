<?php
namespace Business\Controller;

use Think\Controller;

class LogController extends Controller {
    
    public function index()
    {
        header('location:/business/log/in');
    }

    //登录
    public function in()
    {   
        if(IS_POST){
            $username = I('post.username');
            $password = I('post.password');
            $bRes = M('business')->where(array('business_id' => $username,'password' => md5($password)))->find();
            $pRes = M('business')->where(array('phone_num' => $username,'password' => md5($password)))->find();
            if($bRes){
                $_SESSION['business'] = $bRes;
                M('business')->where(array('business_id' => $username))->setField('last_login',date('Y-m-d H:i:s'));
                if ($bRes['lose_time'] < date('Y-m-d H:i:s')) {
                    M('business')->where(array('business_id' => $username,'password' => md5($password)))->setField('status',0);
                }
                echo json_encode(array('error' => 0,'msg' => '登录成功'));
                exit;
            }elseif($pRes){
                $_SESSION['business'] = $pRes;
                M('business')->where(array('phone_num' => $username))->setField('last_login',date('Y-m-d H:i:s'));
                if ($pRes['lose_time'] < date('Y-m-d H:i:s')) {
                    M('business')->where(array('phone_num' => $username,'password' => md5($password)))->setField('status',0);
                }
                echo json_encode(array('error' => 0,'msg' => '登录成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '登录失败，账号或密码错误'));
                exit;
            }
        }
        $this->display();
    }

    //检查企业
    public function check()
    {
        if(IS_GET){
            $this->display();
        }else{
            $data = I('post.');
            $res = longxinApi($data['type'],$data['value']);
            $res['REGCAP'] = $res['REGCAP'] > 0 ? $res['REGCAP'].'万' : $res['REGCAP'];
            $res['type'] = $data['type'];
            $seRes = urlencode(base64_encode(json_encode($res)));
            $con = "";
            if($data['type'] == 'geren'){
                $con .= "<tr><td>个体户名称</td><td>{$res['ENTNAME']}</td></tr>";
                $con .= "<tr><td>统一社会信用代码</td><td>{$res['UNISCID']}</td></tr>";
                $con .= "<tr><td>经营者</td><td>{$res['NAME']}</td></tr>";
                $con .= "<tr><td>成立日期</td><td>{$res['ESDATE']}</td></tr>";
                $con .= "<tr><td>经营状态</td><td>{$res['ENTSTATUS']}</td></tr>";
                $con .= "<tr><td>注册资本(金)</td><td>{$res['REGCAP']}</td></tr>";
                $con .= "<tr><td>注册地址</td><td>{$res['DOM']}</td></tr>";
                $con .= "<tr><td>企业类型</td><td>{$res['ENTTYPE']}</td></tr>";
                $con .= "<tr><td>组成形式</td><td>{$res['COMPFORM']}</td></tr>";
                $con .= "<tr><td>经营业务范围</td><td>{$res['OPSCOANDFORM']}</td></tr>";
                $con .= "<tr><td>登记机关</td><td>{$res['REGORG']}</td></tr>";
                $con .= "<tr><td>经营期限自</td><td>{$res['OPFROM']}</td></tr>";
                $con .= "<tr><td>经营期限至</td><td>{$res['OPTO']}</td></tr>";
                $con .= "<tr><td>核准日期</td><td>{$res['APPRDATE']}</td></tr>";
                $con .= "<tr><td>吊销日期</td><td>{$res['REVDATE']}</td></tr>";
                $con .= "<tr><td>注销日期</td><td>{$res['CANDATE']}</td></tr>";
                echo json_encode(array('error' => 0,'res' => $con,'seRes' => $seRes));
                exit;
            }elseif($data['type'] == 'qiye'){
                $con .= "<tr><td>企业名称</td><td>{$res['ENTNAME']}</td></tr>";
                $con .= "<tr><td>曾用名</td><td>{$res['OLDNAME']}</td></tr>";
                $con .= "<tr><td>统一社会信用代码</td><td>{$res['SHXYDM']}</td></tr>";
                $con .= "<tr><td>法定代表人</td><td>{$res['FRDB']}</td></tr>";
                $con .= "<tr><td>成立日期</td><td>{$res['ESDATE']}</td></tr>";
                $con .= "<tr><td>经营状态</td><td>{$res['ENTSTATUS']}</td></tr>";
                $con .= "<tr><td>注册资本</td><td>{$res['REGCAP']}</td></tr>";
                $con .= "<tr><td>注册资本币种</td><td>{$res['REGCAPCUR']}</td></tr>";
                $con .= "<tr><td>注册地址</td><td>{$res['DOM']}</td></tr>";
                $con .= "<tr><td>企业类型</td><td>{$res['ENTTYPE']}</td></tr>";
                $con .= "<tr><td>经营业务范围</td><td>{$res['OPSCOPE']}</td></tr>";
                $con .= "<tr><td>登记机关</td><td>{$res['REGORG']}</td></tr>";
                $con .= "<tr><td>经营期限自</td><td>{$res['OPFROM']}</td></tr>";
                $con .= "<tr><td>经营期限至</td><td>{$res['OPTO']}</td></tr>";
                $con .= "<tr><td>核准日期</td><td>{$res['APPRDATE']}</td></tr>";
                $con .= "<tr><td>死亡日期</td><td>{$res['ENDDATE']}</td></tr>";
                $con .= "<tr><td>吊销日期</td><td>{$res['REVDATE']}</td></tr>";
                $con .= "<tr><td>注销日期</td><td>{$res['CANDATE']}</td></tr>";
                echo json_encode(array('error' => 0,'res' => $con,'seRes' => $seRes));
                exit;
            }
        }
    }

    //注册
    public function reg()
    {
        if (IS_POST) {
            $data = $_POST;
            //检查为空的错误提示
            $err_msg_array = array(
                        'legal_name' => '未填写法人姓名',
                        'card_num' => '未填写法人身份证号',
                        'company_name' => '未填写公司名称',
                        'exchange_name' => '未填写积分商城名称',
                        'business_address' => '未填写营业地址',
                        'profession_id' => '未选择行业类别',
                        'province_id' => '未选择省份',
                        'city_id' => '未选择城市',
                        'area_id' => '未选择区(县)',
                        'password' => '未填写密码',
                        'repassword' => '两次密码必须相同',
                        'phone_num' => '未填写法人手机号',
                        'phone_code' => '未填写短信验证码',
                        'seRes' => '参数错误'
                    );
            //验证选项是否为空
            foreach ($data as $key => $value) {
                if(empty($value) && $key != 'leader'){
                    echo json_encode(array('error' => 1,'msg' => $err_msg_array[$key]));
                    exit;  
                }
            }

            //密码必须相同
            if($data['password'] != $data['repassword']){
                echo json_encode(array('error' => 1,'msg' => '两次密码必须相同'));
                exit;
            }else{
                unset($data['repassword']);
            }

            //密码最少位数
            if(strlen($data['password']) < 6){
                echo json_encode(array('error' => 1,'msg' => '密码最少应该输入6位'));
                exit;
            }

            //是否同意注册协议
            if(!$data['agree']){
                echo json_encode(array('error' => 1,'msg' => '未同意注册协议'));
                exit; 
            }else{
                unset($data['agree']);
            }

            //推荐人ID
            if(!empty($data['leader'])){
                $findLeader = M('business')->where(array('business_id' => $data['leader']))->find();
                if (!$findLeader) {
                    echo json_encode(array('error' => 1,'msg' => '推荐人ID不存在'));
                    exit;
                }elseif($findLeader['status'] != 1){
                    echo json_encode(array('error' => 1,'msg' => '不可填写推荐人（ID:'.$data['leader'].'），因为对方是未激活用户'));
                    exit;
                }
            }else{
                $data['leader'] = 10001; //默认是官方平台会员
            }

            //重复的商城名
            $data['exchange_name'] = filter_mark($data['exchange_name']);
            if(!empty($data['exchange_name'])){
                $is_set = M('business')->where(array('exchange_name' => $data['exchange_name']))->find();
                if ($is_set) {
                    echo json_encode(array('error' => 1,'msg' => '系统中已存在该积分商城名称'));
                    exit;
                }
            }else{
                echo json_encode(array('error' => 1,'msg' => '积分商城名称非法'));
                exit;
            }

            //企业参数
            $seRes = json_decode(base64_decode(urldecode($data['seRes'])),true);
            if(count($seRes) <= 0){
                echo json_encode(array('error' => 1,'msg' => '企业信息参数错误'));
                exit;
            }

            //手机号检测
            if(!preg_match('/^1[0-9]{10}$/',$data['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '请输入正确的手机号'));
                exit;
            }

            //手机验证码
            if (empty($_SESSION['reg'][$data['phone_num']]['sms_code'])) {
                echo json_encode(array('error' => 1,'msg' => '未发送短信验证码或已过期'));
                exit;
            }
            if ($_SESSION['reg'][$data['phone_num']]['sms_time'] < time()) {
                unset($_SESSION['reg']);
                echo json_encode(array('error' => 1,'msg' => '短信验证码已过期'));
                exit;
            }
            if($data['phone_code'] != $_SESSION['reg'][$data['phone_num']]['sms_code']){
                echo json_encode(array('error' => 1,'msg' => '短信验证码错误'));
                exit;
            }else{
                unset($data['phone_code']);
                unset($_SESSION['reg']);
            }
            //密码
            $data['password'] = md5($data['password']);
            unset($data['repassword']);
            $data['add_time'] = date('Y-m-d H:i:s',time());
            //修改审核状态为审核中
            $data['verify'] = 1;
            //代理商
            $data['is_dai'] = 0;
            //事务
            M('business')->startTrans();
            M('business_info')->startTrans();

            $business_id = M('business')->add($data);
            if($business_id){
                $seRes['business_id'] = $business_id;
                $bool = M('business_info')->add($seRes);
                if($bool){
                    M('business')->commit();;
                    M('business_info')->commit();
                    echo json_encode(array('error' => 0,'msg' => '恭喜！注册成功'));
                    exit;
                }else{
                    M('business')->rollback();
                    M('business_info')->rollback();
                    echo json_encode(array('error' => 1,'msg' => '系统错误，注册失败'));
                    exit;
                }
            }else{
                M('business')->rollback();
                M('business_info')->rollback();
                echo json_encode(array('error' => 1,'msg' => '系统错误，注册失败'));
                exit;
            }
        }else{
            $seRes = I('get.seRes');
            if(!$seRes){
                header('location:/Business/Log/check');
                exit;
            }
            $seRes = json_decode(base64_decode(urldecode($seRes)),true);
            $professions = M('profession')->select(); //行业类别
            $provinces = M('province')->select(); //省份
            $this->assign('seRes',$seRes);
            $this->assign('professions',$professions);
            $this->assign('provinces',$provinces);
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
        $is_reg = M('business')->where(array('phone_num' => $phone_num))->find();
        if (!isset($_POST['find_pass'])) {
            if ($is_reg) {
                echo json_encode(array('error' => 1,'msg' => '手机号已注册'));
                exit;
            }
        }else{
            if (!$is_reg) {
                echo json_encode(array('error' => 1,'msg' => '手机号未注册'));
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
            }else{
                $_SESSION['reg'][$phone_num]['sms_code'] = $sms_code;
                $_SESSION['reg'][$phone_num]['sms_time'] = time()+300;
            }
            echo json_encode(array('error' => 0,'msg' => '发送成功'));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '发送失败'));
            exit;
        }
    }

    //获取城市列表
    public function get_citys(){
        if ($_POST['province_id']) {
            $citys = M('city')->where(array('province_id' => $_POST['province_id']))->select(); //城市
            echo json_encode(array('error' => 0,'data' => $citys));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '参数错误'));
            exit;
        }
    }

    //获取区(县)列表
    public function get_areas(){
        if ($_POST['city_id']) {
            $areas = M('area')->where(array('city_id' => $_POST['city_id']))->select(); //区(县)
            echo json_encode(array('error' => 0,'data' => $areas));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '参数错误'));
            exit;
        }
    }

    //退出
    public function out()
    {
        unset($_SESSION['business']);
        header('location:/business/log/in');
    }

    //找回密码
    public function findpassword()
    {
        if (isset($_POST['sms_code'])) {
            //手机验证码
            if (empty($_POST['phone_num'])) {
                echo json_encode(array('error' => 1,'msg' => '手机号不能为空'));
                exit;
            }
            
            if(!preg_match('/^1[0-9]{10}$/',$_POST['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '请输入正确的手机号'));
                exit;
            }

            if(M('business')->where(array('phone_num'=>$_POST['phone_num']))->count()<1){
                echo json_encode(array('error' => 1,'msg' => '手机号未注册'));
                exit;
            }

            if (empty($_SESSION['find_pass'][$_POST['phone_num']]['sms_code'])) {
                echo json_encode(array('error' => 1,'msg' => '未发送短信验证码或已过期'));
                exit;
            }
            if ($_SESSION['find_pass'][$_POST['phone_num']]['sms_time'] < time()) {
                unset($_SESSION['find_pass']);
                echo json_encode(array('error' => 1,'msg' => '短信验证码已过期'));
                exit;
            }
            if($_POST['sms_code'] != $_SESSION['find_pass'][$_POST['phone_num']]['sms_code']){
                echo json_encode(array('error' => 1,'msg' => '短信验证码错误'));
                exit;
            }else{
                unset($_SESSION['find_pass']);
                echo json_encode(array('error' => 0,'msg' => '请重新设置密码'));
                exit;
            }

        }else if($_POST['password']){
            if (empty($_POST['password'])) {
                echo json_encode(array('error' => 1,'msg' => '未填写新密码'));
                exit;
            }

            if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16) {
                echo json_encode(array('error' => 1,'msg' => '请输入6~16位密码'.strlen($_POST['password'])));
                exit;
            }

            $is_save = M('business')->where(array('phone_num' => $_POST['phone_num']))->setField('password',md5($_POST['password']));
            if (is_int($is_save)) {
                echo json_encode(array('error' => 0,'msg' => '重置成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '重置失败'));
                exit;
            }
            
        }
        $this->display();
    }

}