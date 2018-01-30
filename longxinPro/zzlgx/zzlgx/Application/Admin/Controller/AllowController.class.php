<?php
namespace Admin\Controller;

use Think\Controller;

class AllowController extends Controller {
    
    //结算任务计划
    public function payment_task()
    {
        if(IS_POST){
            $now_time = time();
            $n = "";
            //查询订单完成时间距现在大于48小时的已完成且未结算订单（is_payment）
            $where = array(
                    'status' => 4,
                    'success_time' => array('elt',date('Y-m-d 23:59:59',($now_time-3600*48))),
                    'is_payment' => 0
                );
            $result = M('order')->where($where)->select();
            $b_data = array();
            $business_payment_log_data = array();
            foreach ($result as $key => $value) {
                //开启事物
                M()->startTrans();
                //更改订单状态
                $order_data = array(
                    'is_payment' => 1
                );
                $order_result = M('order')->where(array('order_id'=>$value['order_id']))->save($order_data);
                if (!$order_result) {
                    //事物回滚
                    $business_payment_log_data[$value['order_id']] = 'no';
                    M()->rollback();
                    continue;
                }
                //分配商家共享币
                $b_piao_result = M('business')->where(array('business_id'=>$value['business_id']))->setInc('piao',$value['business_piao']);
                if (!$b_piao_result) {
                    //事物回滚
                    $business_payment_log_data[$value['order_id']] = 'no';
                    M()->rollback();
                    continue;
                }
                //记录商家log
                $b_log_data = array(
                        'business_id' => $value['business_id'],
                        'piao' => $value['business_piao'],
                        'type' => 1,
                        'order_sn' => $value['order_sn'],
                        'phone_num' => $value['phone_num'],
                        'description' => '出售所得',
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $b_piao_log = M('piao_log')->add($b_log_data);
                if (!$b_piao_log) {
                    //事物回滚
                    $business_payment_log_data[$value['order_id']] = 'no';
                    M()->rollback();
                    continue;
                }

                if ($value['sell_business_piao'] > 0) {
                    //分配代理商家共享币
                    $s_piao_result = M('business')->where(array('business_id'=>$value['sell_business_id']))->setInc('piao',$value['sell_business_piao']);
                    if (!$s_piao_result) {
                        //事物回滚
                        $business_payment_log_data[$value['order_id']] = 'no';
                        M()->rollback();
                        continue;
                    }
                    //记录代理商家log
                    $s_log_data = array(
                            'business_id' => $value['sell_business_id'],
                            'piao' => $value['sell_business_piao'],
                            'type' => 1,
                            'order_sn' => $value['order_sn'],
                            'phone_num' => $value['phone_num'],
                            'description' => '出售所得',
                            'add_time' => date('Y-m-d H:i:s')
                        );
                    $s_piao_log = M('piao_log')->add($s_log_data);
                    if (!$s_piao_log) {
                        //事物回滚
                        $business_payment_log_data[$value['order_id']] = 'no';
                        M()->rollback();
                        continue;
                    }
                    $b_data[$value['business_id']]['order_sn'][] = $value['order_sn'];
                    $b_data[$value['business_id']]['piao'] += $value['sell_business_piao'];
                }
                //提交事物
                M()->commit();
                $business_payment_log_data[$value['order_id']] = 'ok';
                $b_data[$value['business_id']]['order_sn'][] = $value['order_sn'];
                $b_data[$value['business_id']]['piao'] += $value['business_piao'];
                
            }
            
            
            //发出通知（订单共享币已结算）
            foreach ($b_data as $ke => $val) {
                $s_notices_data = array(
                        'title' => '订单结算通知',
                        'content' => '订单号为：'.implode(',', $val['order_sn']).' 的订单已结算，您共得到 '.$val['piao'].' 共享币，请悉知',
                        'business_id' => $ke,
                        'is_read' => 0,
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $s_notices_result = M('notices')->add($s_notices_data);
                if (!$s_notices_result) {
                    $business_payment_log_data['business_id->'.$key] = 'notice_error';
                    continue;
                }
            }

            //处理完成
            $n .= " business_payment:".json_encode($business_payment_log_data);
            //echo $n;die;


            //查询退货发起时间距现在大于48小时的订单
            $where_2 = array(
                    'status' => 6,
                    'reason_time' => array('elt',date('Y-m-d 23:59:59',($now_time-3600*48))),
                    'return_status' => 0,
                    'reason_id' => array('in',array(1,2,3)),
                    'is_payment' => 0
                );
            $result_2 = M('order')->where($where_2)->select();
            $b_data = array();
            $member_payment_log_data = array();
            foreach ($result_2 as $key => $value) {
                //开启事物
                M()->startTrans();
                //改变订单状态为已退货
                $order_data = array(
                    'is_payment' => 1,
                    'status' => 7
                );
                $order_result = M('order')->where(array('order_id'=>$value['order_id']))->save($order_data);
                if (!$order_result) {
                    //事物回滚
                    $member_payment_log_data['return_order_id_'.$value['order_id']] = 'no';
                    M()->rollback();
                    continue;
                }
                //用户增加共享币
                $piao_result = M('member')->where(array('phone_num'=>$value['phone_num']))->setInc('piao',$value['piao']);
                if (!$piao_result) {
                    //事物回滚
                    $member_payment_log_data['return_order_id_'.$value['order_id']] = 'no';
                    M()->rollback();
                    continue;
                }
                //记录dispense表（type=4）
                $m_log_data = array(
                        'business_id' => $value['business_id'],
                        'add_piao' => $value['piao'],
                        'phone_num' => $value['phone_num'],
                        'type' => 4,
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $m_piao_log = M('piao_log')->add($m_log_data);
                if (!$m_piao_log) {
                    //事物回滚
                    $member_payment_log_data['return_order_id_'.$value['order_id']] = 'no';
                    M()->rollback();
                    continue;
                }

                //提交事物
                M()->commit();
                $member_payment_log_data['return_order_id_'.$value['order_id']] = 'ok';
                $b_data[$value['business_id']]['order_sn'][] = $value['order_sn'];
                $b_data[$value['business_id']]['piao'] += $value['business_piao'];
                
            }
            
            
            //发出通知（由于您未响应用户退换货请求）
            foreach ($b_data as $ke => $val) {
                $s_notices_data = array(
                        'title' => '订单结算通知',
                        'content' => '订单号为：'.implode(',', $val['order_sn']).' 的订单，由于您超过48未处理申诉的订单，所以已返还订单的所有共享币给买家，请悉知',
                        'business_id' => $ke,
                        'is_read' => 0,
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $s_notices_result = M('notices')->add($s_notices_data);
                if (!$s_notices_result) {
                    $member_payment_log_data['business_id->'.$key] = 'notice_error';
                    continue;
                }
            }

            $n .= " member_piao_back: ".json_encode($member_payment_log_data);

            $str = "[".date('Y-m-d H:i:s')."]  ".$n."\r\n";
            echo $str;
        }   
    }

}