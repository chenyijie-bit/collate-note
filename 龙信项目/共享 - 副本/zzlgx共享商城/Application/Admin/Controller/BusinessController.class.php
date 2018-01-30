<?php
namespace Admin\Controller;

class BusinessController extends CommonController {

    //商户列表
    public function business_list()
    {
        $where = 'business_id <> 10000';
        if ($_GET['keywords']) {
            $where .= ' and company_name like "%'.$_GET['keywords'].'%"';
        }
        //统计总数
        $count = M('business')->where($where)->count();
        //分页
        $page = new \Think\Page($count,$this->step);
        $sql = 'select * from business where '.$where.' order by add_time desc limit '.$page->firstRow.','.$this->step;
        $result = M('business')->query($sql);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('result',$result);
        $this->display();
    }

    //商户详情
    public function business_list_detail()
    {
        $business_id = I('get.business_id');
		if(!$business_id){
			header('location:/admin/business/business_list');
		}
		$info = M('business')->where(array('business_id'=>$business_id))->find();
        $professions = M('profession')->select();
        $provinces = M('province')->select();
        $citys = M('city')->where(array('province_id'=>$info['province_id']))->select();
        $areas = M('area')->where(array('city_id'=>$info['city_id']))->select();
        $this->assign('info',$info);
        $this->assign('provinces',$provinces);
        $this->assign('professions',$professions);
        $this->assign('citys',$citys);
        $this->assign('areas',$areas);
		$this->display();
    }


    //商户资料审核
    public function business_info_check()
    {
        $where = 'verify = 1';
        if ($_GET['keywords']) {
            $where .= ' and company_name like "%'.$_GET['keywords'].'%"';
        }
        //统计总数
        $count = M('business')->where($where)->count();
        //分页
        $page = new \Think\Page($count,$this->step);
        $sql = 'select * from business where '.$where.' order by add_time desc limit '.$page->firstRow.','.$this->step;
        $result = M('business')->query($sql);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('result',$result);
        $this->display();
    }

    //商户资料审核详情
    public function business_info_check_detail(){
    	if(IS_POST){
    		if(count($_POST) < 2){
    			echo json_encode(array('error' => 1,'msg' => '参数错误'));
                exit; 
    		}
    		$business_id = $_POST['business_id'];
    		$verify = $_POST['type'];
    		$affected_rows = M('business')->where('business_id = '.$business_id)->save(array('verify' => $verify));
            $verifyStr = $verify == 3 ? '通过' : '驳回';
            $array = array(
                'title' => '商户资料审核通知',
                'content' => '您的商户资料现已经被客服审核'.$verifyStr.'，请悉知。',
                'business_id' => $business_id,
                'is_read' => '0',
                'add_time' => date('Y-m-d H:i:s')
            );
    		if($affected_rows){
                M('notices')->add($array);
    			echo json_encode(array('error' => 0,'msg' => '审批成功'));
                exit; 
    		}else{
    			echo json_encode(array('error' => 1,'msg' => '系统错误'));
                exit; 
    		}
    	}else{
    		$business_id = I('get.business_id');
    		if(!$business_id){
    			header('location:/admin/business/business_info_check');
    		}
    		$info = M('business')->where(array('business_id'=>$business_id))->find();
	        $professions = M('profession')->select();
	        $provinces = M('province')->select();
	        $citys = M('city')->where(array('province_id'=>$info['province_id']))->select();
	        $areas = M('area')->where(array('city_id'=>$info['city_id']))->select();
	        $this->assign('info',$info);
	        $this->assign('provinces',$provinces);
	        $this->assign('professions',$professions);
	        $this->assign('citys',$citys);
	        $this->assign('areas',$areas);
    		$this->display();
    	}
    }

