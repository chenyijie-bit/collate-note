<?php

namespace Exchange\Controller;

class MemberController extends CommonController {
    
    //优先执行
    protected function _initialize()
    {
        //判断是否登录，如果没有，跳转去登录
        if (empty($this->phone_num())) {
            header('location:/exchange/log/in');
            exit;
        }
    }

    //会员中心
    public function index()
    {   
        $is_member = M('member_'.$this->business_id())->find($this->phone_num());
        $user = M('member')->where('phone_num = '.$this->phone_num())->find();
        $business_info = M('business')->field('business_logo,exchange_name')->where('business_id = '.$this->business_id())->find();
        $exchange_name = $business_info['exchange_name'];
        $business_logo = $business_info['business_logo'];
        $xf = M('order')->where(array('status' => 4,'phone_num' => $this->phone_num()))->sum('piao');
        $js = M('order')->where(array('status' => 4,'phone_num' => $this->phone_num()))->sum('num');
        //待提货的订单
        $dth = M('order')->where(array('status' => 1,'phone_num' => $this->phone_num()))->count();
        //待发货的订单
        $dfh = M('order')->where(array('status' => 2,'phone_num' => $this->phone_num()))->count();
        //待收货的订单
        $dsh = M('order')->where(array('status' => 3,'phone_num' => $this->phone_num()))->count();
        if($this->group_id()){
            $this->assign('group_name',M('group')->where('group_id = '.$this->group_id())->getField('group_name'));
        }
        //退换货的订单
        $thh = M('order')->where(array('status' => 6,'phone_num' => $this->phone_num()))->count();
        $this->assign('is_member',$is_member);
        $this->assign('user',$user);
        $this->assign('piao',$user['piao'] > 0 ? $user['piao'] : 0);
        $this->assign('xf',$xf > 0 ? $xf : 0);
        $this->assign('js',$js > 0 ? $js : 0);
        $this->assign('dth',$dth);
        $this->assign('dfh',$dfh);
        $this->assign('dsh',$dsh);
        $this->assign('thh',$thh);
        $this->assign('business_logo',!empty($business_logo) ? $business_logo : '/Public/mobile/images/default_logo.jpg');
        $this->assign('exchange_name',$exchange_name);
        $this->display();
    }

    //我的订单
    public function myOrders()
    {
        //每页显示商品个数
        $page_count = 10;
        $start = I('page')?(I('page')-1)*$page_count:0;

        $status = $_GET['status'];
        $order_str = 'add_time desc';
        if ($status) {
            //$where = array('order.status'=>$status);
            $where = array('status'=>$status);
        }else{
            $where = array();
        }
        if ($status == 6) {
            $where = array('status'=>array(6,7,'or'));
            $order_str = 'reason_time desc';
        }
        //$where['_string'] = 'order.goods_id=goods.goods_id';

        //$where['order.phone_num'] = $this->phone_num();
        $where['phone_num'] = $this->phone_num();
        //$field = 'goods.pics,goods.description,order.order.goods_name,order.order_sn,order.order_id,order.status,order.add_time,order.piao ';
        //$result = M()->table('goods ,order')->field($field)->where($where)->order('order.add_time desc')->limit($start,$page_count)->select();
        $result = M('order')->where($where)->order($order_str)->limit($start,$page_count)->select();
        if ($start > 0) {
            if (count($result) > 0) {
                echo json_encode(array('error'=>0,'data'=>$result));
            }else{
                echo json_encode(array('error'=>1,'msg'=>'没有更多了'));
            }
        }else{
           $this->assign('orders',$result);
            $this->display(); 
        }
    }

    //我的订单详情
    public function myOrder_info()
    {
        if (!$_GET['order_id']) {
            echo '<script>alert("参数错误！");history.back(-1);</script>';
            exit;
        }
        $result = M('order')->where(array('order_id'=>$_GET['order_id'],'phone_num'=>$this->phone_num()))->find();
        if (!$result) {
            echo '<script>alert("参数错误！");history.back(-1);</script>';
            exit;
        }
        //商家信息
        $business_info = M('business')->where(array('business_id'=>$result['business_id']))->find();
        $business_address = $business_info['business_address'];
        $transport = M('transport')->where(array('transport_id'=>$result['transport_id']))->getField('transport_name');
        $this->assign('business_address',$business_address);
        $this->assign('order_info',$result);
        $this->assign('b_phone_num',$business_info['phone_num']);
        $this->assign('b_name',$business_info['legal_name']);
        $this->assign('transport',$transport);
        $this->display();
    }

