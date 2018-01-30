<?php
namespace Hospital\Controller;
use Think\Controller;

class DoctorController extends CommonController {

    
    //医生列表
    public function lists(){
    	//获取科室名称
    	$department = $_REQUEST['department'];
    	//设置默认排序以人气（点赞数）降序
    	$order = 'count_zan desc';
    	//判断选择以评价降序
    	if ($_REQUEST['sort'] == 'h') {
    		$order = 'level desc,count_zan desc';
    	}
        $where = array();
        if ($_REQUEST['isuse'] == true) {
            $where['doctor.hospital_id'] = array('in',array(2,3,4,5,6));
        }
    	//设置每页显示条数
    	$page_count = 10;
    	//判断是否为ajax传值（获取更多）
    	if (IS_AJAX) {
    		//获取页码
    		$page = $_POST['page'];
    		
    	}
    	//判断页码为空或不为空时的limit的区间
    	if (empty($page)) {
    		$start = 0;
    		$end = $page_count;	
    	}else{
    		$start = $page_count * ($page - 1);
    		$end = $page_count;
    	}
    	//判断是某个科室或是全部科室并查询对应的医生信息
    	if (!empty($department)) {
    		$keshi = M('department')->where(array('department_id'=>$department))->getfield('department_name');
    		//未查询到科室时为全部科室
    		if (empty($keshi)) {
	            $keshi = '全部科室';
	            $doctors = D('DoctorsView')->where($where)->group('doctor.doctor_id')->having('1=1 order by '.$order)->limit($start,$end)->select();
	        }else{
                $where['doctor.department_id'] = $department;
	        	$doctors = D('DoctorsView')->where($where)->group('doctor.doctor_id')->having('1=1 order by '.$order)->limit($start,$end)->select();
	        }
    	}else{
    	    $keshi = '全部科室'; 
    	    $doctors = D('DoctorsView')->where($where)->group('doctor.doctor_id')->having('1=1 order by '.$order)->limit($start,$end)->select();
        }
    	//判断是否已登录
    	if (!empty($this->member['member_id'])) {
    		//已经登录查询是否对医生点赞
    		$zan_doctors = M('zan')->where(array('member_id'=>$this->member['member_id']))->select();
    		//将登录用户已经点赞的医生id放入一维数组中
    		$doctor_ids = array();
    		foreach ($zan_doctors as $key => $val) {
    			$doctor_ids[] = $val['doctor_id'];
    		}
    		//已登录，设置是否点赞 is_zan   0:未点赞  1:已点赞
    		foreach ($doctors as $key => $value) {
    			if (in_array($value['doctor_id'],$doctor_ids)) {
    				$doctors[$key]['is_zan'] = 1;
    			}else{
    				$doctors[$key]['is_zan'] = 0;
    			}
                if (mb_strlen($value['adept'])>13) {
                    $doctors[$key]['adept'] = mb_substr($value['adept'],0,13,'utf-8').'...';
                }
    		}
    	}else{
    		//未登录，设置全部未点赞 is_zan = 0   0:未点赞  1:已点赞
    		foreach ($doctors as $key => $value) {
    			$doctors[$key]['is_zan'] = 0;
                if (mb_strlen($value['adept'])>13) {
                    $doctors[$key]['adept'] = mb_substr($value['adept'],0,13,'utf-8').'...';
                }
                
    		}

    	}
    	//判断如果为ajax时返回json数据
    	if (IS_AJAX) {
    		if (!empty($doctors)) {
    			$array = array('status'=>1,'data'=>$doctors);
    			$this->ajaxReturn($array);die;
    		}else{
    			$array = array('status'=>0,'data'=>'已经没有了');
    			$this->ajaxReturn($array);die;
    		}
    		
    	}
    	$this->assign('doctors',$doctors);
        $this->assign('keshi',$keshi);
    	$this->display();
    }


