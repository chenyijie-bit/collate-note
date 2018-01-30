<?php
namespace Admin\Controller;

class OrderController extends CommonController {

    //退货订单仲裁
    public function return_orders()
    {
        $where['status'] = 6;
        $where['_string'] = "return_result = 3";
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
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('where_count',$where_count);
        $this->assign('result',$result);
        $this->display();
    }

    //退货订单仲裁详情
    public function return_orders_detail()
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
        $business = M('business')->field('phone_num,legal_name,company_name,exchange_name')->find($order_info['business_id']);
        $member = M('member')->find($order_info['phone_num']);
        $this->assign('transports',$transports);
        $this->assign('order_info',$order_info);
        $this->assign('business',$business);
        $this->assign('member',$member);
        $this->display();
    }

    //分配退货订单的共享币
    public function assign_piao()
    {
        if (IS_POST) {
            if (empty($_POST['order_id'])) {
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            $order_info = M('order')->where(array('order_id'=>$_POST['order_id']))->find();
            if ($order_info['status'] != 6 || $order_info['return_result'] != 3) {
                echo json_encode(array('error'=>1,'msg'=>'非法操作'));
                exit;
            }
            //开启事物
            M()->startTrans();
            //修改订单信息
            $order_data = array(
                    'business_piao'=>$_POST['business_piao'],
                    'sell_business_piao'=>$_POST['sell_business_piao'],
                    'status' => 4,
                    'success_time' => date('Y-m-d H:i:s'),
                    'is_payment' => 1
                );
            $order_result = M('order')->where(array('order_id'=>$_POST['order_id']))->save($order_data);
            if (!$order_result) {
                //事物回滚
                M()->rollback();
                echo json_encode(array('error'=>1,'msg'=>'共享币分配失败'));
                exit;
            }
            //修改商家的票、记录log、通知商家
            if ($_POST['business_piao'] > 0) {
                //增加共享币
                $b_piao_result = M('business')->where(array('business_id'=>$order_info['business_id']))->setInc('piao',$_POST['business_piao']);
                //记录log
                $b_log_data = array(
                        'business_id' => $order_info['business_id'],
                        'piao' => $_POST['business_piao'],
                        'type' => 1,
                        'order_sn' => $order_info['order_sn'],
                        'phone_num' => $order_info['phone_num'],
                        'description' => '出售所得',
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $b_piao_log = M('piao_log')->add($b_log_data);
                //通知商家
                $b_notices_data = array(
                        'title' => '订单仲裁结算通知',
                        'content' => '订单号为 '.$order_info['order_sn'].' 的订单经过我们客服的仲裁您得到 '.$_POST['business_piao'].' 共享币，请悉知',
                        'business_id' => $order_info['business_id'],
                        'is_read' => 0,
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $b_notices_result = M('notices')->add($b_notices_data);
                if (!$b_piao_result || !$b_piao_log || !$b_notices_result) {
                    //事物回滚
                    M()->rollback();
                    echo json_encode(array('error'=>1,'msg'=>'共享币分配失败'));
                    exit;
                }
            }
            if ($_POST['sell_business_piao'] > 0) {
                $s_piao_result = M('business')->where(array('business_id'=>$order_info['sell_business_id']))->setInc('piao',$_POST['sell_business_piao']);
                $s_log_data = array(
                        'business_id' => $order_info['business_id'],
                        'piao' => $_POST['business_piao'],
                        'type' => 1,
                        'order_sn' => $order_info['order_sn'],
                        'phone_num' => $order_info['phone_num'],
                        'description' => '出售所得',
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $s_piao_log = M('piao_log')->add($s_log_data);
                //通知商家
                $s_notices_data = array(
                        'title' => '订单仲裁结算通知',
                        'content' => '订单号为 '.$order_info['order_sn'].' 的订单经过我们客服的仲裁您得到 '.$_POST['sell_business_piao'].' 共享币，请悉知',
                        'business_id' => $order_info['sell_business_id'],
                        'is_read' => 0,
                        'add_time' => date('Y-m-d H:i:s')
                    );
                $s_notices_result = M('notices')->add($s_notices_data);
                if (!$s_piao_result || !$s_piao_log || !$s_notices_result) {
                    //事物回滚
                    M()->rollback();
                    echo json_encode(array('error'=>1,'msg'=>'共享币分配失败'));
                    exit;
                }
            }

            //返回买家共享币、记录log
            if ($_POST['member_piao'] > 0) {
                //返还买家共享币
                $m_piao_result = M('member')->where(array('phone_num'=>$order_info['phone_num']))->setInc('piao',$_POST['member_piao']);
                //记录log
                $m_piao_data = array(
                        'phone_num' => $order_info['phone_num'],
                        'add_piao' => $_POST['member_piao'],
                        'business_id' => $order_info['business_id'],
                        'add_time' => date('Y-m-d H:i:s'),
                        'type' => 3
                    );
                $m_piao_log = M('dispense')->add($m_piao_data);
                if (!$m_piao_data || !$m_piao_result) {
                    //事物回滚
                    M()->rollback();
                    echo json_encode(array('error'=>1,'msg'=>'共享币分配失败'));
                    exit;
                }
            }
            
            //提交事务
            M()->commit();
            echo json_encode(array('error'=>0,'msg'=>'共享币已分配'));
            exit;
        }
    }

}