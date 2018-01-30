<?php

namespace Exchange\Controller;

class IndexController extends CommonController {
    
    //积分商城首页
    public function index()
    {
    	//需要接收商户id简写b，或者商品id简写g，或者分组id简写t，分别进行解析跳转
    	if(!$this->business_id() && !$this->group_id() && !$this->goods_id()){
            unset($_SESSION['exchange']['business_id']);
            unset($_SESSION['exchange']['group_id']);
            unset($_SESSION['exchange']['goods_id']);
    		header('location:/exchange?b=10001');
    		exit;
    	}elseif(!empty($this->group_id())){
    		$business_id = M('group')->where('group_id = '.$this->group_id())->getField('business_id');
    		$_SESSION['exchange']['business_id'] = $business_id;
    		header('location:/exchange/goods/group?group_id='.$this->group_id());
    		exit;
    	}elseif(!empty($_GET['g'])){
    		$business_id = M('goods')->where('goods_id = '.$this->goods_id())->getField('business_id');
    		$_SESSION['exchange']['business_id'] = $business_id;
    		header('location:/exchange/goods/detail?goods_id='.$this->goods_id());
    		exit;
    	}elseif(!empty($_GET['d'])){
            header('location:/exchange/goods/detail?goods_id='.$this->goods_id());
            exit;
        }

    	//以下流程可以保证business_id已经存在
    	
    	$exchange_name = M('business')->where(array('business_id' => $this->business_id()))->getField('exchange_name');

    	//全部商品
    	$goods = M('goods')->field('goods_id,pics,goods_name,piao,is_jian,business_id')->where(array('is_del' => 0,'status' => 1))->order('add_time desc')->select();
    	//我代理且推荐的
    	$myAgentIsJian = M('agent')->field('goods_id')->where(array('business_id' => $this->business_id(),'is_jian' => 1))->order('add_time desc')->select();
    	foreach ($myAgentIsJian as $k => $v) {
			$myAgentIsJianGoodsIds[] = $v['goods_id'];
		}
		//我代理的
		$myAgent = M('agent')->field('goods_id')->where(array('business_id' => $this->business_id()))->order('add_time desc')->select();
		//大循环
    	foreach ($goods as $key => $value) {
    		//推荐
    		if($value['business_id'] == $this->business_id() && $value['is_jian'] == 1){
    			$res_jian[] = $value;
    		}
    		if(in_array($value['goods_id'],$myAgentIsJianGoodsIds)) $res_jian[] = $value;
    		//1-2000共享币
    		if($value['business_id'] == $this->business_id() && $value['piao'] >= 1 && $value['piao'] <= 2000){
    			$res_1_2000[] = $value;
    		}
    		foreach ($myAgent as $k => $v) {
				if($v['goods_id'] == $value['goods_id'] && $value['piao'] >= 1 && $value['piao'] <= 2000) $res_1_2000[] = $value;
			}
    		//2001-5000共享币
    		if($value['business_id'] == $this->business_id() && $value['piao'] >= 2001 && $value['piao'] <= 5000){
    			$res_2001_5000[] = $value;
    		}
    		foreach ($myAgent as $k => $v) {
				if($v['goods_id'] == $value['goods_id'] && $value['piao'] >= 2001 && $value['piao'] <= 5000) $res_2001_5000[] = $value;
			}
    		//5000以上共享币
    		if($value['business_id'] == $this->business_id() && $value['piao'] >= 5000){
    			$res_5000up[] = $value;
    		}
    		foreach ($myAgent as $k => $v) {
				if($v['goods_id'] == $value['goods_id'] && $value['piao'] >= 5000) $res_5000up[] = $value;
			}
			//我代理的
			foreach ($myAgent as $k => $v) {
				if($v['goods_id'] == $value['goods_id']) $myagent[] = $value;
			}
    	}
    	$result['res_jian'] = $res_jian;
    	$result['res_1_2000'] = array_slice($res_1_2000,0,4);
    	$result['res_2001_5000'] = array_slice($res_2001_5000,0,4);
    	$result['res_5000up'] = array_slice($res_5000up,0,4);
    	$result['res_myagent'] = array_slice($myagent,0,4);

    	$this->assign('exchange_name',$exchange_name);
    	$this->assign('result',$result);
        $this->display();
    }
}