    //医生详情
    public function detail(){
    	//接收医生的id
    	$doctor_id = $_GET['doctor_id'];
    	$member_id = $this->member['member_id'];
    	if (empty($doctor_id)) {
    		echo '<script>alert("医生参数错误,请重新进入");history.back(-1)</script>';die;
    	}
    	$info = $doctors = D('DoctorsView')->where('doctor.doctor_id='.$doctor_id)->group('doctor.doctor_id')->find();
    	//判断如果查询结果为空返回上一页并提示错误
    	if (empty($info)) {
    		echo '<script>alert("未找到该医生信息");history.back(-1)</script>';die;
    	}
    	if (!empty($member_id)) {
    		//已经登录查询是否对医生点赞
    		$zan_doctor = M('zan')->where(array('member_id'=>$member_id,'doctor_id'=>$doctor_id))->find();
	    	if (!empty($zan_doctor)) {
	    		$info['is_zan'] = 1;
	    	}else{
	    		$info['is_zan'] = 0;
	    	}
            //已经登录查询是否对医生点赞
            $is_collect = M('collect')->where(array('member_id'=>$member_id,'doctor_id'=>$doctor_id))->find();
            if (!empty($is_collect)) {
                $info['is_collect'] = 1;
            }else{
                $info['is_collect'] = 0;
            }
	    	$info['is_login'] = 1;
    	}else{
    		//未登录都为0
    		$info['is_zan'] = 0;
            $info['is_collect'] = 0;
	    	$info['is_login'] = 0;
    	}
    	//查询接诊次数
    	$info['count_see'] = M('registration')->where(array('doctor_id'=>$doctor_id))->count();
    	//查询评价条数
    	$opinion_count = M('user_opinion')->where(array('doctor_id'=>$doctor_id))->count();
    	//查询评价内容
    	$opinions = M()->table('hospital_user_opinion o,common_member m')->where('o.doctor_id='.$doctor_id.' and o.member_id=m.member_id')->field('m.nick_name,o.opinion_comment,o.create_time')->limit(0,1)->select();

    	//头衔分组
        $toux = array();
    	foreach (explode(',',$info['title']) as $key => $value) {
            if (mb_strlen($value)>13) {
                $toux[] = mb_substr($value,0,13,'utf-8').'...';
            }else{
                $toux[] = $value;
            }
            
        }
        //短的与长的介绍
        $qian=array(" ","　","\t","\n","\r","&ldquo;","&rdquo;");
        $short = str_replace($qian, '',mb_substr($info['introduce'],0,98,'utf-8').'....');
        $long = str_replace($qian, '',$info['introduce']);
    	//将出诊时间分组
    	$chu = explode(',',$info['visiting_time']);
    	$visiting = array();
    	foreach ($chu as $key => $value) {
    		$visiting[$value] = 'class="fa fa-check"';
    	}
        //可挂号的医院
        $ke_hospital_id = array(2,3,4,5,6);
        $this->assign('ke_hospital_id',$ke_hospital_id);
    	$this->assign('visiting',$visiting);
    	$this->assign('opinion_count',$opinion_count);
    	$this->assign('opinions',$opinions);
    	$this->assign('toux',$toux);
    	$this->assign('info',$info);
        $this->assign('short',$short);
        $this->assign('long',$long);
    	$this->display();
    }

    //获取医生评价
    public function get_opinions(){
    	//接收页码
    	$page = $_POST['page'];
    	if (empty($page)) {
    		$page = 2;
    	}
    	//接收医生的id
    	$doctor_id = $_POST['doctor_id'];
    	if (empty($doctor_id)) {
    		$array = array('status'=>0,'data'=>'数据错误');
			$this->ajaxReturn($array);die;
    	}
    	//设置每面显示个数
    	$count = 1;
    	//设置limit的起始
    	$start = $count * ($page-1);
    	$end = $count;
    	$opinions = M()->table('hospital_user_opinion o,common_member m')->where('o.doctor_id='.$doctor_id.' and o.member_id=m.member_id')->field('m.nick_name,o.opinion_comment,o.create_time')->limit($start,$end)->select();


		if (!empty($opinions)) {
			$array = array('status'=>1,'data'=>$opinions);
			$this->ajaxReturn($array);die;
		}else{
			$array = array('status'=>2,'data'=>'已经没有了');
			$this->ajaxReturn($array);die;
		}
    }


