<?php
namespace Hospital\Controller;
use Think\Controller;

class IndexController extends CommonController {

    public function index(){
        //内网进入本站显示bar
        if(isset($_GET['site_url'])) $_SESSION['SITE_URL'] = $_GET['site_url'];
        //从首页导航进入
        if($_GET['into'] == 'index') $_SESSION['SITE_URL'] = null;
       	//查询热门医生
       	$hot_doctors = M()->table('hospital_doctor d,hospital_department hd')->where('d.department_id=hd.department_id')->order('d.count_zan desc')->limit(0,6)->select();
       	//查询最近的三条咨询问题及科室分类
       	$advices = M()->table('hospital_advice a,hospital_department d')->where('a.department_id = d.department_id')->order('a.send_time desc')->limit(0,3)->select();
       	foreach ($advices as $key => $value) {
       		//查询已经回复消息的医生的改名及头像
       		$doctor_info = M()->table('hospital_doctor d,hospital_advice_reply ar')->where('d.doctor_id=ar.doctor_id and ar.advice_id='.$value['advice_id'])->field('doctor_name,head_img')->find();
       		if (empty($doctor_info)) {
       			$advices[$key]['doctor_name'] = '等待医生回复';
       		}else{
       			$advices[$key]['doctor_name'] = $doctor_info['doctor_name'];
       			$advices[$key]['head_img'] = $doctor_info['head_img'];
       		}
       	}
       	$this->assign('advices',$advices);
       	$this->assign('hot_doctors',$hot_doctors);
    	$this->display();
    }
}