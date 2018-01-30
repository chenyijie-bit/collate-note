<?php
namespace Business\Controller;

use Think\Controller;

class AlipayController extends Controller {
    
    public function index()
    {
        return false;
    }

    public function notify_url()
    {   
        // error_log(json_encode($_POST),3,'Public/log.txt');
        $data = I('post.');
        if($data['app_id'] != '2017111309909702'){
            die('error');
        }
        parse_str(urldecode($data['passback_params']),$bparams);
        $business_id = $bparams['business_id'];
        $type = $bparams['type'];

        if($type == 'jihuo' && $data['trade_status'] == 'TRADE_SUCCESS'){
            /**
             * 走支付宝接口
             * 激活需要的钱要查数据库、返利票查数据库
             * 激活的时候需要给推荐人发放共享币（更新到商户表，记录共享币明细表）
             * 激活后更新商户表的激活时间、过期时间、状态、激活价格、返利票、建会员表
             */
            //==以下为临时测试激活流程==//
            $activate_price = $data['total_amount'];
            //系统规定的激活返利金额
            $system_config = M('system_config')->select();
            $reward_piao = $system_config[0]['reward_piao'];
            //激活
            $activeArray = array(
                'activate_time' => date('Y-m-d H:i:s'),
                'lose_time' => date('Y-m-d H:i:s',time() + 60*60*24*90),
                'status' => 1,
                'activate_price' => $activate_price,
            );
            //首次激活时间
            $first_activate_time = M('business')->where(array('business_id'=>$business_id))->getField('first_activate_time');
            if($first_activate_time == null){
                $activeArray['first_activate_time'] = date('Y-m-d H:i:s');
            }
            $leader = M('business')->where(array('business_id'=>$business_id))->getField('leader');
            if($leader){ //如果有推荐人就写上返利
                $activeArray['reward_piao'] = $reward_piao;
            }
            //商户表开启事务
            M('business')->startTrans();
            M('business')->where(array('business_id'=>$business_id))->save($activeArray);
            M('business')->where(array('business_id'=>$leader))->setInc('piao',$reward_piao);
            //共享币记录表开启事务
            M('piao_log')->startTrans();
            $addPiaoLog = array(
                'business_id' => $leader,
                'piao' => $reward_piao,
                'type' => 1,
                'description' => '推荐奖励',
                'add_time' => date('Y-m-d H:i:s')
            );
            M('piao_log')->add($addPiaoLog);
            //建商户会员表
            $createSql = <<<CREATESQL
CREATE TABLE `member_{$business_id}` ( `phone_num` varchar(50) NOT NULL COMMENT '手机号', `name` varchar(50) DEFAULT NULL COMMENT '姓名', `sex` tinyint(1) unsigned DEFAULT '0' COMMENT '性别（1男，0女）', `add_time` datetime DEFAULT NULL COMMENT '添加时间', `join_time` date DEFAULT NULL COMMENT '入会时间', `birth_time` date DEFAULT NULL COMMENT '生日', `birth_send_time` date DEFAULT NULL COMMENT '生日奖励时间', `level` varchar(255) DEFAULT 'D' COMMENT '级别（A、B、C、D）', `is_formal` tinyint(1) unsigned DEFAULT '1' COMMENT '1是，2不是', `notice` varchar(255) DEFAULT NULL COMMENT '备注', PRIMARY KEY (`phone_num`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATESQL;
            if(is_int(M()->execute($createSql))){
                //提交
                M('business')->commit();
                M('piao_log')->commit();
            }else{
                M('business')->rollback();
                M('piao_log')->rollback();
            }
            echo 'success';
        }elseif($type == 'chongzhi' && $data['trade_status'] == 'TRADE_SUCCESS'){
            /**
             * 走支付宝接口
             * 需要给充值提现表添加记录
             * 需要给共享币明细表添加记录
             * 需要更新商户表共享币余额
             * 需要更改充值提现表处理状态
             */
            $data['rmb'] = ceil($bparams['piao']) / 10;
            //充值提现表开启事务
            M('finance_log')->startTrans();
            $financeLogArray = array(
                'business_id' => $business_id,
                'bank_id' => $bparams['bank_id'],
                'rmb' => $data['rmb'],
                'piao' => $bparams['security'] == 0 ? ceil($bparams['piao']) - $systemSecurity * 10 : ceil($bparams['piao']), //第一次要交保证金
                'type' => 1,
                'add_time' => date('Y-m-d H:i:s'),
                'status' => 2 //充值提现状态暂时全部都是已完成
            );
            M('finance_log')->add($financeLogArray);
            //共享币明细表开启事务
            M('piao_log')->startTrans();
            $piaoLogArray = array(
                'business_id' => $business_id,
                'piao' => $bparams['security'] == 0 ? ceil($bparams['piao']) - $systemSecurity * 10 : ceil($bparams['piao']), //第一次要交保证金,
                'type' => 1,
                'description' => '充值成功',
                'add_time' => date('Y-m-d H:i:s'),
            );
            M('piao_log')->add($piaoLogArray);
            //改商户表金额
            if($bparams['security'] == 0){
                M('business')->startTrans();
                $r1 = M('business')->where(array('business_id'=>$business_id))->setInc('piao',ceil($bparams['piao']) - $systemSecurity * 10);
                $r2 = M('business')->where(array('business_id'=>$business_id))->save(array('security' => $systemSecurity));
                if($r1 && $r2){
                    M('business')->commit();
                    $isUpPiao = true;
                }else{
                    M('business')->rollback();
                    $isUpPiao = false;
                }
            }else{
                $isUpPiao = M('business')->where(array('business_id'=>$business_id))->setInc('piao',ceil($bparams['piao']));
            }
            if($isUpPiao){
                //提交
                M('finance_log')->commit();
                M('piao_log')->commit();
            }else{
                M('finance_log')->rollback();
                M('piao_log')->rollback();
            }
            echo 'success';
        }
    }

    public function return_url()
    {
        $this->display();
    }
}