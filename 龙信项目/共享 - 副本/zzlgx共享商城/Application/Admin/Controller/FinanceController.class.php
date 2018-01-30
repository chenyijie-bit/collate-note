<?php
namespace Admin\Controller;

class FinanceController extends CommonController {

    //提现审核
    public function finance_check()
    {   
        if(IS_GET){
            $where = 'type = 2 and status = 1';
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
            //分页
            $page = new \Think\Page($count,$this->step);
            $sql = 'select * from finance_log where '.$where.' order by add_time desc limit '.$page->firstRow.','.$this->step;
            $result = M('finance_log')->query($sql);
            //循环查询银行信息
            foreach ($result as $key => $value) {
                $bank_card =  M('bank_card')->find($value['bank_card_id']);
                //户名和卡号
                $result[$key]['card_name'] = $bank_card['card_name'];
                $result[$key]['card_number'] = $bank_card['card_number'];
                //开户行
                $result[$key]['bank_name'] = M('bank')->find($bank_card['bank_id'])['bank_name'];
                //企业名
                $result[$key]['company_name'] = M('business')->find($value['business_id'])['company_name'];
            }
            $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
            $this->assign('count',$count);
            $this->assign('result',$result);
            $this->display();
        }elseif(IS_POST){
            $finance_log_id = I('post.finance_log_id');
            $res = M('finance_log')->find($finance_log_id);
            $check_time = date('Y-m-d H:i:s');
            $array = array(
                'title' => '共享币提现成功通知',
                'content' => '您有一笔'.$res['piao'].'个共享币提现人民币的申请已经在'.$check_time.'打款成功，请悉知。',
                'business_id' => $res['business_id'],
                'is_read' => '0',
                'add_time' => date('Y-m-d H:i:s')
            );
            $affected_rows = M('finance_log')->where('finance_log_id = '.$finance_log_id)->save(array('status' => 2,'check_time' => $check_time));
            if($affected_rows){
                M('notices')->add($array);
                echo json_encode(array('error' => 0,'msg' => '操作成功'));
                exit;  
            }else{
                echo json_encode(array('error' => 1,'msg' => '操作失败'));
                exit;
            }
        }
    }

    //提现审核记录
    public function finance_check_log()
    {   
        if(IS_GET){
            $where = 'type = 2 and status = 2';
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
            //分页
            $page = new \Think\Page($count,$this->step);
            $sql = 'select * from finance_log where '.$where.' order by add_time desc limit '.$page->firstRow.','.$this->step;
            $result = M('finance_log')->query($sql);
            //循环查询银行信息
            foreach ($result as $key => $value) {
                $bank_card =  M('bank_card')->find($value['bank_card_id']);
                //户名和卡号
                $result[$key]['card_name'] = $bank_card['card_name'];
                $result[$key]['card_number'] = $bank_card['card_number'];
                //开户行
                $result[$key]['bank_name'] = M('bank')->find($bank_card['bank_id'])['bank_name'];
                //企业名
                $result[$key]['company_name'] = M('business')->find($value['business_id'])['company_name'];
            }
            $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
            $this->assign('count',$count);
            $this->assign('result',$result);
            $this->display();
        }
    }

}