    //推荐下线递归
    private function recommend_list_recursion($business_id,$allleaders_res_count,$final_business_id,$where)
    {   
        $allleaders_res = M('business')->field('business_id')->where('leader = '.$business_id.$where)->select();
        $GLOBALS['allleaders_res_count'] += count($allleaders_res);
        foreach ($allleaders_res as $key => $value) {
            if(count($allleaders_res) > 0){
                $GLOBALS['allleaders_res_every'][$final_business_id] += 1;
                $this->recommend_list_recursion($value['business_id'],count($allleaders_res),$final_business_id,$where);
            }
        }
    }

    //推荐下线
    public function recommend_list()
    {
        $where = '';
        if ($_GET['status']==1) {
            $where .= ' and status=1';
        }else if($_GET['status']==2){
            $where .= ' and status=0';
        }

        if ($_GET['regs_start_time']) {
            $where .= ' and add_time>="'.date('Y-m-d H:i:s',strtotime($_GET['regs_start_time'])).'"';
        }
        if ($_GET['regs_end_time']) {
            $where .= ' and add_time<="'.date('Y-m-d',strtotime($_GET['regs_end_time'])).' 23:59:59"';
        }
        if ($_GET['activate_start_time']) {
            $where .= ' and first_activate_time>="'.date('Y-m-d H:i:s',strtotime($_GET['activate_start_time'])).'"';
        }
        if ($_GET['activate_end_time']) {
            $where .= ' and first_activate_time<="'.date('Y-m-d',strtotime($_GET['activate_end_time'])).' 23:59:59"';
        }

        $business_id = I('get.business_id');
        $unleaders = M('business')->field('business_id,status,legal_name,company_name,phone_num,first_activate_time,add_time')->where('leader = '.$business_id.$where)->select();
        //间接
        $allleaders_res = M('business')->field('leader')->select();
        foreach ($allleaders_res as $key => $value) {
            $allleaders[] = $value['leader'];
        }
        $everyCount = array_count_values($allleaders);
        foreach ($unleaders as $key => $value) {
            if($everyCount[$value['business_id']] > 0){
                $this->recommend_list_recursion($value['business_id'],0,$value['business_id'],$where);
            }
        }
        $jianjieCount = $GLOBALS['allleaders_res_count'];
        unset($GLOBALS['allleaders_res_count']);
        $jianjieEvery = $GLOBALS['allleaders_res_every'];
        unset($GLOBALS['allleaders_res_every']);
        if(count(explode(' > ',$_GET['recommend'])) <= 1){
            $back_url = '/admin/business/business_list';
        }else{
            $new_recommend = explode(' > ',$_GET['recommend']);
            array_pop($new_recommend);
            $parameter = '?business_id='.end($new_recommend).'&recommend='.implode(' > ',$new_recommend);
            if (count($new_recommend) <= 1) {
                $parameter .= '&is_start=1';
            }
            $back_url = '/admin/business/recommend_list'.$parameter;
        }
        $this->assign('unleaders',$unleaders);
        $this->assign('jianjieCount',$jianjieCount > 0 ? $jianjieCount : 0);
        $this->assign('jianjieEvery',$jianjieEvery);
        $this->assign('recommend',$_GET['recommend']);
        $this->assign('back_url',$back_url);
        $this->display();
    }

    //设置为代理商或取消代理商
    public function dai_business()
    {
        if (IS_POST) {
            $business_id = I('post.business_id');
            if (empty($business_id)) {
                echo json_encode(array('error'=>1,'msg'=>'参数错误'));
                exit;
            }
            if (I('post.is_dai') == 1) {
                $is_dai = 0;
            }else{
                $is_dai = 1;
            }
            $result = M('business')->where(array('business_id'=>$business_id))->setField('is_dai',$is_dai);
            if (is_int($result)) {
                echo json_encode(array('error'=>0,'msg'=>'操作成功'));
                exit;
            }else{
                echo json_encode(array('error'=>1,'msg'=>'操作失败'));
                exit;
            }
        }
    }

}