    //点赞
    public function laud(){
    	//获取登录用户的id
    	$member_id = $this->member['member_id'];
    	//获取医生id
    	$doctor_id = $_POST['doctor_id'];
    	if (empty($doctor_id)) {
    		$array = array('status'=>0,'data'=>'数据错误');
			$this->ajaxReturn($array);die;
    	}
    	//判断是否登录
    	if (empty($member_id)) {
    		$array = array('status'=>0,'data'=>'未登录');
			$this->ajaxReturn($array);die;
    	}

    	//查询是否点过赞
    	$is_zan = M('zan')->where(array('member_id'=>$member_id,'doctor_id'=>$doctor_id))->find();
    	if ($is_zan) {
    		$array = array('status'=>2,'data'=>'已经点过赞了');
			$this->ajaxReturn($array);die;
    	}
    	//添加点赞数据
    	$res = M('doctor')->where(array('doctor_id'=>$doctor_id))->setInc('count_zan',1);
    	$result = M('zan')->add(array('member_id'=>$member_id,'doctor_id'=>$doctor_id));
    	if ($result) {
    		$array = array('status'=>1,'data'=>$opinions);
			$this->ajaxReturn($array);die;
    	}else{
    		$array = array('status'=>0,'data'=>'点赞失败');
			$this->ajaxReturn($array);die;
    	}
    }

    //收藏/取消收藏
    public function collect(){
        //获取登录用户的id
        $member_id = $this->member['member_id'];
        //获取医生id
        $doctor_id = $_POST['doctor_id'];
        if (empty($doctor_id)) {
            $array = array('status'=>0,'data'=>'数据错误');
            $this->ajaxReturn($array);die;
        }
        //判断是否登录
        if (empty($member_id)) {
            $array = array('status'=>0,'data'=>'未登录');
            $this->ajaxReturn($array);die;
        }

        //判断是取消收藏还是添加收藏
        if ($_POST['is_collect']==1) {
            //添加收藏数据
            $result = M('collect')->add(array('member_id'=>$member_id,'doctor_id'=>$doctor_id));
        }else{
            //删除收藏数据
            $result = M('collect')->where(array('member_id'=>$member_id,'doctor_id'=>$doctor_id))->delete();
        }
        
        if ($result) {
            $array = array('status'=>1,'data'=>'操作成功');
            $this->ajaxReturn($array);die;
        }else{
            $array = array('status'=>0,'data'=>'操作失败');
            $this->ajaxReturn($array);die;
        }
    }

    //医生说
    public function chat(){
        $doctor_id = $_REQUEST['doctor_id'];
        if (empty($doctor_id)) {
            echo '<script>alert("未找到该医生信息");history.back(-1)</script>';die;
        }
        $info = $doctors = D('DoctorsView')->where('doctor.doctor_id='.$doctor_id)->group('doctor.doctor_id')->find();
        //判断如果查询结果为空返回上一页并提示错误
        if (empty($info)) {
            echo '<script>alert("未找到该医生信息");history.back(-1)</script>';die;
        }

        $star = 0;
        $count = 4;
        //判断是否有查询条件
        $where = ' and a.doctor_id='.$doctor_id;
        //查询咨询问题及科室分类
        $advices = M()->table('hospital_advice a,hospital_department d')->where('a.department_id = d.department_id'.$where)->order('a.send_time desc')->select();//limit($star,$count)->

        foreach ($advices as $key => $value) {
        //中文字符截取
        $advices[$key]['advice_comment'] = mb_substr($value['advice_comment'],0,38,'utf-8').'...';
        }

        if (IS_AJAX && !empty($advices)) {
        $arr = array(
              'status' => 1,
              'data' => $advices
          );
        $this->ajaxReturn($arr);die;
        }else if(IS_AJAX){
        $arr = array(
              'status' => 0,
              'data' => '已经没有了'
          );
        $this->ajaxReturn($arr);die;
        }

        $this->assign('advices',$advices);
        $this->assign('info',$info);
        $this->display();
    }


