<?php
namespace Business\Controller;

class IndexController extends CommonController {
    
    //后台首页
    public function index()
    {
        //查询企业基本信息
        $info = M('business')->field('exchange_name,status,is_dai')->find($this->business_id());
        $this->assign('info',$info);
        $this->display();
    }

    //后台默认缺省页
    public function index_base()
    {
        //总人数，查子表用安全id
        $result['countNum'] = M('member_'.$this->safe_business_id())->where('is_formal = 1')->count();
        //今日营收
        $where = array(
            'business_id' => $this->business_id(),
            'add_time' => array(
                array('gt',date('Y-m-d 00:00:00')),
                array('lt',date('Y-m-d 23:59:59')),
                'and'
            )
        );
        $result['countToday'] = M('dispense')->where($where)->sum('pay_num');
        $result['countToday'] = empty($result['countToday']) ? 0 : $result['countToday'];
        //商户信息
        $result['info'] = M('business')->where('business_id = '.$this->business_id())->find();
        $result['info']['status'] = $result['info']['status'] == 1 ? '已激活' : '未激活';
        $profession = M('profession')->select();
        $result['info']['profession'] = $profession[$result['info']['profession_id'] - 1]['profession_name'];
        //推荐奖励
        $result['sumRewardPiao'] = M('business')->where('leader = '.$this->business_id())->sum('reward_piao');
        //累计发放
        $result['give_piao'] = M('dispense')->where(array('business_id'=>$this->business_id()))->sum('add_piao');
        //累计反利
        $result['rebate_piao'] = M('order')->where(array('sell_business_id'=>$this->business_id()))->sum('sell_business_piao');
        //累计代理
        $result['agent_piao'] = M('order')->where(array('business_id'=>$this->business_id(),'sell_business_id'=>array('neq',0)))->sum('business_piao');
        //累计自营
        $result['sale_piao'] = M('order')->where(array('business_id'=>$this->business_id(),'sell_business_id'=>array('eq',0)))->sum('business_piao');
        //累计充值
        $result['recharge_piao'] = M('finance_log')->where(array('business_id'=>$this->business_id(),'type'=>1))->sum('piao');
        //累计提现
        $result['withdrawals_piao'] = M('finance_log')->where(array('business_id'=>$this->business_id(),'type'=>2))->sum('piao');
        //推荐人数
        $result['unLeaderNum'] = M('business')->where('leader = '.$this->business_id())->count();
        $result['unLeaderNum'] = empty($result['unLeaderNum']) ? 0 : $result['unLeaderNum'];
        //公告
        $result['placard'] = M('placard')->order('add_time desc')->limit('7')->select();
        $this->assign('result',$result);
        $this->display();
    }
}