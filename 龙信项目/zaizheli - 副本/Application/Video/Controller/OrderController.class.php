<?php
namespace Video\Controller;
use Think\Controller;
Vendor('WxPay.WxPayApi');
Vendor('WxPay.JsApiPay');

class OrderController extends CommonController {

    //购买本影片
    public function buyThisVideo(){
        $video_id = I('post.video_id');
        //当前的时间戳
        $time = time();
        //生成订单号
        $order_sn = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //金额
        $pay_num = M('video')->find($video_id)['pay_num'];
        $data = array(
            'member_id' => $this->member['member_id'],
            'video_id' => $video_id,
            'create_time' => date('Y-m-d H:i:s',$time),
            'pay_num' => $pay_num,
            'status' => '0',
            'order_sn' => $order_sn
        );
        $video_order = M('order')->where(array('member_id'=>$this->member['member_id'],'video_id'=>$video_id))->find();
        if ($video_order) {
            $result = $video_order['order_id'];
        }else{
            $result = M('order')->add($data);            
        }
        if($result){
            $oldHost = $_SERVER['HTTP_HOST'];
            $state = base64_encode($oldHost.'#'.$result);
            echo json_encode(array('error' => 0,'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx52c227bda3132ccb&redirect_uri=http%3A%2F%2Fwww.zzlhi.com%2FVideo%2FOrder%2Fpayment&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect'));
        }else{
            echo json_encode(array('error' => 1,'msg' => '系统错误,请稍后再试'));
        }

        
    }

    //code授权
    private function get_openid($code) {
        //$code = $_GET['code'];
        //根据code获取Access_Token
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . C('AppId') . "&secret=" . C('AppSecret')  . "&code={$code}&grant_type=authorization_code";
        $token_array = json_decode($this->https_request($token_url), true); 
        //var_dump($token_array);die;
        /*array(
            "access_token" => riWesT5OFR6lZJwIih-_oODDEo95YBGE2PpmJSHA3tL1HnSDGVd837Fp_darKT8ioVJADeK4uAargwcn1DkRW1j3xANK_YZEdPsrghQtYPE
            "expires_in" => 7200 
            "refresh_token" => zqVAOb7WfXIrEDn6tZiQNhtBx22zsK_z7Kha9OyzzGnZq22LfqkAxdynOW8z4I_dSTAy_VbGDs3XBqHbY_s1XyX9b0BuLpeNwUEZkJ_PLCE
            "openid" => oNqFIwRARJXXPk3WDzKQEnk2LjQM 
            "scope" => snsapi_base  
            )*/
        $openid = $token_array['openid'];
        return $openid;
    }
    
    //curl
    private function https_request($url, $data = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //确认支付页
    public function payment(){
        //用户的id
        $id = $this->member['member_id'];
        if(empty($id)){
            echo "<script>alert('请先登录');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].";</script>";
        }

        $status = base64_decode($_GET['state']);
        $order_id = explode('#', $status)[1];
        $oldHost = explode('#', $status)[0];
        $result = M('order')->find($order_id);
        $openid = M('member','common_')->where(array('member_id'=>$id))->getField('openid');
        if (empty($openid)) {
            $openid = $this->get_openid($_GET['code']);
            $res = M('member','common_')->where(array('member_id'=>$id))->save(array('openid'=>$openid));
        }
        if (!$result) {
            echo "<script>alert('订单错误，请重新下单');history.go(-1);</script>";
        }
        $this->assign('order_id',$order_id);
        $this->assign('oldHost',$oldHost);
        $this->assign('pay_num',$result['pay_num']);
        $this->assign('video_id',$result['video_id']);
        $this->display();
    }

    //确认支付流程
    public function charges(){
        //订单查询
        $id = $this->member['member_id']; //用户的id

        $result = M('order')->where(array('member_id'=>$id,'status'=>0))->order('create_time desc')->find();
        
        $paymoney = $result['pay_num'];
        //处理参数
        
        $price = $paymoney * 100;//这个是以分为单位
        $order_sn = $result['order_sn'];

        $openid = M('member','common_')->where(array('member_id'=>$id))->getField('openid');

        $tools = new \JsApiPay();

        //②、统一下单
        $input = new \WxPayUnifiedOrder();

        $input->SetBody('微信支付'); //标题
        $input->SetAttach('龙信智博'); //标题
        $input->SetOut_trade_no($order_sn);  //订单号
        $input->SetTotal_fee($price);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("");
        $input->SetNotify_url("http://www.zzlhi.com/Video/Order/callback");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid);

        $order1 = \WxPayApi::unifiedOrder($input);

        $jsApiParameters = $tools->GetJsApiParameters($order1);

        //$jsApiParameters = json_encode(array("res"=>json_decode($jsApiParameters,true),"order"=>$order));
        //error_log(PHP_EOL . "jsApiParameters1=" . var_export($jsApiParameters, 1), 3, "/log/logs/pay.log");
        echo $jsApiParameters;
        exit;
    }
    //回调方法
    public function callback(){
        //接收返回的参数
        $postStr = file_get_contents("php://input");
        libxml_disable_entity_loader(true);
        $msg_json = json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA));
        $msg = json_decode($msg_json,true);
        //拼装数据
        $data = array(
            'appid' => $msg['appid'],
            'bank_type' => $msg['bank_type'],
            'cash_fee' => $msg['cash_fee']*100,
            'fee_type' => $msg['fee_type'],
            'is_subscribe' => $msg['is_subscribe'],
            'mch_id' => $msg['mch_id'],
            'openid' => $msg['openid'],
            'out_trade_no' => $msg['out_trade_no'],
            'result_code' => $msg['result_code'],
            'transaction_id' => $msg['transaction_id']
        );
        //M('wxpay_log')->add($data);
        
        $result = $result = M('order')->where(array('order_sn'=>$msg['out_trade_no']))->save(array('status'=>1));
        if($result){
            M('wxpay_log')->add($data);
            echo 'SUCCESS';
        }else{
            echo 'FAIL';
        }
        
        //修改一些其他的字段;
    }
}