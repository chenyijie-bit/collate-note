<?php
namespace Business\Controller;

class FinanceController extends CommonController {
    
    //销售订单
    public function orders()
    {
        $where['_string'] = '(business_id='.$this->business_id().' OR sell_business_id='.$this->business_id().')';
        //统计总订单数
        $count = M('order')->where($where)->count();
        if ($_GET['start_time']) {
            $where['add_time'] = array('egt',$_GET['start_time']);
        }
        if ($_GET['end_time']) {
            $where['add_time'] = array('elt',date('Y-m-d',(strtotime($_GET['end_time'])+24*3600)));
        }
        if ($_GET['start_time'] && $_GET['end_time']) {
            $where['add_time'] = array(array('egt',$_GET['start_time']),array('elt',date('Y-m-d',(strtotime($_GET['end_time'])+24*3600))),'and');
        }
        if ($_GET['order_sn']) {
            $where['order_sn'] = $_GET['order_sn'];
        }
        if ($_GET['keywords']) {
            $where['goods_name'] = array('like','%'.$_GET['keywords'].'%');
        }
        //统计条件订单数
        $where_count = M('order')->where($where)->count(); 
        //分页
        $page = new \Think\Page($where_count,$this->step);
        $result = M('order')->where($where)->limit($page->firstRow,$this->step)->order('add_time desc')->select();
        //快递
        $transports = M('transport')->where('transport_id > 0')->select();

        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('where_count',$where_count);
        $this->assign('result',$result);
        $this->assign('business_id',$this->business_id());
        $this->assign('transports',$transports);
        $this->display();
    }

    public function return_orders()
    {
        $where['_string'] = '(business_id='.$this->business_id().' OR sell_business_id='.$this->business_id().')';
        $where['status'] = 6;
        //统计总订单数
        $count = M('order')->where($where)->count();
        if ($_GET['start_time']) {
            $where['add_time'] = array('egt',$_GET['start_time']);
        }
        if ($_GET['end_time']) {
            $where['add_time'] = array('elt',date('Y-m-d',(strtotime($_GET['end_time'])+24*3600)));
        }
        if ($_GET['start_time'] && $_GET['end_time']) {
            $where['add_time'] = array(array('egt',$_GET['start_time']),array('elt',date('Y-m-d',(strtotime($_GET['end_time'])+24*3600))),'and');
        }
        if ($_GET['order_sn']) {
            $where['order_sn'] = $_GET['order_sn'];
        }
        if ($_GET['keywords']) {
            $where['goods_name'] = array('like','%'.$_GET['keywords'].'%');
        }
        //统计条件订单数
        $where_count = M('order')->where($where)->count(); 
        //分页
        $page = new \Think\Page($where_count,$this->step);
        $result = M('order')->where($where)->limit($page->firstRow,$this->step)->order('add_time desc')->select();
        //快递
        $transports = M('transport')->where('transport_id > 0')->select();

        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('where_count',$where_count);
        $this->assign('result',$result);
        $this->assign('business_id',$this->business_id());
        $this->assign('transports',$transports);
        $this->display();
    }

    //退换详情
    public function return_detail()
    {
        $order_id = $_GET['order_id'];
        if (empty($order_id)) {
            echo '<script>alert("参数错误！");history.go(-1);</script>';
            exit;
        }
        $order_info = M('order')->where(array('order_id'=>$order_id))->find();
        $order_info['c_name'] = M('member')->where(array('phone_num'=>$order_info['phone_num']))->getField('name');
        $transport = M('transport')->select();
        foreach ($transport as $key => $value) {
            $transports[$value['transport_id']] = $value['transport_name'];
        }
        $this->assign('transports',$transports);
        $this->assign('order_info',$order_info);
        $this->display();
    }

