<?php

namespace Exchange\Controller;

class GoodsController extends CommonController {

    //商品列表
    public function lists()
    {   
        if($this->group_id()){
            header('location:/exchange/goods/group?group_id='.$this->group_id());
            exit;
        }
        $type = I('get.type');
        //每页显示商品个数
        $page_count = 10;
        $start = I('page')?(I('page')-1)*$page_count:0;
        //排序
        switch (I('order')) {
            case 'sa':
                $order = 'g.sales_volume desc';
                $orders = 'sales_volume desc';
                break;
            case 'sd':
                $order = 'g.sales_volume asc';
                $orders = 'sales_volume asc';
                break;
            case 'pa':
                $order = 'g.piao desc';
                $orders = 'piao desc';
                break;
            case 'pd':
                $order = 'g.piao asc';
                $orders = 'piao asc';
                break;
            
            default:
                $order = 'g.sales_volume desc';
                $orders = 'sales_volume desc';
                break;
        }
        //自营
        if(empty($type)){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id()))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == '1_2000'){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id(),'piao'=>array('elt',2000)))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == '2001_5000'){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id(),'piao'=>array(array('gt',2000),array('elt',5000),'and')))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == '5000up'){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id(),'piao'=>array('gt',5000)))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == 'myagent'){
            $goods = M()->table('agent a,goods g')->field('a.goods_id,g.pics,g.goods_name,g.piao,g.sales_volume')->where(array('a.business_id' => $this->business_id(),'_string'=>'a.goods_id=g.goods_id','g.is_del' => 0,'g.status' => 1))->order($order)->limit($start,$page_count)->select();
        }else{
            header('location:/exchange/?b='.$_SESSION['exchange']['business_id']);
            exit;
        }
        if ($start > 0) {
            if (empty($goods)) {
                echo json_encode(array('error' => 1,'msg' => '没有更多了'));
                exit;
            }else{
                echo json_encode(array('error' => 0,'data' => $goods));
                exit;
            }
        }else{
            $this->assign('goods',$goods);
            $this->display();
        }
    }

    //分组
    public function group()
    {   
        $type = I('get.type');
        if(!$this->group_id()){
            header('location:/exchange/?b='.$_SESSION['exchange']['business_id']);
            exit;
        }
        $group_id = $this->group_id();
        $theGroup = M('group')->where('group_id = '.$group_id)->find();
        if($theGroup['is_del'] == 2){
            echo "该分组已被删除，无法显示内容";
            exit;
        }
        $goods_ids_arr = M('group_goods')->field('goods_id')->where('group_id = '.$group_id)->select();
        $goods_ids = '';
        foreach ($goods_ids_arr as $key => $value) {
            $goods_ids[] = $value['goods_id'];
        }
        //每页显示商品个数
        $page_count = 10;
        $start = I('page')?(I('page')-1)*$page_count:0;
        //排序
        switch (I('order')) {
            case 'sa':
                $order = 'g.sales_volume desc';
                $orders = 'sales_volume desc';
                break;
            case 'sd':
                $order = 'g.sales_volume asc';
                $orders = 'sales_volume asc';
                break;
            case 'pa':
                $order = 'g.piao desc';
                $orders = 'piao desc';
                break;
            case 'pd':
                $order = 'g.piao asc';
                $orders = 'piao asc';
                break;
            
            default:
                $order = 'g.sales_volume desc';
                $orders = 'sales_volume desc';
                break;
        }
        //自营
        if(empty($type)){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id(),'goods_id' => array('in',$goods_ids)))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == '1_2000'){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id(),'piao'=>array('elt',2000),'goods_id' => array('in',$goods_ids)))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == '2001_5000'){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id(),'piao'=>array(array('gt',2000),array('elt',5000),'and'),'goods_id' => array('in',$goods_ids)))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == '5000up'){
            $goods = M('goods')->field('goods_id,pics,goods_name,piao,sales_volume')->where(array('is_del' => 0,'status' => 1,'business_id' => $this->business_id(),'piao'=>array('gt',5000),'goods_id' => array('in',$goods_ids)))->order($orders)->limit($start,$page_count)->select();
        }elseif($type == 'myagent'){
            $goods = M()->table('agent a,goods g')->field('a.goods_id,g.pics,g.goods_name,g.piao,g.sales_volume')->where(array('a.business_id' => $this->business_id(),'_string'=>'a.goods_id=g.goods_id','g.is_del' => 0,'g.status' => 1,'g.goods_id' => array('in',$goods_ids)))->order($order)->limit($start,$page_count)->select();
        }else{
            header('location:/exchange/?b='.$_SESSION['exchange']['business_id']);
            exit;
        }
        if ($start > 0) {
            if (empty($goods)) {
                echo json_encode(array('error' => 1,'msg' => '没有更多了'));
                exit;
            }else{
                echo json_encode(array('error' => 0,'data' => $goods));
                exit;
            }
        }else{
            $this->assign('goods',$goods);
            $this->assign('theGroup',$theGroup);
            $this->display();
        }
    }

    //商品详情
    public function detail()
    {   
        $goods_id = I('get.goods_id');
        if(!$goods_id){
            header('location:/exchange/?b='.$_SESSION['exchange']['business_id']);
            exit;
        }
        $info = M('goods')->find($goods_id);
        if($info['is_del'] == 1){
            header('location:/exchange/?b='.$_SESSION['exchange']['business_id']);
            exit;
        }
        $evals = M()->table('eval , member')->field('eval.*,member.name')->where(array('eval.goods_id'=>$goods_id,'_string'=>'eval.phone_num=member.phone_num'))->order('level desc')->limit(0,3)->select();
        $this->assign('evals',$evals);
        $this->assign('info',$info);
        $this->display();
    }

    //提交订单
    public function order()
    {   
        if(IS_GET){
            $user = M('member')->find($this->phone_num());

            if($user['province_id'] && $user['city_id'] && $user['area_id']){
                $user['province_name'] = M('province')->where('province_id = '.$user['province_id'])->getField('province_name');
                $user['city_name'] = M('city')->where('city_id = '.$user['city_id'])->getField('city_name');
                $user['area_name'] = M('area')->where('area_id = '.$user['area_id'])->getField('area_name');
            }

            $goods_id = I('get.goods_id');
            
            if(!$goods_id){
                header('location:/exchange/?b='.$_SESSION['exchange']['business_id']);
                exit;
            }

            $goods = M('goods')->field('goods_id,goods_name,pics,piao')->find($goods_id);

            $temp = M('business')->field('province_id,city_id,area_id,business_address')->find($this->business_id());
            $business['province_name'] = M('province')->where('province_id = '.$temp['province_id'])->getField('province_name');
            $business['city_name'] = M('city')->where('city_id = '.$temp['city_id'])->getField('city_name');
            $business['area_name'] = M('area')->where('area_id = '.$temp['area_id'])->getField('area_name');
            $business['business_address'] = $temp['business_address'];

            $this->assign('user',$user);
            $this->assign('goods',$goods);
            $this->assign('business',$business);
            $this->display();
        }else{
            $data = I('post.');

            $user_info = M('member')->find($this->phone_num());
            if (empty($_POST['passWordCon'])) {
                echo json_encode(array('error' => 1,'msg' => '没有密码'));
                exit;
            }

            if($user_info['pay_pass'] != md5($_POST['passWordCon'])){
                echo json_encode(array('error' => 1,'msg' => '密码输入错误'));
                exit;
            }
        

            if(count($data) < 4){
                echo json_encode(array('error' => 1,'msg' => '缺少参数'));
                exit;
            }
            if(!is_numeric($data['goods_id'])){
                echo json_encode(array('error' => 1,'msg' => '商品ID不可为空'));
                exit;
            }
            if($data['address'] == ''){
                echo json_encode(array('error' => 1,'msg' => '收货地址未补全'));
                exit;
            }

            $theMemberPiao = M('member')->where('phone_num = '.$this->phone_num())->getField('piao');
            $theGoods = M('goods')->find($data['goods_id']);

            if($theGoods['nums'] <= 0){
                echo json_encode(array('error' => 1,'msg' => '商品库存不足'));
                exit;
            }
            if($theMemberPiao < $theGoods['piao']){
                echo json_encode(array('error' => 1,'msg' => '您的共享币不足'));
                exit;
            }

            $data['business_id'] = $theGoods['business_id'];
            $data['goods_name'] = $theGoods['goods_name'];
            $data['phone_num'] = $this->phone_num();
            $data['num'] = 1;
            $data['piao'] = $theGoods['piao'];
            $data['rebate'] = $theGoods['rebate'];
            $data['business_piao'] = $theGoods['business_id'] == $this->business_id() ? $theGoods['piao'] : floor($theGoods['piao'] * (1 - $theGoods['rebate']));
            $data['sell_business_piao'] = $theGoods['business_id'] == $this->business_id() ? 0 : floor($theGoods['piao'] * $theGoods['rebate']);
            $data['order_sn'] = 'SN'.getOrderSN();
            $data['sell_business_id'] = $theGoods['business_id'] == $this->business_id() ? 0 : $this->business_id();
            $data['status'] = $data['is_ziti'] == 1 ? 1 : 2;
            $data['add_time'] = date('Y-m-d H:i:s');
            $data['pic'] = explode(';',$theGoods['pics'])[0];
            $data['group_id'] = !empty($this->group_id()) ? $this->group_id() : null;
            
            M('order')->startTrans();
            M('member')->startTrans();
            M('goods')->startTrans();

            $affected_rows = M('order')->add($data);
            if(is_numeric($affected_rows)){
                $piaoDeced = M('member')->where('phone_num = '.$this->phone_num())->setDec('piao',$theGoods['piao']);
                $NumsDeced = M('goods')->where('goods_id = '.$theGoods['goods_id'])->setDec('nums',1);
                if(is_numeric($piaoDeced) && is_numeric($NumsDeced)){
                    M('order')->commit();
                    M('member')->commit();
                    M('goods')->commit();
                    //发送短信通知商户处理订单
                    $phone_num = M('business')->where(array('business_id'=>$_SESSION['exchange']['business_id']))->getField('phone_num');
                    $sms_content = '您有1笔新的订单，请及时处理 '.PHP_EOL.date('Y-m-d H:i:s');
                    $is_send = sendSMS($phone_num,0,$sms_content);
                    echo json_encode(array('error' => 0,'msg' => '兑换成功'));
                    exit;
                }
            }

            M('order')->rollback();
            M('member')->rollback();
            M('goods')->rollback();
            echo json_encode(array('error' => 1,'msg' => '兑换失败'));
            exit;
        }
    }

}