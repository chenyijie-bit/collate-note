<?php
namespace Business\Controller;

class CenterController extends CommonController {
    
    //商户资料
    public function info()
    {   
        if (IS_POST) {
            $save_data = $_POST;
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
                    );
            //验证选项是否为空
            foreach ($save_data as $key => $value) {
                if(empty($value)){
                    echo json_encode(array('error' => 1,'msg' => $err_msg_array[$key]));
                    exit;  
                }
            }
            $verify = M('business')->where(array('business_id'=>$this->business_id()))->getField('verify');
            if ($verify == 0 || $verify == 2) {
                $save_data['verify'] = 1;
            }
            //开启商品表事务
            M('goods')->startTrans();
            $is_save = M('business')->where(array('business_id'=>$this->business_id()))->save($save_data);
            if (is_int($is_save)) {
                //改商品所在地
                M('goods')->where(array('business_id'=>$this->business_id()))->save(array('province_id'=>$save_data['province_id'],'city_id'=>$save_data['city_id'],'area_id'=>$save_data['area_id']));
                M('goods')->commit();
                echo json_encode(array('error' => 0,'msg' => '修改成功'));
                exit;
            }else{
                M('goods')->rollback();
                echo json_encode(array('error' => 1,'msg' => '修改失败'));
                exit;
            }
        }
        $info = M('business')->where(array('business_id'=>$this->business_id()))->find();
        $professions = M('profession')->select();
        $provinces = M('province')->select();
        $citys = M('city')->where(array('province_id'=>$info['province_id']))->select();
        $areas = M('area')->where(array('city_id'=>$info['city_id']))->select();
        switch ($info['verify']) {
            case '0':
                $res = array(
                    'button' => '<p id="signupForm_submit" class="btn btn-primary ">提交审核</p>',
                    'tishi' => '',
                    'lock' => '',
                    'alert' => '<div class="alert alert-warning" style="text-align:center;">未提交审核，请填写公司信息后提交审核！</div>'
                );
                break;
            case '1':
                $res = array(
                    'button' => '<p id="" class="btn btn-primary ">审核中...</p>',
                    'tishi' => '',
                    'lock' => 'disabled="disabled"',
                    'alert' => '<div class="alert alert-warning" style="text-align:center;">审核中，最多7个工作日审核完毕，请请耐心等待审核...</div>'
                );
                break;
            case '2':
                $res = array(
                    'button' => '<p id="signupForm_submit" class="btn btn-primary ">提交审核</p>',
                    'tishi' => '',
                    'lock' => '',
                    'alert' => '<div class="alert alert-danger" style="text-align:center;">审核失败，请重新修改后再提交审核！</div>'
                );
                break;
            case '3':
                $res = array(
                    'button' => '<p id="" class="btn btn-primary ">通过审核</p>',
                    'tishi' => '',
                    'lock' => 'disabled="disabled"',
                    'alert' => '<div class="alert alert-success" style="text-align:center;">审核通过，如果需要重新修改公司信息，请联系我们！</div>'
                );
                break;
            
            default:
                # code...
                break;
        }
        $this->assign('res',$res);
        $this->assign('info',$info);
        $this->assign('provinces',$provinces);
        $this->assign('professions',$professions);
        $this->assign('citys',$citys);
        $this->assign('areas',$areas);
        $this->display();
    }

    //修改密码
    public function repassword()
    {
        if (IS_POST) {
            if (empty($_POST['o_password'])) {
                echo json_encode(array('error' => 1,'msg' => '未填写旧密码'));
                exit;
            }
            
            if (empty($_POST['n_password'])) {
                echo json_encode(array('error' => 1,'msg' => '未填写新密码'));
                exit;
            }

            if (strlen($_POST['n_password']) < 6 || strlen($_POST['n_password']) > 16) {
                echo json_encode(array('error' => 1,'msg' => '请输入6~16位密码'));
                exit;
            }

            $old_pass = M('business')->where(array('business_id' => $this->business_id()))->getField('password');
            if ($old_pass == md5($_POST['o_password'])) {
                $is_save = M('business')->where(array('business_id' => $this->business_id()))->setField('password',md5($_POST['n_password']));
                echo json_encode(array('error' => 0,'msg' => '修改成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '原密码错误'));
                exit; 
            }
            exit;
        }
        $this->display();
    }

    //我的通知
    public function notices()
    {
        //总数
        $countNum = M('notices')->where(array('business_id'=>$this->business_id()))->count();
        //分页
        $page = new \Think\Page($countNum,$this->step);
        $notices = M('notices')->where(array('business_id'=>$this->business_id()))->order('add_time desc')->limit($page->firstRow.','.$this->step)->select();
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('notices',$notices);
        $this->display();
    }

    //我的通知详情
    public function notices_detail()
    {
        if (empty($_GET['notices_id'])) {
            echo '<script>window.location.href="/Business/Center/notices";</script>';
        }
        $notices_info = M('notices')->where(array('notices_id'=>$_GET['notices_id']))->find();
        M('notices')->where(array('notices_id'=>$_GET['notices_id']))->setField('is_read',1);
        $this->assign('notices_info',$notices_info);
        $this->display();
    }

    //我的未读通知数
    public function notices_num(){
        $notices_num = M('notices')->where(array('business_id'=>$this->business_id(),'is_read'=>0))->count();
        $business_logo = M('business')->where(array('business_id'=>$this->business_id()))->getField('business_logo');
        if (empty($business_logo)) {
            $business_logo = '/Public/hplus/img/black_logo.png';
        }
        echo json_encode(array('error' => 0,'notices_num' => $notices_num,'business_logo' => $business_logo));
        exit;
    }

    //删除通知
    public function del_notice(){
        if (!empty($_POST['notices_id'])) {
            $result = M('notices')->where(array('notices_id'=>$_POST['notices_id']))->delete();
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '删除成功！'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '删除失败，请重新试！'));
                exit;
            }
        }
    }
   
    //账户激活
    public function activate()
    {   
        if(IS_GET){
            //系统规定的激活金额
            $system_config = M('system_config')->select();
            $activate_price = $system_config[0]['activate_price'];
            //查询
            $memberInfo = M('business')->field('business_id,status,lose_time,activate_time')->where(array('business_id'=>$this->business_id()))->find();
            $this->assign('result',$memberInfo);
            $this->assign('activate_price',$activate_price);
            $this->display();
        }elseif(IS_POST){
            //旧的激活流程
        }
    }

    //支付宝执行账户激活
    public function do_activate(){
        $system_config = M('system_config')->select();
        //系统规定的激活金额
        $activate_price = $system_config[0]['activate_price'];
        require_once("Public/plugin/alipay/AopSdk.php");
        //构造参数  
        $aop = new \AopClient ();  
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';  
        $aop->appId = '2017111309909702';  
        $aop->rsaPrivateKey = 'MIIEogIBAAKCAQEAsVcYRprdlEJ4fVs7BKk2At4Ck7jLg88FI2Hu4NsiEJL5JmzPRpFPctLOBme0crZoeCgBuZuuRQnHQqrpL7sdLzosLg5ShclhaBEI0WaeBduj0rt4yRBQ7sIN07OBYUX8U75BGXLCpHagCHkoKM9ZeoT/4Zk1s5887XgXrzrKBIvXARmBs1We6UWHQnVh7/xwWo6C6L/LjSwFK2AiWojmXGxKbmtf6EIch5H7oJzqWEGi8ADH9hkcojebNBxI3tI+vXqceD3TgvhScZggwv2VEQL4TfQ7e6GoR8wWxWR4OEW2kUUJ1Iuf5pY3lnJV8qugsmyqyNvX9wUtqCvKdICZ5QIDAQABAoIBABlaIx1M3GpqikEZfrlu20rTpDisDWQdf1WMlZLNoPQPntCwc31aHqqCmnNt9e0ESLEMvxpiuCokeLj+J/Hr5QMwZMp8v61imas/7CvLaMHboXLp3B2aWIeZdFKUceWPFMCADVxu/IZ4cu5jK6uR4O/T/aSpu3SfSh2EspYZaHH3srBRxZF4QVRLz4WjVm64I+3xxfx714zjkQDjNlvLkcvOHTUc/ZUc4blJCHzE0KcB75vQA7dMFIA0cXxPBWqyUomNKoyCdggmPW9JHa0zIn6ZCnzmYowQwupZM8B/wXZnW44pdjqLyiHL0DX2dziMTlNeUyXX3sSQPssSDTREcM0CgYEA6JNIX53qE2vxbOGdn62GDoaXjlavhjBTCmOxWZIYO6WIAPGvMqz74Elo9p1rLafTnTaChAk0ieircsTVYcsb4jMQIZE7yuDxu44PQ/+FZH2qeJPUPMeivfd6FlIhz8yG4Gd77xJ7Qml2eHNNPFtOrCP77IB49z2mQxFtTPJJKqcCgYEAwzOhl5knmF8arDHuWp4XjLKALdy+wxamDOIuaKEKZ1mjCWDp5feiZ3cORoOODcrIEOE0nL9nWiDxkPvRjYsgQ3XnUke8NKmrSJA8TRWjbMcqPJ7b5BO7LeIlZ+9aUlhupCxThJyTHryLfE77UPoZI+QHef6/RXvWHzTfi4RphJMCgYB3qSeogoAnw/bwVVibClWZ7afWhUVD3mMrSkW6Vw9+yNkj2zWP9i6VpE+L60x0rg+TqLMYKgBNIFft8dXzveO3yxv2mVnRNVFKdXnnO3WvUXS/GxgsuW5DHSxEhbd9ybZviO7b/39JmSdqK7DGaBgfO1hnw2X5l0+O3E2HNHVuqwKBgDX7dVjDVhvhUTMFq/ELf1+9jY0hWvAAt3MgqcztnD7wnxHc51JdpWAPoLcHcqWFysZAQZiHpkFakvORcGZAb2+4j3xFslquAVxT5xk6PrO6cIfLNuxgOId73vRbURMsuYxVZdNqqZT6d2itPvsp7wHp8ddfB+5jTNfce2XN/JBZAoGADugJa+/qDjWKrlNuUoJUEsppPLk36DvmR0Whk8OJ6Lqw3MbURbH//P9yCj3dTkOFqQ5Gdt+PHHY7IEQAP3JaNju/iJGF+vmQRdr/Mj9yz9CKxv23PT9iD2FxqktO4lP7YVtO8GBskpDkFDpIitt1Pep84IIwR6XAd3k/n8oyxpQ=';  
        $aop->apiVersion = '1.0';  
        $aop->signType = 'RSA2';  
        $aop->postCharset= 'utf-8';  
        $aop->format='json';  
        $request = new \AlipayTradePagePayRequest ();  
        $request->setReturnUrl('http://gx.zzlhi.com/business/alipay/return_url');  
        $request->setNotifyUrl('http://gx.zzlhi.com/business/alipay/notify_url');
        $bparams = urlencode('type=jihuo&business_id='.$this->business_id());
        $request->setBizContent('{"product_code":"FAST_INSTANT_TRADE_PAY","out_trade_no":"'.time().'","subject":"在这里商企平台账户激活费","passback_params":"'.$bparams.'","total_amount":"'.$activate_price.'","body":"在这里商企平台的企业用户账户激活三个月使用权。"}');

        //请求  
        $result = $aop->pageExecute ($request);

        //输出  
        echo $result;
    }

    //公告列表
    public function placard()
    {   
        //总数
        $countNum = M('placard')->count();
        //分页
        $page = new \Think\Page($countNum,$this->step);
        $result = M('placard')->order('add_time desc')->limit($page->firstRow.','.$this->step)->select();
        $this->assign('result',$result);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    //公告详情
    public function placard_detail()
    {   
        $placard_id = intval(I('get.placard_id'));
        $result = M('placard')->find($placard_id);
        if(!$result){
            foreach ($result as $key => $value) {
                $result[$key] = '';
            }
        }
        $this->assign('result',$result);
        $this->display();
    }

    //联系我们
    public function about_us()
    {
        $about_us = M('about_us')->select();
        $this->assign('about_us',$about_us[0]);
        $this->display();
    }

    //推荐奖励
    public function recommend()
    {
        //总数
        $countNum = M('placard')->count();
        //分页
        $page = new \Think\Page($countNum,$this->step);
        //查询
        $result = M('business')->field('business_id,status,legal_name,company_name,phone_num,add_time,reward_piao,activate_time')->where('leader = '.$this->business_id())->limit($page->firstRow.','.$this->step)->select();
        //总共获赠共享币数
        $sumRewardPiao = M('business')->where('leader = '.$this->business_id())->sum('reward_piao');
        //规则票数
        $system_config = M('system_config')->select();
        $this->assign('result',$result);
        $this->assign('sumRewardPiao',$sumRewardPiao);
        $this->assign('business_id',$this->business_id());
        $this->assign('reward_piao',$system_config[0]['reward_piao']);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    //商户logo修改
    public function edit_logo()
    {
        if (IS_POST) {
            if (empty($_POST['exchange_name'])) {
                echo json_encode(array('error' => 1,'msg' => '请输入商城名称'));
                exit;
            }
            $_input['path'] = '/Public/business/logo/';
            $_input['size'] = 2048;
            $_input['save_name'] = $this->business_id().'_log';
            if(count($_FILES)){
                $file_path = upload_file($_input);
                sleep(1);
            }else{
                $file_path['result'] = 1;
            }
            $_POST['exchange_name'] = filter_mark($_POST['exchange_name']);
            $is_set = M('business')->where(array('exchange_name' => $_POST['exchange_name'],'business_id' => array('neq',$this->business_id())))->find();
            if ($is_set) {
                echo json_encode(array('error' => 1,'msg' => '系统中已存在该积分商城名称'));
                exit;
            }
            $pic = M('business')->where('business_id='.$this->business_id())->getField('business_logo');
            if (empty($pic) && $file_path['file_path']) {
                $result = M('business')->where('business_id='.$this->business_id())->save(array('business_logo'=>$file_path['file_path'],'exchange_name'=>$_POST['exchange_name']));
            }else{
                $result = M('business')->where('business_id='.$this->business_id())->save(array('exchange_name'=>$_POST['exchange_name']));
            }
            
            if ($file_path['result']==1 && is_int($result)) {
                echo json_encode(array('error' => 0,'msg' => '修改成功'));
                exit;
            }else{
                echo json_encode(array('error' => 2,'msg' => $file_path['msg']));
                exit;
            }
            exit;
        }
        $b_info = M('business')->field('exchange_name,business_logo')->where('business_id='.$this->business_id())->find();
        $now_pic = $b_info['business_logo'];
        if (empty($now_pic)) {
            $now_pic = '/Public/hplus/img/addback-img.jpg';
        }
        $this->assign('exchange_name',$b_info['exchange_name']);
        $this->assign('now_pic',$now_pic);
        $this->display();
    }

    //推荐下线递归
    private function recommend_list_recursion($business_id,$allleaders_res_count,$final_business_id,$where)
    {   
        $allleaders_res = M('business')->field('business_id')->where('leader = '.$business_id.$where)->select();
        $GLOBALS['allleaders_res_count'] += count($allleaders_res);
        foreach ($allleaders_res as $key => $value) {
            if(count($allleaders_res) > 0){
                $GLOBALS['allleaders_res_every'][$final_business_id] += 1;
                $this->recommend_list_recursion($value['business_id'],count($allleaders_res),$final_business_id,$where);
            }
        }
    }

    //我的团队
    public function recommend_list()
    {   
        if(M('business')->where('business_id = '.$this->business_id())->getField('is_dai') != 1){
            die('您不是代理商，无权查看');
        }

        $where = '';
        if ($_GET['status']==1) {
            $where .= ' and status=1';
        }else if($_GET['status']==2){
            $where .= ' and status=0';
        }

        if ($_GET['regs_start_time']) {
            $where .= ' and add_time>="'.date('Y-m-d H:i:s',strtotime($_GET['regs_start_time'])).'"';
        }
        if ($_GET['regs_end_time']) {
            $where .= ' and add_time<="'.date('Y-m-d',strtotime($_GET['regs_end_time'])).' 23:59:59"';
        }
        if ($_GET['activate_start_time']) {
            $where .= ' and first_activate_time>="'.date('Y-m-d H:i:s',strtotime($_GET['activate_start_time'])).'"';
        }
        if ($_GET['activate_end_time']) {
            $where .= ' and first_activate_time<="'.date('Y-m-d',strtotime($_GET['activate_end_time'])).' 23:59:59"';
        }

        $business_id = I('get.business_id');
        $unleaders = M('business')->field('business_id,status,legal_name,company_name,phone_num,first_activate_time,add_time')->where('leader = '.$business_id.$where)->select();
        //间接
        $allleaders_res = M('business')->field('leader')->select();
        foreach ($allleaders_res as $key => $value) {
            $allleaders[] = $value['leader'];
        }
        $everyCount = array_count_values($allleaders);
        foreach ($unleaders as $key => $value) {
            if($everyCount[$value['business_id']] > 0){
                $this->recommend_list_recursion($value['business_id'],0,$value['business_id'],$where);
            }
        }
        $jianjieCount = $GLOBALS['allleaders_res_count'];
        unset($GLOBALS['allleaders_res_count']);
        $jianjieEvery = $GLOBALS['allleaders_res_every'];
        unset($GLOBALS['allleaders_res_every']);
        if(count(explode(' > ',$_GET['recommend'])) <= 1){
            $back_url = '';
        }else{
            $new_recommend = explode(' > ',$_GET['recommend']);
            $new_recommend[0] = $this->business_id();
            array_pop($new_recommend);
            $parameter = '?business_id='.end($new_recommend).'&recommend='.implode(' > ',$new_recommend);
            if (count($new_recommend) <= 1) {
                $parameter .= '&is_start=1';
            }
            $back_url = '/business/center/recommend_list'.$parameter;
        }
        $this->assign('unleaders',$unleaders);
        $this->assign('jianjieCount',$jianjieCount > 0 ? $jianjieCount : 0);
        $this->assign('jianjieEvery',$jianjieEvery);
        $this->assign('recommend',$_GET['recommend']);
        $this->assign('back_url',$back_url);
        $this->display();
    }

}