    //处理状态
    public function return_status()
    {
        if (IS_POST) {
            if (!$_POST['order_id']) {
                echo json_encode(array('error' => 1,'msg' => '参数错误'));
                exit;
            }
            if ($_POST['status'] == 2 && !$_POST['reason_content']) {
                echo json_encode(array('error' => 1,'msg' => '请输入拒绝理由'));
                exit;
            }
            if ($_POST['status'] == 3) {
                $order_info = M('order')->where(array('order_id'=>$_POST['order_id']))->find();
                if ($order_info['return_status'] != 1) {
                    echo json_encode(array('error' => 1,'msg' => '非法操作'));
                    exit;
                }elseif($order_info['return_result'] != 2){
                    echo json_encode(array('error' => 1,'msg' => '买家未同意协商，尽快联系买家'));
                    exit;
                }
                $result = M('order')->where(array('order_id'=>$_POST['order_id']))->save(array('return_result'=>3));
                if ($result) {
                    echo json_encode(array('error' => 0,'msg' => '已通知客服'));
                    exit;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '通知客服失败'));
                    exit;
                }
            }else{
                $result = M('order')->where(array('order_id'=>$_POST['order_id']))->save(array('return_status'=>$_POST['status'],'reason_content'=>$_POST['reason_content']));
                if ($result) {
                    echo json_encode(array('error' => 0,'msg' => '操作成功'));
                    exit;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '操作失败'));
                    exit;
                }
            }
        }
    }

    //销售订单详情
    public function orders_detail()
    {
        $order_id = $_GET['order_id'];
        if (empty($order_id)) {
            echo '<script>alert("参数错误！");history.go(-1);</script>';
            exit;
        }
        $order_info = M('order')->where(array('order_id'=>$order_id))->find();
        $transport = M('transport')->select();
        foreach ($transport as $key => $value) {
            $transports[$value['transport_id']] = $value['transport_name'];
        }
        $this->assign('transports',$transports);
        $this->assign('order_info',$order_info);
        $this->display();
    }

    //添加订单的运单号
    public function add_waybill()
    {
        if (IS_POST) {
            if ($_POST['me_give'] == 2) {
                $_POST['waybill'] = '#';
                $_POST['transport_id'] = '0';
            }
            if (empty($_POST['order_id']) || empty($_POST['waybill'])) {
                echo json_encode(array('error' => 1,'msg' => '参数错误！'));
                exit;
            }
            $result = M('order')->where(array('order_id'=>$_POST['order_id']))->save(array('waybill'=>$_POST['waybill'],'status'=>3,'transport_id'=>$_POST['transport_id']));
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '发货成功！'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '发货失败！'));
                exit;
            }
        }
    }

    //帮客户确认收货
    public function geted()
    {
        if (IS_POST) {
            if (empty($_POST['order_id'])) {
                echo json_encode(array('error' => 1,'msg' => '参数错误！'));
                exit;
            }
            $result = M('order')->where(array('order_id'=>$_POST['order_id']))->save(array('status'=>4,'success_time'=>date('Y-m-d H:i:s')));
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '订单已完成！'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '订单状态修改失败！'));
                exit;
            }
        }
    }

    //销售统计(年)
    public function orders_count_year()
    {
        $year = date('Y');
        if ($_GET['year']) {
            $year = $_GET['year'];
        }
        //本店商品数
        $self_goods = M('goods')->where('business_id='.$this->business_id())->count();
        //本店代理商品数
        $sell_goods = M('agent')->where('business_id='.$this->business_id())->count();
        //实体店销售统计
        $shop_count = M()->query('select month(add_time) as month,sum(pay_num) as sum from dispense where year(add_time)='.$year.' and business_id='.$this->business_id().' group by month(add_time);');
        foreach ($shop_count as $key => $value) {
            $new_shop_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_shop_c[$i] = empty($new_shop_count[$i])?0:$new_shop_count[$i];
        }
        $json_shop_count = implode(',',$new_shop_c);
        //兑换共享币统计
        $piao_count = M()->query('select month(add_time) as month,sum(add_piao) as sum from dispense where year(add_time)='.$year.' and business_id='.$this->business_id().' group by month(add_time);');
        foreach ($piao_count as $key => $value) {
            $new_piao_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_piao_c[$i] = empty($new_piao_count[$i])?0:$new_piao_count[$i];
        }
        $json_piao_count = implode(',',$new_piao_c);
        //本店商品销售数量统计
        $myNum_count = M()->query('select month(add_time) as month,sum(num) as sum from `order` where year(add_time)='.$year.' and business_id='.$this->business_id().' and sell_business_id=0 and status=4 group by month(add_time);');
        foreach ($myNum_count as $key => $value) {
            $new_myNum_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_myNum_c[$i] = empty($new_myNum_count[$i])?0:$new_myNum_count[$i];
        }
        $json_myNum_count = implode(',',$new_myNum_c);
        //本店商品代理销售数量统计
        $sellNum_count = M()->query('select month(add_time) as month,sum(num) as sum from `order` where year(add_time)='.$year.' and business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by month(add_time);');
        foreach ($sellNum_count as $key => $value) {
            $new_sellNum_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_sellNum_c[$i] = empty($new_sellNum_count[$i])?0:$new_sellNum_count[$i];
        }
        $json_sellNum_count = implode(',',$new_sellNum_c);
        //本店代理商品销售数量统计
        $agentNum_count = M()->query('select month(add_time) as month,sum(num) as sum from `order` where year(add_time)='.$year.' and sell_business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by month(add_time);');
        foreach ($agentNum_count as $key => $value) {
            $new_agentNum_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_agentNum_c[$i] = empty($new_agentNum_count[$i])?0:$new_agentNum_count[$i];
        }
        $json_agentNum_count = implode(',',$new_agentNum_c);
        //本店商品销售共享币统计
        $myPiao_count = M()->query('select month(add_time) as month,sum(piao) as sum from `order` where year(add_time)='.$year.' and business_id='.$this->business_id().' and sell_business_id=0 and status=4 group by month(add_time);');
        foreach ($myPiao_count as $key => $value) {
            $new_myPiao_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_myPiao_c[$i] = empty($new_myPiao_count[$i])?0:$new_myPiao_count[$i];
        }
        $json_myPiao_count = implode(',',$new_myPiao_c);
        //本店商品代理销售共享币统计
        $sellPiao_count = M()->query('select month(add_time) as month,sum(business_piao) as sum from `order` where year(add_time)='.$year.' and business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by month(add_time);');
        foreach ($sellPiao_count as $key => $value) {
            $new_sellPiao_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_sellPiao_c[$i] = empty($new_sellPiao_count[$i])?0:$new_sellPiao_count[$i];
        }
        $json_sellPiao_count = implode(',',$new_sellPiao_c);
        //本店代理商品销售共享币统计
        $agentPiao_count = M()->query('select month(add_time) as month,sum(sell_business_piao) as sum from `order` where year(add_time)='.$year.' and sell_business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by month(add_time);');
        foreach ($agentPiao_count as $key => $value) {
            $new_agentPiao_count[$value['month']] = $value['sum'];
        }
        for ($i=1; $i <= date('m'); $i++) { 
            $new_agentPiao_c[$i] = empty($new_agentPiao_count[$i])?0:$new_agentPiao_count[$i];
        }
        $json_agentPiao_count = implode(',',$new_agentPiao_c);
        
        $this->assign('self_goods',$self_goods);
        $this->assign('sell_goods',$sell_goods);
        $this->assign('json_shop_count',$json_shop_count);
        $this->assign('json_piao_count',$json_piao_count);
        $this->assign('json_myNum_count',$json_myNum_count);
        $this->assign('json_sellNum_count',$json_sellNum_count);
        $this->assign('json_agentNum_count',$json_agentNum_count);
        $this->assign('json_myPiao_count',$json_myPiao_count);
        $this->assign('json_sellPiao_count',$json_sellPiao_count);
        $this->assign('json_agentPiao_count',$json_agentPiao_count);
        $this->display();
    }

    //销售统计(月)
    public function orders_count_month()
    {
        $year = date('Y');
        $month = date('m');
        if ($_GET['year']) {
            $year = explode('-',$_GET['year'])[0];
            $month = explode('-',$_GET['year'])[1];
        }
        //本店商品数
        $self_goods = M('goods')->where('business_id='.$this->business_id())->count();
        //本店代理商品数
        $sell_goods = M('agent')->where('business_id='.$this->business_id())->count();
        //实体店销售统计
        $shop_count = M()->query('select day(add_time) as day,sum(pay_num) as sum from dispense where year(add_time)='.$year.' and month(add_time)='.$month.' and business_id='.$this->business_id().' group by day(add_time);');
        foreach ($shop_count as $key => $value) {
            $new_shop_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_shop_c[$i] = empty($new_shop_count[$i])?0:$new_shop_count[$i];
        }
        $json_shop_count = implode(',',$new_shop_c);
        //兑换共享币统计
        $piao_count = M()->query('select day(add_time) as day,sum(add_piao) as sum from dispense where year(add_time)='.$year.' and month(add_time)='.$month.' and business_id='.$this->business_id().' group by day(add_time);');
        foreach ($piao_count as $key => $value) {
            $new_piao_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_piao_c[$i] = empty($new_piao_count[$i])?0:$new_piao_count[$i];
        }
        $json_piao_count = implode(',',$new_piao_c);
        //本店商品销售数量统计
        $myNum_count = M()->query('select day(add_time) as day,sum(num) as sum from `order` where year(add_time)='.$year.' and month(add_time)='.$month.' and business_id='.$this->business_id().' and sell_business_id=0 and status=4 group by day(add_time);');
        foreach ($myNum_count as $key => $value) {
            $new_myNum_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_myNum_c[$i] = empty($new_myNum_count[$i])?0:$new_myNum_count[$i];
        }
        $json_myNum_count = implode(',',$new_myNum_c);
        //本店商品代理销售数量统计
        $sellNum_count = M()->query('select day(add_time) as day,sum(num) as sum from `order` where year(add_time)='.$year.' and month(add_time)='.$month.' and business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by day(add_time);');
        foreach ($sellNum_count as $key => $value) {
            $new_sellNum_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_sellNum_c[$i] = empty($new_sellNum_count[$i])?0:$new_sellNum_count[$i];
        }
        $json_sellNum_count = implode(',',$new_sellNum_c);
        //本店代理商品销售数量统计
        $agentNum_count = M()->query('select day(add_time) as day,sum(num) as sum from `order` where year(add_time)='.$year.' and month(add_time)='.$month.' and sell_business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by day(add_time);');
        foreach ($agentNum_count as $key => $value) {
            $new_agentNum_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_agentNum_c[$i] = empty($new_agentNum_count[$i])?0:$new_agentNum_count[$i];
        }
        $json_agentNum_count = implode(',',$new_agentNum_c);
        //本店商品销售共享币统计
        $myPiao_count = M()->query('select day(add_time) as day,sum(piao) as sum from `order` where year(add_time)='.$year.' and month(add_time)='.$month.' and business_id='.$this->business_id().' and sell_business_id=0 and status=4 group by day(add_time);');
        foreach ($myPiao_count as $key => $value) {
            $new_myPiao_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_myPiao_c[$i] = empty($new_myPiao_count[$i])?0:$new_myPiao_count[$i];
        }
        $json_myPiao_count = implode(',',$new_myPiao_c);
        //本店商品代理销售共享币统计
        $sellPiao_count = M()->query('select day(add_time) as day,sum(business_piao) as sum from `order` where year(add_time)='.$year.' and month(add_time)='.$month.' and business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by day(add_time);');
        foreach ($sellPiao_count as $key => $value) {
            $new_sellPiao_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_sellPiao_c[$i] = empty($new_sellPiao_count[$i])?0:$new_sellPiao_count[$i];
        }
        $json_sellPiao_count = implode(',',$new_sellPiao_c);
        //本店代理商品销售共享币统计
        $agentPiao_count = M()->query('select day(add_time) as day,sum(sell_business_piao) as sum from `order` where year(add_time)='.$year.' and month(add_time)='.$month.' and sell_business_id='.$this->business_id().' and sell_business_id!=0 and status=4 group by day(add_time);');
        foreach ($agentPiao_count as $key => $value) {
            $new_agentPiao_count[$value['day']] = $value['sum'];
        }
        for ($i=1; $i <= date('d'); $i++) { 
            $new_agentPiao_c[$i] = empty($new_agentPiao_count[$i])?0:$new_agentPiao_count[$i];
        }
        $json_agentPiao_count = implode(',',$new_agentPiao_c);
        //查询月的天数
        for ($i=1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++) { 
            $month_day[] = '"'.$i.'日"';
        }
        $month_day = implode(',',$month_day);

        $this->assign('self_goods',$self_goods);
        $this->assign('sell_goods',$sell_goods);
        $this->assign('json_shop_count',$json_shop_count);
        $this->assign('json_piao_count',$json_piao_count);
        $this->assign('json_myNum_count',$json_myNum_count);
        $this->assign('json_sellNum_count',$json_sellNum_count);
        $this->assign('json_agentNum_count',$json_agentNum_count);
        $this->assign('json_myPiao_count',$json_myPiao_count);
        $this->assign('json_sellPiao_count',$json_sellPiao_count);
        $this->assign('json_agentPiao_count',$json_agentPiao_count);
        $this->assign('month_day',$month_day);
        $this->display();
    }

    //共享币充值
    public function recharge()
    {
        if(IS_GET){
            $piao = M('business')->where('business_id = '.$this->business_id())->getField('piao');
            $security = M('business')->where('business_id = '.$this->business_id())->getField('security');
            $system_config = M('system_config')->select();
            $systemSecurity = $system_config[0]['security'];
            $this->assign('piao',$piao);
            $this->assign('security',$security);
            $this->assign('systemSecurity',$systemSecurity);
            $this->assign('business_id',$this->business_id());
            $this->display();
        }elseif(IS_POST){
            $data = I('post.');
            //保证金拦截
            $security = M('business')->where('business_id = '.$this->business_id())->getField('security');
            $system_config = M('system_config')->select();
            $systemSecurity = $system_config[0]['security'];
            if(($security == 0) && (($data['piao'] / 10) < $systemSecurity)){
                echo json_encode(array('error' => 1,'msg' => '您尚未缴纳保证金，首次充值必须大于'.$systemSecurity.'元'));
                exit;
            }else{
                $url = '/business/finance/do_recharge?security='.$security.'&piao='.$data['piao'].'&bank_id='.$data['bank_id'];
                echo json_encode(array('error' => 0,'url' => $url));
                exit;
            }        
        }
    }

    //共享币充值执行
    public function do_recharge()
    {
        $piao = I('get.piao');
        $bank_id = I('get.bank_id');
        $security = I('get.security');
        if(!isset($piao) || !isset($bank_id) || !isset($security)) die;
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
        $bparams = urlencode('security='.$security.'&type=chongzhi&bank_id='.$bank_id.'&piao='.$piao.'&business_id='.$this->business_id());
        $request->setBizContent('{"product_code":"FAST_INSTANT_TRADE_PAY","out_trade_no":"'.time().'","subject":"在这里商企平台共享币充值'.$piao.'个","passback_params":"'.$bparams.'","total_amount":"'.($piao/10).'","body":"在这里商企平台的企业用户进行'.$piao.'个共享币充值。"}');

        //请求  
        $result = $aop->pageExecute ($request);

        //输出  
        echo $result;
    }  

    //共享币提现
    public function withdraw()
    {   
        //当前总票数
        $piao = M('business')->where('business_id = '.$this->business_id())->getField('piao');
        //提现手续费
        $system_config = M('system_config')->select();
        $poundage = $system_config[0]['poundage'];
        //保证金
        $security = M('business')->where('business_id = '.$this->business_id())->getField('security');
        if(IS_GET){
            $bank = M()->query('select * from bank_card,bank where bank_card.bank_id = bank.bank_id and bank_card.business_id = '.$this->business_id());
            $this->assign('bank',$bank);
            $this->assign('piao',$piao);
            $this->assign('security',$security);
            $this->assign('poundage',$poundage);
            $this->display();
        }elseif(IS_POST){
            $data = I('post.');
            if($data['piao'] > $piao){
                echo json_encode(array('error' => 1,'msg' => '输入的提现共享币数不可大于您当前的共享币余额'));
                exit;
            }
            /**
             * 需要给充值提现表添加记录
             * 需要给共享币明细表添加记录
             * 需要更新商户表共享币余额
             * 需要更改充值提现表处理状态
             */
            $data['rmb'] = (ceil($data['piao']) / 10) - (ceil($data['piao']) / 10 * $poundage);
            //充值提现表开启事务
            M('finance_log')->startTrans();
            $financeLogArray = array(
                'business_id' => $this->business_id(),
                'bank_card_id' => $data['bank_card_id'],
                'rmb' => $data['rmb'],
                'piao' => ceil($data['piao']),
                'type' => 2,
                'add_time' => date('Y-m-d H:i:s'),
                'status' => 1 //充值提现状态
            );
            M('finance_log')->add($financeLogArray);
            //共享币明细表开启事务
            M('piao_log')->startTrans();
            $piaoLogArray = array(
                'business_id' => $this->business_id(),
                'piao' => ceil($data['piao']),
                'type' => 2,
                'description' => '提现成功',
                'add_time' => date('Y-m-d H:i:s'),
            );
            M('piao_log')->add($piaoLogArray);
            //改商户表金额
            $isUpPiao = M('business')->where(array('business_id'=>$this->business_id()))->setDec('piao',ceil($data['piao']));
            if($isUpPiao){
                //提交
                M('finance_log')->commit();
                M('piao_log')->commit();
                echo json_encode(array('error' => 0,'msg' => '提现请求成功！请等待审核'));
                exit;
            }else{
                M('finance_log')->rollback();
                M('piao_log')->rollback();
                echo json_encode(array('error' => 1,'msg' => '提现请求失败'));
                exit;
            }
        }
    }

    //共享币充提记录
    public function finance_log()
    {
        $where = 'business_id = '.$this->business_id();
        //统计总数
        $count = M('finance_log')->where($where)->count();
        if ($_GET['start_time']) {
            $where .= ' and add_time >= "'. $_GET['start_time'].' 00:00:00"';
        }
        if ($_GET['end_time']) {
            $where .= ' and add_time <= "'. $_GET['end_time'].' 23:59:59"';
        }
        if ($_GET['start_time'] && $_GET['end_time']) {
            $where .= ' and add_time >= "'. $_GET['start_time'].' 00:00:00" and add_time <= "'.$_GET['end_time'].' 23:59:59"';
        }
        if ($_GET['type']) {
            $where .= ' and type = '.$_GET['type'];
        }
        //分页
        $page = new \Think\Page($count,$this->step);
        $sql = 'select * from finance_log where '.$where.' order by add_time desc limit '.$page->firstRow.','.$this->step;
        $result = M('finance_log')->query($sql);
        //循环查询银行信息
        foreach ($result as $key => $value) {
            //提现
            if($value['bank_card_id'] && $value['type'] == 2){
                $bank_card =  M('bank_card')->find($value['bank_card_id']);
                //户名和卡号
                $result[$key]['card_name'] = $bank_card['card_name'];
                $result[$key]['card_number'] = $bank_card['card_number'];
                //开户行
                $result[$key]['bank_name'] = M('bank')->find($bank_card['bank_id'])['bank_name'];
            //充值
            }else{
                //开户行
                $result[$key]['bank_name'] = M('bank')->find($value['bank_id'])['bank_name'];
            }
        }
        //充值条数
        $c = M('finance_log')->where('business_id = '.$this->business_id().' and type = 1')->count();
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('c',$c);
        $this->assign('t',$count - $c);
        $this->assign('result',$result);
        $this->display();
    }

    //共享币明细
    public function piao_log()
    {   
        $where = array('business_id'=>$this->business_id());
        //统计总数
        $count = M('piao_log')->where($where)->count();
        if ($_GET['start_time']) {
            $where['add_time'] = array('egt',$_GET['start_time'].' 00:00:00');
        }
        if ($_GET['end_time']) {
            $where['add_time'] = array('elt',$_GET['end_time'].' 23:59:59');
        }
        if ($_GET['start_time'] && $_GET['end_time']) {
            $where['add_time'] = array(array('egt',$_GET['start_time'].' 00:00:00'),array('elt',$_GET['end_time'].' 23:59:59'),'and');
        }
        if ($_GET['type']) {
            $where['type'] = $_GET['type'];
        }
        if ($_GET['description']) {
            $where['description'] = array('LIKE',$_GET['description']);
        }
        //分页
        $page = new \Think\Page($count,$this->step);
        $result = M('piao_log')->where($where)->limit($page->firstRow,$this->step)->order('add_time desc')->select();
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('result',$result);
        $this->display();
    }

    //绑定银行卡
    public function bank_card()
    {
        $result = M()->query('select * from bank_card,bank where bank_card.bank_id = bank.bank_id and bank_card.business_id = '.$this->business_id().' and is_del = 0');
        $this->assign('result',$result);
        $this->display();
    }

    //银行卡添加/编辑/删除
    public function bank_card_action()
    {   
        if(IS_POST){
            //执行删除或添加
            $method = I('post.method');
            if($method == 'delete'){
                $bank_card_id = I('post.bank_card_id');
                $isDel = M('bank_card')->where('bank_card_id = '.$bank_card_id)->save(array('is_del' => 1));
                if ($isDel) {
                    echo json_encode(array('error' => 0,'msg' => '删除成功'));
                    exit;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '删除失败'));
                    exit; 
                }
            }elseif($method == 'add'){
                $data = I('post.');
                unset($data['method']);
                $data['business_id'] = $this->business_id();
                $affected = M('bank_card')->add($data);
                if ($affected) {
                    echo json_encode(array('error' => 0,'msg' => '添加成功'));
                    exit;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '添加失败'));
                    exit; 
                }
            }
        }else{
            //添加
            $banks = M('bank')->select();
            $this->assign('banks',$banks);
            $this->display();
        }
    }

    //发送系统消息（催发货）
    public function delivery_notice(){
        if (empty($_POST['order_sn']) || empty($_POST['order_id'])) {
            echo json_encode(array('error' => 1,'msg' => '参数错误！'));
            exit; 
        }
        $order_info = M('order')->where(array('order_id'=>$_POST['order_id']))->find();
        $data = array(
                'title' => '代理商家催发货通知！',
                'content' => '代理商家催您，将订单号为：'.$_POST["order_sn"].' 的订单尽快发货！如有特殊情况请联系该商家，手机号为：'.M("business")->where(array("business_id"=>$order_info["sell_business_id"]))->getField("phone_num"),
                'business_id' => $order_info['business_id'],
                'add_time' => date('Y-m-d H:i:s'),
            );
        $result = M('notices')->add($data);
        if ($result) {
            echo json_encode(array('error' => 0,'msg' => '发送通知成功！'));
            exit; 
        }else{
            echo json_encode(array('error' => 1,'msg' => '发送通知失败！'));
            exit; 
        }
    }

    //业务信息
    public function capital_info()
    {
        //商户信息
        $result['info'] = M('business')->where('business_id = '.$this->business_id())->find();
        //推荐奖励
        $result['sumRewardPiao'] = M('business')->where('leader = '.$this->business_id())->sum('reward_piao');
        //累计发放
        $result['give_piao'] = M('dispense')->where(array('business_id'=>$this->business_id()))->sum('add_piao');
        //累计反利
        $result['rebate_piao'] = M('order')->where(array('sell_business_id'=>$this->business_id()))->sum('sell_business_piao');
        //累计代理
        $result['agent_piao'] = M('order')->where(array('business_id'=>$this->business_id(),'sell_business_id'=>array('neq',0)))->sum('business_piao');
        //累计自营
        $result['sale_piao'] = M('order')->where(array('business_id'=>$this->business_id(),'sell_business_id'=>array('eq',0)))->sum('piao');
        //累计充值
        $result['recharge_piao'] = M('finance_log')->where(array('business_id'=>$this->business_id(),'type'=>1))->sum('piao');
        //累计提现
        $result['withdrawals_piao'] = M('finance_log')->where(array('business_id'=>$this->business_id(),'type'=>2))->sum('piao');
        //推荐人数
        $result['unLeaderNum'] = M('business')->where('leader = '.$this->business_id())->count();
        $result['unLeaderNum'] = empty($result['unLeaderNum']) ? 0 : $result['unLeaderNum'];
        $this->assign('result',$result);
        $this->display();
    }

    //销售统计(组)
    public function orders_count_group()
    {
        $year = date('Y');
        $month = date('m');
        if ($_GET['year']) {
            $year = explode('-',$_GET['year'])[0];
            $month = explode('-',$_GET['year'])[1];
        }

        $str_where = 'year(add_time)='.$year.' and month(add_time)='.$month;
        //排序
        $order = 'group_id asc';

        //查询所有的组
        $groups = M('group')->where(array('is_del'=>1,'business_id'=>$this->business_id()))->order($order)->select();
        $array_group_id = array(0);
        foreach ($groups as $key => $value) {
            $array_group[] = '"'.$value['group_name'].'"';
            $array_group_id[] = $value['group_id'];
        }
        $str_group = implode(',',$array_group);

        //各组商品销售数量统计
        $groupNum_count = M('order')->field('sum(num) count,group_id')->where(array('status'=>4,'group_id'=>array('in',$array_group_id),'_string'=>$str_where))->group('group_id')->order($order)->select();
        foreach ($groupNum_count as $key => $value) {
            $array_groupNum_count[] = $value['count'];
        }
        $str_agentNum_count = implode(',',$array_groupNum_count);
        
        //各组商品销售共享币统计
        $selfNum_count = M('order')->field('sum(business_piao) sum,group_id')->where(array('status'=>4,'group_id'=>array('in',$array_group_id),'business_id'=>$this->business_id(),'_string'=>$str_where))->group('group_id')->order($order)->select();
        foreach ($selfNum_count as $key => $value) {
            $new_selfNum_count[$value['group_id']] = $value;
        }

        $sellNum_count = M('order')->field('sum(sell_business_piao) sum,group_id')->where(array('status'=>4,'group_id'=>array('in',$array_group_id),'sell_business_id'=>$this->business_id(),'_string'=>$str_where))->group('group_id')->order($order)->select();
        foreach ($sellNum_count as $key => $value) {
            $new_sellNum_count[$value['group_id']] = $value;
        }

        foreach ($array_group_id as $k => $v) {
            $selfNum = $new_selfNum_count[$v]['sum']?$new_selfNum_count[$v]['sum']:0;
            $sellNum = $new_sellNum_count[$v]['sum']?$new_sellNum_count[$v]['sum']:0;
            $array_groupPiao_count[] = ($selfNum + $sellNum);
        }
        $str_agentPiao_count = implode(',',$array_groupPiao_count);
        
        $this->assign('str_group',$str_group);
        $this->assign('str_agentNum_count',$str_agentNum_count);
        $this->assign('str_agentPiao_count',$str_agentPiao_count);
        $this->display();
    }

    //级别统计
    public function order_count_level()
    {
        $year = date('Y');
        $month = date('m');
        if ($_GET['year']) {
            $year = explode('-',$_GET['year'])[0];
            $month = explode('-',$_GET['year'])[1];
        }
        //A
        $level_A_member = M('member_'.$this->business_id())->where(array('level'=>'A'))->select();
        $level_A_id = array(0);
        foreach ($level_A_member as $key => $value) {
            $level_A_id[] = $value['phone_num'];
        }
        $where = array(
                'status' => 4,
                'phone_num' => array('in',$level_A_id),
                '_string' => 'year(add_time)='.$year.' and month(add_time)='.$month
            );
        //查询12月消费者中A级会员的个数
        $level_A_count = M('order')->where($where)->count('distinct(phone_num)');
        $level_A_orders = M('order')->where($where)->count('distinct(order_id)');
        //B
        $level_B_member = M('member_'.$this->business_id())->where(array('level'=>'B'))->select();
        $level_B_id = array(0);
        foreach ($level_B_member as $key => $value) {
            $level_B_id[] = $value['phone_num'];
        }
        $where = array(
                'status' => 4,
                'phone_num' => array('in',$level_B_id),
                '_string' => 'year(add_time)='.$year.' and month(add_time)='.$month
            );
        //查询12月消费者中B级会员的个数
        $level_B_count = M('order')->where($where)->count('distinct(phone_num)');
        $level_B_orders = M('order')->where($where)->count('distinct(order_id)');
        //C
        $level_C_member = M('member_'.$this->business_id())->where(array('level'=>'C'))->select();
        $level_C_id = array(0);
        foreach ($level_C_member as $key => $value) {
            $level_C_id[] = $value['phone_num'];
        }
        $where = array(
                'status' => 4,
                'phone_num' => array('in',$level_C_id),
                '_string' => 'year(add_time)='.$year.' and month(add_time)='.$month
            );
        //查询12月消费者中C级会员的个数
        $level_C_count = M('order')->where($where)->count('distinct(phone_num)');
        $level_C_orders = M('order')->where($where)->count('distinct(order_id)');
        //D
        $level_D_member = M('member_'.$this->business_id())->where(array('level'=>'D'))->select();
        $level_D_id = array('abc');
        foreach ($level_D_member as $key => $value) {
            $level_D_id[] = $value['phone_num'];
        }
        $where = array(
                'status' => 4,
                'phone_num' => array('in',$level_D_id),
                '_string' => 'year(add_time)='.$year.' and month(add_time)='.$month
            );
        //查询12月消费者中D级会员的个数
        $level_D_count = M('order')->where($where)->count('distinct(phone_num)');
        $level_D_orders = M('order')->where($where)->count();
        $this->assign('level_A_count',$level_A_count);
        $this->assign('level_A_orders',$level_A_orders);
        $this->assign('level_B_count',$level_B_count);
        $this->assign('level_B_orders',$level_B_orders);
        $this->assign('level_C_count',$level_C_count);
        $this->assign('level_C_orders',$level_C_orders);
        $this->assign('level_D_count',$level_D_count);
        $this->assign('level_D_orders',$level_D_orders);
        $this->display();
    }

    //级别统计-参与人数
    public function order_count_level_member()
    {
        //总人数，查子表用安全id
        $countNum = M('member_'.$this->safe_business_id())->count();
        //基础语句，查子表用安全id
        $sql = "select * from member_{$this->safe_business_id()} where 1 = 1";
        //级别
        if(I('get.level')){
            $level = I('get.level');
            $sql .= " and level = '{$level}'";
        }
        //会员手机号
        $year = date('Y');
        $month = date('m');
        if ($_GET['year']) {
            $year = explode('-',$_GET['year'])[0];
            $month = explode('-',$_GET['year'])[1];
        }
        $where = array(
                'status' => 4,
                '_string' => 'year(add_time)='.$year.' and month(add_time)='.$month
            );
        $level_orders = M('order')->distinct(true)->field('phone_num')->where($where)->select();
        $level_phone_num = array('000000000000');
        foreach ($level_orders as $key => $value) {
            $level_phone_num[] = $value['phone_num'];
        }

        $sql .= " and phone_num in (".implode(',',$level_phone_num).")";
        //拼排序
        $sql .= " order by join_time desc";
        //拼分页，查子表用安全id
        // $page = new \Think\Page($countNum,$this->step);
        // $sql .= ' limit '.$page->firstRow.','.$this->step;
        $members = M('member_'.$this->safe_business_id())->query($sql);
        //发放共享币数
        foreach ($members as $key => $value) {
            //性别
            $members[$key]['sex'] = $value['sex'] == 1 ? '男' : '女';
            //本店
            $theWhere = array('business_id' => $this->business_id(),'phone_num' => $value['phone_num']);
            $members[$key]['the_piao'] = M('dispense')->where($theWhere)->sum('add_piao');
            if(!$members[$key]['the_piao']) $members[$key]['the_piao'] = 0;
            //全部店
            $members[$key]['all_piao'] = M('dispense')->where('phone_num = '.$value['phone_num'])->sum('add_piao');
            if(!$members[$key]['all_piao']) $members[$key]['all_piao'] = 0;
        }
        $this->assign('members',$members);
        $this->assign('countNum',$countNum);
        // $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    //级别统计-参与人数-订单
    public function order_count_level_member_order()
    {
        $year = date('Y');
        $month = date('m');
        if ($_GET['year']) {
            $year = explode('-',$_GET['year'])[0];
            $month = explode('-',$_GET['year'])[1];
        }
        $where = array(
                'status' => 4
            );
        $where['_string'] = '(business_id='.$this->business_id().' OR sell_business_id='.$this->business_id().')'.' and year(add_time)='.$year.' and month(add_time)='.$month;

        if ($_GET['phone_num']) {
            $where['phone_num'] = $_GET['phone_num'];
        }else{
            $_GET['phone_num'] = '000000000000';
        }

        $result = M('order')->where($where)->order('add_time desc')->select();
        $member_info = M('member')->where(array('phone_num'=>$_GET['phone_num']))->find();
        $member_info['level'] = M('member_'.$this->business_id())->where(array('phone_num'=>$_GET['phone_num']))->getField('level');
        $this->assign('result',$result);
        $this->assign('business_id',$this->business_id());
        $this->assign('member_info',$member_info);
        $this->display();
    }
}