    //确认沟通
    public function return_result()
    {
        if (IS_POST) {
            $order_id = $_POST['order_id'];
            if (empty($order_id)) {
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            $order_info = M('order')->where(array('order_id'=>$order_id,'phone_num'=>$this->phone_num()))->find();
            if ($order_info['status'] != 6 && $order_info['return_status'] != 1) {
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            if ($order_info['return_result'] == 1) {
                $save_data = array('return_result'=>3);
            }else{
                $save_data = array('return_result'=>2);
            }
            $result = M('order')->where(array('order_id'=>$order_id,'phone_num'=>$this->phone_num()))->save($save_data);
            if ($result) {
                echo json_encode(array('error'=>0,'msg'=>'已确认协商'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'确认沟通失败'));
                exit;
            }
        }
    }

    public function appeal()
    {
        if (IS_POST) {
            $order_id = $_POST['order_id'];
            if (empty($order_id)) {
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            $order_info = M('order')->where(array('order_id'=>$order_id,'phone_num'=>$this->phone_num()))->find();
            if ($order_info['status'] != 6 && $order_info['return_status'] != 2) {
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            $save_data = array('return_result'=>3);
            $result = M('order')->where(array('order_id'=>$order_id,'phone_num'=>$this->phone_num()))->save($save_data);
            if ($result) {
                echo json_encode(array('error'=>0,'msg'=>'已申请客服介入'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'申请客服介入失败'));
                exit;
            }
        }
    }
    //完成订单
    public function order_success()
    {
        if ($_POST['order_id']) {
            $order_info = M('order')->where(array('order_id'=>$_POST['order_id']))->find();
            //开启事物
            //M()->startTrans();
            $result = M('order')->where(array('order_id'=>$_POST['order_id']))->save(array('status'=>4,'success_time'=>date('Y-m-d H:i:s')));
            if (!$result) {
                //M()->rollback();
                echo json_encode(array('error'=>1,'msg'=>'操作失败，请重试！'));
                exit;
            }

           /* $add_piao = M('business')->where(array('business_id'=>$order_info['business_id']))->setInc('piao',$order_info['business_piao']);
            if (!$add_piao) {
                M()->rollback();
                echo json_encode(array('error'=>1,'msg'=>'操作失败，请重试！'));
                exit;
            }

            if ($order_info['sell_business_id'] != 0) {
                $add_piao2 = M('business')->where(array('business_id'=>$order_info['sell_business_id']))->setInc('piao',$order_info['sell_business_piao']);
                if (!$add_piao2) {
                    M()->rollback();
                    echo json_encode(array('error'=>1,'msg'=>'操作失败，请重试！'));
                    exit;
                }
            }
            M()->commit();*/
            echo json_encode(array('error'=>0,'msg'=>'订单已完成！'));
            exit;
        }else{
            echo json_encode(array('error'=>1,'msg'=>'参数错误！'));
            exit;
        }

    }

    //取消订单
    public function cancel_order()
    {
        if ($_POST['order_id']) {
            $order_info = M('order')->where(array('order_id'=>$_POST['order_id']))->find();
            if($order_info['status'] != 2){
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            //开启事物
            M()->startTrans();
            $result = M('order')->where(array('order_id'=>$_POST['order_id']))->setField('status',5);
            if (!$result) {
                M()->rollback();
                echo json_encode(array('error'=>1,'msg'=>'操作失败，请重试'));
                exit;
            }

            $add_piao = M('member')->where(array('phone_num'=>$order_info['phone_num']))->setInc('piao',$order_info['piao']);
            if (!$add_piao) {
                M()->rollback();
                echo json_encode(array('error'=>1,'msg'=>'操作失败，请重试'));
                exit;
            }

            M()->commit();
            echo json_encode(array('error'=>0,'msg'=>'订单已取消'));
            exit;
        }else{
            echo json_encode(array('error'=>1,'msg'=>'参数错误'));
            exit;
        }

    }

    //退货页面
    public function return_goods()
    {
        if (IS_POST) {
            if (empty($_POST['order_id']) || empty($_POST['reason'])) {
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            if ($_POST['reason'] == 3 && empty($_POST['content'])) {
                echo json_encode(array('error'=>1,'msg'=>'请输入您的退换原因'));
                exit;
            }
            $order_info = M('order')->where(array('order_id'=>$_POST['order_id'],'phone_num'=>$this->phone_num()))->find();
            if (!($order_info['status'] == 3 || ($order_info['status'] == 4 && $order_info['success_time'] >= date('Y-m-d H:i:s',(time()-3600*48))))) {
                echo json_encode(array('error'=>1,'msg'=>'非法操作'));
                exit;
            }
            $contents = array('1'=>'未收到商品','2'=>'商品质量问题','3'=>$_POST['content']);
            $save_data = array(
                    'status' => 6,
                    'reason_id' => $_POST['reason'],
                    'reason_content' => $contents[$_POST['reason']],
                    'reason_time' => date('Y-m-d H:i:s')
                );
            $result = M('order')->where(array('order_id'=>$_POST['order_id'],'phone_num'=>$this->phone_num()))->save($save_data);
            $notice = array(
                    'title' => '买家退换通知',
                    'content' => '订单号为 “'.$order_info['order_sn'].'” 的订单买家发起了退换申请，请在 “订单管理->退货订单” 中查看，若您48小时未受理或拒绝，系统将会全额返还共享币给买家。',
                    'business_id' => $order_info['business_id'],
                    'is_read' => 0,
                    'add_time' => date('Y-m-d H:i:s')
                );

            if ($result) {
                M('notices')->add($notice);
                echo json_encode(array('error'=>0,'msg'=>'已提交申请'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'提交申请失败'));
                exit;
            }
        }else{
            $order_id = $_GET['order_id'];
            if (empty($order_id)) {
                $order_id = 0;
            }
            //订单信息
            $order_info = M('order')->where(array('order_id'=>$order_id,'phone_num'=>$this->phone_num()))->find();
            //
            $this->assign('order_info',$order_info);
            $this->display();
        }
        
    }

    //共享币记录
    public function myPiao()
    {   
        $in_res = M()->query('select dispense.*,business.exchange_name from dispense left join business on dispense.business_id = business.business_id where dispense.phone_num = '.$this->phone_num());
        $out_res = M('order')->field('business_id,sell_business_id,num,piao,goods_name,add_time')->where(array('phone_num' => $this->phone_num(),'status' => 4))->select();
        foreach ($out_res as $key => $value) {
            if($value['sell_business_id'] > '0'){
                $out_res[$key]['exchange_name'] = M('business')->where('business_id = '.$value['sell_business_id'])->getField('exchange_name');
            }else{
                $out_res[$key]['exchange_name'] = M('business')->where('business_id = '.$value['business_id'])->getField('exchange_name');
            }
        }
        $result = array_merge($out_res,$in_res);
        sortArrByField($result,'add_time',true);
        $this->assign('result',$result);
        $this->display();
    }

    //修改密码
    public function rePassword()
    {
        if (IS_POST) {
            if (!$_POST['old_password']) {
                echo json_encode(array('error'=>1,'msg'=>'请输入原密码'));
                exit;
            }
            if (!$_POST['password']) {
                echo json_encode(array('error'=>1,'msg'=>'请输入新密码'));
                exit;
            }
            if ($_POST['password'] != $_POST['repassword']) {
                echo json_encode(array('error'=>1,'msg'=>'两次新密码不一致'));
                exit;
            }
            if (strlen($_POST['password']) < 6) {
                echo json_encode(array('error'=>1,'msg'=>'密码长度要大于6位'));
                exit;
            }
            $old_password = M('member')->where(array('phone_num'=>$this->phone_num()))->getField('password');
            if ($old_password !=  md5($_POST['old_password'])) {
                echo json_encode(array('error'=>1,'msg'=>'原密码输入错误'));
                exit; 
            }
            if (md5($_POST['password']) == $old_password) {
                echo json_encode(array('error'=>1,'msg'=>'新密码与原密码相同'));
                exit;
            }
            $result = M('member')->where(array('phone_num'=>$this->phone_num()))->setField('password',md5($_POST['repassword']));
            if ($result) {
                $business_id = $_SESSION['exchange']['business_id'];
                $phone_num = $this->phone_num();
                unset($_SESSION['exchange']);
                echo json_encode(array('error'=>0,'phone_num'=>$phone_num,'business_id'=>$business_id));
                exit; 
            }else{
                echo json_encode(array('error'=>1,'msg'=>'密码修改失败'));
                exit; 
            }
        }
        $this->display();
    }

    //收货地址编辑
    public function address_edit()
    {   
        if(IS_GET){
            $phone_num = $this->phone_num();
            $info = M('member')->field('area_id,city_id,province_id,phone_num,address,name')->where('phone_num = '.$phone_num)->find();
            $province = M('province')->select();
            $city = !empty($info['province_id']) ? M('city')->where('province_id = '.$info['province_id'])->select() : "";
            $area = !empty($info['city_id']) ? M('area')->where('city_id = '.$info['city_id'])->select() : "";
            $this->assign('info',$info);
            $this->assign('area',$area);
            $this->assign('city',$city);
            $this->assign('province',$province);
            $this->display();
        }else{
            $data = I('post.');
            if($data['type'] == 'SAVE'){
                parse_str($data['form'],$data);
                foreach ($data as $key => $value) {
                    if(strpos($key,';')){
                        $data[end(explode(';',$key))] = $value;
                        unset($data[$key]);
                    }
                }
                $affected_rows = M('member')->where('phone_num = '.$data['phone_num'])->save($data);
                if(is_int($affected_rows)){
                    echo json_encode(array('error' => 0,'msg' => '保存成功'));
                    exit;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '保存失败'));
                    exit;
                }
            }elseif($data['type'] == 'OPTION'){
                if($data['select_type'] == 'province_id'){
                    $city = M('city')->where('province_id = '.$data['select_val'])->select();
                    echo json_encode(array('error' => 0,'result' => $city));
                    exit;
                }
                if($data['select_type'] == 'city_id'){
                    $area = M('area')->where('city_id = '.$data['select_val'])->select();
                    echo json_encode(array('error' => 0,'result' => $area));
                    exit;
                }
            }
        }
    }

    //添加评价
    public function add_evaluate()
    {
        if (IS_POST) {
            if (empty($_POST['order_id'])) {
                echo json_encode(array('error' => 1,'msg' => '参数错误'));
                exit;
            }

            if (empty($_POST['level'])) {
                echo json_encode(array('error' => 1,'msg' => '请选择评价等级'));
                exit;
            }

            if (empty($_POST['content'])) {
                echo json_encode(array('error' => 1,'msg' => '请输入评价内容'));
                exit;
            }
            $order_info = M('order')->where(array('order_id'=>$_POST['order_id'],'phone_num'=>$this->phone_num()))->find();
            if (!$order_info || $order_info['status'] != 4 || $order_info['is_eval'] != 0) {
                echo json_encode(array('error' => 1,'msg' => '非法操作'));
                exit;
            }
            //开启事物
            M()->startTrans();
            $add_data = array(
                    'level' => $_POST['level'],
                    'content' => $_POST['content'],
                    'order_id' => $order_info['order_id'],
                    'goods_id' => $order_info['goods_id'],
                    'phone_num'  => $this->phone_num()
                );
            $add_eval = M('eval')->add($add_data);
            if (!$add_eval) {
                M()->rollback();
                echo json_encode(array('error' => 1,'msg' => '订单评价失败1'));
                exit;
            }
            $result = M('order')->where(array('order_id'=>$_POST['order_id'],'phone_num'=>$this->phone_num()))->setField('is_eval',1); 
            if (!$result) {
                M()->rollback();
                echo json_encode(array('error' => 1,'msg' => '订单评价失败2'));
                exit;
            }else{
                M()->commit();
                echo json_encode(array('error' => 0,'msg' => '订单评价成功'));
                exit;  
            }
            

        }
        $this->display();
    }

    //评价列表
    public function evaluate_list()
    {
        //每页个数
        $page_count = 10;
        //页码
        $page = I('page')?I('page'):1;
        //起始
        $start = ($page-1) * $page_count;
        $goods_id = I('get.goods_id');
        if(!$goods_id){
            header('location:/exchange/?b='.$_SESSION['exchange']['business_id']);
            exit;
        }

        //总数
        $count = M('eval')->where(array('goods_id'=>$goods_id))->count();

        $evals = M()->table('eval , member')->field('eval.*,member.name')->where(array('eval.goods_id'=>$goods_id,'_string'=>'eval.phone_num=member.phone_num'))->order('add_time desc')->limit($start,$page_count)->select();

        if ($start > 0) {
            if (empty($evals)) {
                echo json_encode(array('error' => 1,'msg' => '没有更多了'));
                exit;
            }else{
                echo json_encode(array('error' => 0,'result' => $evals));
                exit;
            }
        }else{
            $this->assign('evals',$evals);
            $this->assign('page_count',$page_count);
            $this->display();
        }
        
    }

    //修改支付密码
    public function edit_pay()
    {
        if (IS_POST) {
            $phone_num = $this->phone_num();
            if (empty($_POST['phone_code'])) {
                echo json_encode(array('error'=>1,'msg'=>'请填写手机验证码'));
                exit;
            }
            if (empty($_POST['pay_pass']) || strlen($_POST['pay_pass']) != 6) {
                echo json_encode(array('error'=>1,'msg'=>'请填写6位支付密码'));
                exit;
            }
            if ($_POST['pay_pass'] != $_POST['repay_pass']) {
                echo json_encode(array('error'=>1,'msg'=>'两次密码不一致'));
                exit;
            }
            if ($_POST['phone_code'] != $_SESSION['pay_pass'][$phone_num]['sms_code']) {
                echo json_encode(array('error'=>1,'msg'=>'手机验证码错误'));
                exit;
            }
            $_SESSION['pay_pass'][$phone_num]['sms_time'] = time()+100;
            if (time() > $_SESSION['pay_pass'][$phone_num]['sms_time']) {
                echo json_encode(array('error'=>1,'msg'=>'手机验证码已过期'));
                exit;
            }
            $result = M('member')->where(array('phone_num'=>$phone_num))->setField('pay_pass',md5($_POST['pay_pass']));
            if (is_int($result)) {
                unset($_SESSION['pay_pass'][$phone_num]);
                echo json_encode(array('error'=>0,'msg'=>'修改密码成功'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'修改密码失败'));
                exit;
            }
        }
        $this->display();
    }
    
}