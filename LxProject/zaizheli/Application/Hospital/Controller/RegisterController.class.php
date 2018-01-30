<?php
namespace Hospital\Controller;
use Think\Controller;

class RegisterController extends CommonController {
	//挂号页面
    public function register(){
     	//接收医生的id
    	$doctor_id = $_GET['doctor_id'];
    	$member_id = $this->member['member_id'];
    	if (empty($member_id)) {
    		echo '<script> if(confirm("挂号需要登录，是否登录？")){window.location.href="http://www.zzlhi.com/log/action/in?refererUrl=http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'";}else{history.back(-1)} </script>';die;
    	}
    	if (empty($doctor_id)) {
    		echo '<script>alert("医生参数错误,请重新进入");history.back(-1)</script>';die;
    	}
    	$info = $doctors = D('DoctorsView')->where('doctor.doctor_id='.$doctor_id)->group('doctor.doctor_id')->find();
    	//判断如果查询结果为空返回上一页并提示错误
    	if (empty($info)) {
    		echo '<script>alert("未找到该医生信息");history.back(-1)</script>';die;
    	}

    	//头衔分组
    	$toux = explode(',',$info['title']);
    	//将出诊时间分组
    	$chu = explode(',',$info['visiting_time']);
    	$visiting = array();
    	foreach ($chu as $key => $value) {
    		$visiting[$value] = 'class="fa fa-check"';
    	}

    	//查询就诊人
    	$patients = M('patients')->where(array('member_id'=>$member_id))->select();

    	//医生出诊时间
    	$arr = array();
    	$visiting_time = explode(',',$info['visiting_time']);
    	foreach ($visiting_time as $key => $value) {
    		$weekk = substr($value,0,1);
    		if ($weekk == '7') {
    			$weekk = '0';
    		}
    		$arr[] = $weekk;
    	}
    	$arr = array_flip($arr);
    	$arr = array_flip($arr);
		$weeks = array(0=>'日',1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六');
		$start_time = date('Y-m-d',time());
		$dates = array();
		for ($i=0; $i <= 30; $i++) { 
			$da = date('Y-m-d',strtotime("+$i day"));
			$da_w = date('w',strtotime($da));
			if (in_array($da_w,$arr)) {
				$dates[] = array('date'=>$da,'week'=>'星期'.$weeks[$da_w]);
			}
		}

		$this->assign('dates',$dates);
    	$this->assign('patients',$patients);
    	$this->assign('visiting',$visiting);
		$this->assign('toux',$toux);
    	$this->assign('info',$info);
    	$this->display();
    }


    //执行添加挂号单程序
    public function add_register(){
        
    	$ajax = array('status'=>0);

    	$member_id = $this->member['member_id'];
    	$patients_id = $_POST['patients_id'];
		$doctor_id = $_POST['doctor_id'];
		$consultation_date = $_POST['date'];
		$is_visit = $_POST['is_visit'];
		$is_yibao = $_POST['is_yibao'];
    	
    	if (empty($member_id)) {
    		$ajax = array('status'=>2);
    		$ajax['data'] = '请先登录';
    		$this->ajaxReturn($ajax);
    	}

        $start_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d',time())));
        $end_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))+3600*24);

        $gh_count = M('registration')->where('member_id='.$member_id.' and create_time >= "'.$start_time.'" and create_time < "'.$end_time.'"')->count();
        if ($gh_count >= 5) {
            $ajax['data'] = '您今天已挂号'.$gh_count.'次,请明天再来吧！';
            $this->ajaxReturn($ajax);die;
        }
        
        if (empty($patients_id)) {
    		$ajax['data'] = '请选择就诊人';
    		$this->ajaxReturn($ajax);
    	}
    	if (empty($doctor_id)) {
    		$ajax['data'] = '参数错误，请刷新后再挂号';
    		$this->ajaxReturn($ajax);
    	}
    	if (empty($consultation_date)) {
    		$ajax['data'] = '请选择挂号时间';
    		$this->ajaxReturn($ajax);
    	}
    	if (empty($is_visit)) {
    		$ajax['data'] = '请选择初诊或复诊';
    		$this->ajaxReturn($ajax);
    	}
    	if (!in_array($is_yibao,array('1','0'))) {
    		$ajax['data'] = '请选择是否有医保卡';
    		$this->ajaxReturn($ajax);
    	}

    	$data = array(
    			'patients_id' => $patients_id,
				'doctor_id' => $doctor_id,
				'consultation_date' => $consultation_date,
				'is_visit' => $is_visit,
				'is_yibao' => $is_yibao,
				'member_id' => $member_id,
                'is_status' => 1,
				'create_time' => date('Y-m-d H:i:s',time())
    		);	
    	$result = M('registration')->add($data);
    	if ($result) {
    		//挂号接口
            $doctor_info = M('doctor')->where(array('doctor_id'=>$doctor_id))->field('dt_id,doctor_name')->find();
            $patients_info = M('patients')->where(array('patients_id'=>$patients_id))->find();
            $now_time = time();
            $data_post = array(
                    'doctorid' => $doctor_info['dt_id'],//医生id
                    'docname' => $doctor_info['doctor_name'],//医生姓名
                    'dateday' => strtotime($data['consultation_date']) + (3600*8),//就诊日期，时间戳
                    'sickname' => $patients_info['patients_name'],//就诊人姓名
                    'identity' => $patients_info['id_card'],//就诊人身份证号
                    'mobile' => $patients_info['patients_phone'],//就诊人手机号
                    'timestamp' => $now_time,//提交时的的时间戳
                    'secure' => md5('dengta120.com' . $now_time)//安全字符串，其值是md5('dengta120.com' . $timestamp)
                );
            $resu = $this->register_interface($data_post);
    		if ($resu) {
                $resull = M('registration')->where(array('registration_id'=>$result))->save(array('other_gh_id'=>$resu));
                $ajax['status'] = 1;
                $ajax['data'] = $data_post;
                $this->ajaxReturn($ajax);
            }else{
                $resull = M('registration')->where(array('registration_id'=>$result))->delete();
                $ajax['data'] = $data_post;
                $this->ajaxReturn($ajax);
            }
            
    	}else{
    		$ajax['data'] = '添加失败';
    		$this->ajaxReturn($ajax);
    	}
    }

    //挂号接口
    private function register_interface($data=array()){
        $url = 'http://www.dengta120.com/wap.php?op=userreg';
        $result = $this->https_request($url,$data);
        
        $res = json_decode($result,1);
        if ($res['status'] > 0) {
            return $res['status'];
        }else{
            return false;
        }
    }


    //添加就诊人
    public function add_patients(){
    	$ajax = array('status'=>0);
    	$patients_name = $_POST['name'];
    	$age = $_POST['age'];
    	$phone = $_POST['phone'];
    	$member_id = $this->member['member_id'];
    	$id_card = $_POST['id_card'];
    	if (empty($patients_name)) {
    		$ajax['data'] = '请填写就诊人姓名';
    		$this->ajaxReturn($ajax);
    	}
        if (empty($id_card)) {
            $ajax['data'] = '请填写就诊人身份证号';
            $this->ajaxReturn($ajax);
        }
    	if (empty($age)) {
    		$ajax['data'] = '请填写就诊人年龄';
    		$this->ajaxReturn($ajax);
    	}
    	if (empty($phone)) {
    		$ajax['data'] = '请填写就诊人手机号';
    		$this->ajaxReturn($ajax);
    	}
    	if (strlen($phone) != 11) {
    		$ajax['data'] = '手机号格式错误';
    		$this->ajaxReturn($ajax);
    	}

    	$data = array(
    			'patients_name' => $patients_name,
                'id_card' => $id_card,
    			'patients_age' => $age,
    			'patients_phone' => $phone,
    			'member_id' => $member_id,
                'is_status' => 1
    		);	
    	$result = M('patients')->add($data);
    	if ($result) {
    		$ajax['status'] = 1;
    		$ajax['data'] = $result;
    		$this->ajaxReturn($ajax);
    	}else{
    		$ajax['data'] = '添加失败';
    		$this->ajaxReturn($ajax);
    	}
    	
    }

}