    private function get_doctors(){
        $url = 'http://www.dengta120.com/wap.php?op=getdoc&secure=dengta120';
        $data = $this->https_request($url);
        $data = json_decode($data,1)['data'];
        $visi = array(
                '1' => '1-1',
                '2' => '2-1',
                '3' => '3-1',
                '4' => '4-1',
                '5' => '5-1',
                '6' => '6-1',
                '7' => '7-1',
                '8' => '1-2',
                '9' => '2-2',
                '10' => '3-2',
                '11' => '4-2',
                '12' => '5-2',
                '13' => '6-2',
                '14' => '7-2'
            );
        $doctors = array();
        //医院
        $hospital = array();
        $hospitals = M('list')->select();
        foreach ($hospitals as $key => $value) {
            $hospital[$value['hospital_id']] = $value['hospital_name'];
        }
        //科室
        $cat = array();
        $cats = M('department')->select();
        foreach ($cats as $key => $value) {
            $cat[$value['department_id']] = $value['department_name'];
        }
        //职称
        $rank = array();
        $ranks = M('ranks')->select();
        foreach ($ranks as $key => $value) {
            $rank[$value['ranks_id']] = $value['ranks_name'];
        }

        foreach ($data as $k => $v) {

            if (!in_array($v['hospital'],$hospital)) {
                $result = M('list')->add(array('hospital_name'=>$v['hospital']));
                $hospital[$result] = $v['hospital'];
            }
            if (!in_array($v['catname'],$cat)) {
                $result = M('department')->add(array('department_name'=>$v['catname']));
                $cat[$result] = $v['catname'];
            }
            if (!in_array(trim($v['title'],' '),$rank) && !empty(trim($v['title'],' '))) {
                $result = M('ranks')->add(array('ranks_name'=>trim($v['title'],' ')));
                $rank[$result] = trim($v['title'],' ');
            }
            $visit = array();
            foreach (explode(',',$v['time']) as $key => $value) {
                $visit[] = $visi[$value];
            }
            $doctors[] = array(
                    'doctor_name' => $v['username'],
                    'title' => str_replace(' ',',',$v['honor']),
                    'hospital_id' => array_search($v['hospital'],$hospital),
                    'hospital_name' => $v['hospital'],
                    'department_id' => array_search($v['catname'],$cat),
                    'ranks_id' => array_search(trim($v['title'],' '),$rank),
                    'adept' => $v['adept'],
                    'head_img' => $v['avatar'],
                    'visiting_time' => implode(',',$visit),
                    'dt_id' => $v['userid'],
                    'create_time' => date('Y-m-d H:i:s',time())
                );
        }
        $result = M('doctor')->addAll($doctors);
        if ($result) {
            echo '<script>alert("导入医生信息成功！");history.back();</script>';
        }
        
        //var_dump($cat);
        //var_dump($hospital);
        //var_dump($rank);
        //var_dump($doctors);
        //var_dump($data);
    }

    public function doctor_intro(){
        $url = 'http://www.dengta120.com/wap.php?op=getdoc&secure=dengta120';
        $data = $this->https_request($url);
        $data = json_decode($data,1)['data'];
        foreach ($data as $key => $value) {
            $b = strip_tags($value['intro']);
            M('doctor')->where(array('dt_id'=>$value['userid']))->save(array('introduce'=>$b));      
        }

    }

}