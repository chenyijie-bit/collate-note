<?php
namespace Hospital\Controller;
use Think\Controller;

class MyController extends CommonController {

    //个人中心
    public function index(){
      if (empty($this->member['member_id'])) {
        $this->display('not_login');die;
      }
      $memberInfo = M('member','common_')->field('nick_name,member_id')->find($this->member['member_id']);
      //头像
      $base64_con = M('headimg','common_')->field('base64_con')->where('member_id = '.$this->member['member_id'])->find();
      $this->assign('memberInfo',$memberInfo);
      $this->assign('base64_con',$base64_con['base64_con'] ? $base64_con['base64_con'] : '/Public/Common/Member/picture/default.jpg');
      $this->display();
    }

    //我的医生
    public function myDoctor(){
      $member_id = $this->member['member_id'];
      if (empty($member_id)) {
        echo "<script>alert('请先登录！');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?holder=true'</script>";
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
      
      $doctors = D('MydoctorsView')->where(array('collect.member_id'=>$member_id))->order('collect.collect_id desc')->limit($start,$end)->select();
      foreach ($doctors as $key => $value) {
        if (mb_strlen($value['adept']) > 13) {
          $doctors[$key]['adept'] = mb_substr($value['adept'],0,13,'utf-8').'...';
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
      $this->display();
    }

    //我的咨询
    public function myAdvice(){
      $member_id = $this->member['member_id'];
      if (empty($member_id)) {
        echo "<script>alert('请先登录！');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?holder=true'</script>";
      }
      
       $star = 0;
      $count = 10;
      //判断是否有查询条件
      $where = ' and a.member_id = '.$member_id;
      
      if (IS_AJAX) {
        $page = I('post.page');
        $star = $count * ($page - 1);
      }
      //查询咨询问题及科室分类
      $advices = M()->table('hospital_advice a,hospital_department d')->where('a.department_id = d.department_id'.$where)->order('a.send_time desc')->limit($star,$count)->select();
      foreach ($advices as $key => $value) {
        //查询已经回复消息的医生的改名及头像
        $doctor_info = M()->table('hospital_doctor d,hospital_advice_reply ar')->where('d.doctor_id=ar.doctor_id and ar.advice_id='.$value['advice_id'])->field('doctor_name,head_img')->find();
        //中文字符截取
        //$advices[$key]['advice_comment'] = mb_substr($value['advice_comment'],0,38,'utf-8').'...';
        if (empty($doctor_info)) {
          $advices[$key]['doctor_name'] = '等待医生回复';
        }else{
          $advices[$key]['doctor_name'] = $doctor_info['doctor_name'];
          $advices[$key]['head_img'] = $doctor_info['head_img'];
        }
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
      $this->display();
    }

    //常用就诊人
    public function patients(){
      $member_id = $this->member['member_id'];
      if (empty($member_id)) {
        echo "<script>alert('请先登录！');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?holder=true'</script>";
      }
      $patients = M('patients')->where(array('member_id'=>$member_id))->select();
      $this->assign('patients',$patients);
      $this->display();
    }

    //修改常用就诊人
    public function edit_patients(){
      $member_id = $this->member['member_id'];
      if (empty($member_id)) {
        echo "<script>alert('请先登录！');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?holder=true'</script>";
      }
      $patients_id = $_REQUEST['patients_id'];
      if (empty($patients_id)) {
        echo "<script>alert('参数错误！');history.back()</script>";
      }

      $result = M('patients')->where(array('member_id'=>$member_id,'patients_id'=>$patients_id))->find();

      $this->assign('result',$result);
      $this->display();
    }

    //执行修改常用就诊人程序
    public function do_edit_patients(){
      $ajax = array('status'=>0);
      $patients_name = $_POST['patients_name'];
      $age = $_POST['patients_age'];
      $phone = $_POST['patients_phone'];
      $member_id = $this->member['member_id'];
      $patients_id = $_POST['patients_id'];
      $id_card = $_POST['id_card'];
      if (empty($patients_id)) {
        echo '<script>alert("参数错误！");history.back();</script>';
      }
      if (empty($patients_name)) {
        echo '<script>alert("请填写就诊人姓名");history.back();</script>';
      }
      if (empty($id_card)) {
        echo '<script>alert("请填写就诊人身份证号");history.back();</script>';
      }
      if (empty($age)) {
        echo '<script>alert("请填写就诊人年龄");history.back();</script>';
      }
      if (empty($phone)) {
        echo '<script>alert("请填写就诊人手机号");history.back();</script>';
      }
      if (strlen($phone) != 11) {
        echo '<script>alert("手机号格式错误");history.back();</script>';
      }

      $data = array(
          'patients_name' => $patients_name,
          'id_card' => $id_card,
          'patients_age' => $age,
          'patients_phone' => $phone,
          'member_id' => $member_id
        );  
      $result = M('patients')->where(array('member_id'=>$member_id,'patients_id'=>$patients_id))->save($data);
      if (is_int($result)) {
        header("Location:/Hospital/My/patients");
      }else{
        echo '<script>alert("修改失败");history.back();</script>';
      }
    }

    //添加常用就诊人
    public function add_patients(){
      $this->display();
    }
    
    //执行添加常用就诊人程序
    public function do_add_patients(){
      $ajax = array('status'=>0);
      $patients_name = $_POST['patients_name'];
      $age = $_POST['patients_age'];
      $phone = $_POST['patients_phone'];
      $member_id = $this->member['member_id'];
      $id_card = $_POST['id_card'];
      if (empty($patients_name)) {
        echo '<script>alert("请填写就诊人姓名");history.back();</script>';
      }
      if (empty($id_card)) {
        echo '<script>alert("请填写就诊人身份证号");history.back();</script>';
      }
      if (empty($age)) {
        echo '<script>alert("请填写就诊人年龄");history.back();</script>';
      }
      if (empty($phone)) {
        echo '<script>alert("请填写就诊人手机号");history.back();</script>';
      }
      if (strlen($phone) != 11) {
        echo '<script>alert("手机号格式错误");history.back();</script>';
      }

      $data = array(
          'patients_name' => $patients_name,
          'id_card' => $id_card,
          'patients_age' => $age,
          'patients_phone' => $phone,
          'member_id' => $member_id
        );  
      $result = M('patients')->add($data);
      if ($result) {
        header("Location:/Hospital/My/patients");
      }else{
        echo '<script>alert("添加失败");history.back();</script>';
      }
    }

    //查看就诊人信息
    public function see_patients(){
      $member_id = $this->member['member_id'];
      if (empty($member_id)) {
        echo "<script>alert('请先登录！');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?holder=true'</script>";
      }
      $patients_id = $_REQUEST['patients_id'];
      if (empty($patients_id)) {
        echo "<script>alert('参数错误！');history.back()</script>";
      }

      $result = M('patients')->where(array('member_id'=>$member_id,'patients_id'=>$patients_id))->find();

      $this->assign('result',$result);
      $this->display();
    }

    //删除就诊人信息
    public function del_patients(){
      $member_id = $this->member['member_id'];
      if (empty($member_id)) {
        echo "<script>alert('请先登录！');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?holder=true'</script>";
      }
      $patients_id = $_REQUEST['patients_id'];
      if (empty($patients_id)) {
        echo "<script>alert('参数错误！');history.back()</script>";
      }

      $result = M('patients')->where(array('member_id'=>$member_id,'patients_id'=>$patients_id))->delete();

      if ($result) {
        header("Location:/Hospital/My/patients");
      }else{
        echo '<script>alert("删除失败");history.back();</script>';
      }
    }


    //我的挂号
    public function myRegister(){
      $member_id = $this->member['member_id'];
      if (empty($member_id)) {
        echo "<script>alert('请先登录！');window.location.href='http://www.zzlhi.com/log/action/in?refererUrl=http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?holder=true'</script>";
      }

      //$start = 0;
      //$count = 10;
      //if (IS_AJAX) {
        //$start = ($_POST['page'] - 1) * $count;
      //}
      //查询挂号信息
      $result = M()->table('hospital_registration r,hospital_doctor d')->where('r.member_id='.$member_id.' and d.doctor_id=r.doctor_id')->field('r.registration_id,r.is_status,r.create_time,d.doctor_name,d.head_img,d.hospital_name')->order('r.create_time desc')->limit($start,$count)->select();
      //var_dump($result);
      /*if (IS_AJAX) {
        if (count($result)>0) {
          $this->ajaxReturn(array('status'=>1,'data'=>$reslut));
        }else{
          $this->ajaxReturn(array('status'=>2,'data'=>'已经没有'))
        }
        die;
      }*/
      $this->assign('result',$result);
      $this->display();
    }

    //我的挂号详情
    public function register_info(){
      $registration_id = $_GET['registration_id'];
      if (empty($registration_id)) {
        echo "<script>alert('参数错误！');history.back()</script>";
      }

      $result = M()->table('hospital_registration r,hospital_doctor d,hospital_department hd,hospital_patients p')->where('r.registration_id='.$registration_id.' and d.doctor_id=r.doctor_id  and d.department_id=hd.department_id  and r.patients_id=p.patients_id')->field('d.doctor_name,d.hospital_name,r.consultation_date,p.patients_name,hd.department_name,r.is_status')->find();

      $this->assign('result',$result);
      $this->display();
    }

    //取消挂号
    public function cancel(){
      $registration_id = $_GET['registration_id'];
      if (empty($registration_id)) {
        echo "<script>alert('参数错误！');history.back()</script>";
      }
      $other_gh_id = M('registration')->where('registration_id='.$registration_id)->getField('other_gh_id');
      $timestamp = time();
      $secure = md5('dengta120.com' . $timestamp);
      $url = 'http://www.dengta120.com/wap.php?op=cancelreg&secure='.$secure.'&timestamp='.$timestamp.'&rid='.$other_gh_id;
      $res = $this->https_request($url);
      $res = json_decode($res,1);
      if ($res['status'] == 1) {
        $result = M('registration')->where('registration_id='.$registration_id)->save(array('is_status'=>2));
        header('location:/Hospital/My/myRegister');
      }else{
        echo "<script>alert('取消失败！');history.back()</script>";
      }
    }
}