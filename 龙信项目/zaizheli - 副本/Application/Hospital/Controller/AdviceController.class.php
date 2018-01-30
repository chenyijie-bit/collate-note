<?php
namespace Hospital\Controller;
use Think\Controller;

class AdviceController extends CommonController {

    //咨询列表
    public function lists(){
      $star = 0;
      $count = 10;
      //判断是否有查询条件
      $where = '';
      if (!empty(I('get.department_id'))) {
        $department_id = I('get.department_id');
        $where = ' and a.department_id = '.$department_id;
      }
      if (IS_AJAX) {
        $page = I('post.page');
        $star = $count * ($page - 1);
        if (!empty(I('post.department_id'))) {
          $department_id = I('post.department_id');
          $where = ' and a.department_id = '.$department_id;
        }
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


    
    //添加咨询问题html
    public function add_advice(){  
      $member_id = $this->member['member_id'];
      if (empty($member_id) && empty($_GET['doctor_id'])) {
        echo '<script> if(confirm("问诊需要登录，是否登录？")){window.location.href="http://www.zzlhi.com/log/action/in?refererUrl=http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'";}else{history.back(-1)} </script>';die;
      }else if(empty($member_id)){
        echo '<script> if(confirm("问诊需要登录，是否登录？")){window.location.href="http://www.zzlhi.com/log/action/in?refererUrl=http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'";}else{history.back(-1)} </script>';die;
      }
      //查询所有科室
      $departments = M('department')->select();
      $department_id = $departments[0]['department_id'];
      //判断是否是向某个医生单独咨询
      if (!empty($_GET['doctor_id'])) {
        $department_id = M('doctor')->where('doctor_id='.$_GET['doctor_id'])->getField('department_id');
      }
      $doctors = M('doctor')->where('department_id='.$department_id)->select();
      $this->assign('department_id',$department_id);
      $this->assign('departments',$departments);  
      $this->assign('doctors',$doctors); 
      $this->display();
    }

    //获取某科室的所有医生
    public function get_doctors(){
      if (IS_POST) {
        if (empty($_POST['department_id'])) {
          $array = array(
              'status' => 0,
              'data' => '数据错误'
          );
          $this->ajaxReturn($array);
        }
        //查询医生
        $doctors = M('doctor')->where('department_id='.$_POST['department_id'])->field('doctor_id,doctor_name')->select();
        $array = array(
              'status' => 1,
              'data' => $doctors
          );
        $this->ajaxReturn($array);
      }else{
        $array = array(
              'status' => 0,
              'data' => '信息错误'
          );
        $this->ajaxReturn($array);
      }
    }


    //执行添加咨询问题程序
    public function do_add_advice(){
      $ajax = array('error_code' => 0);
      //接收图片文件
      $file = $_FILES;
      $num = 0;
      $is_up = false;
      foreach ($file as $key => $value) {
        $num++;
        if (!empty($value['name']) && ($value['size'] == 0 || $value['size'] > 1048576)) {
               $ajax['data'] = "第 ".($num)." 张上传图片大于 1M";
               $this->ajaxReturn($ajax);die;
        }

        if ($value['size'] > 0) {
          $is_up = true;
        }else{
          unset($file[$key]);
        }

      }
      $img_url = array();
      if ($is_up) {
        foreach ($file as $key => $value) {
         //上传到服务器
          if ((($file[$key]["type"] == "image/jpeg") || ($file[$key]["type"] == "image/pjpeg")) && ($file[$key]["size"] < 1048576)){
              if ($file[$key]["error"] > 0){
                  echo "Return Code: " . $file[$key]["error"] . "<br />";
              }else{
                  $time = microtime(true)*10000; //毫秒时间戳
                  $b = base_convert($time,10,36); //转为36进制
                  $data1 = substr($b,-3); //获取后三位字符
                  $c = sha1($time); //sha1 加密
                  $length = strlen($c)-1; //加密后的字符串长度
                  $start=rand(0,$length-3); //字符串起始位置随机
                  $data2=substr($c, $start,3); //获取三位字符
                  $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; //所有字符
                  $str = str_shuffle($string); //打乱所有字符
                  $star = rand(0,61); //字符串起始位置随机
                  $data3 = substr($str,$star,2); //获取两位字符
                  $data = str_shuffle($data1.$data2.$data3); //将获取到的字符串拼接，并打乱
                  $file_path = "./Public/Hospital/index/uploads/".date('Y-m-d').'/' . $data .'.jpg';

                  if(move_uploaded_file($file[$key]["tmp_name"],$file_path)){
                    $img_url[] = substr($file_path,1); 
                  }else{
                    $ajax['data'] = '图片上传失败！';
                    $this->ajaxReturn($ajax);die;
                  }
              }
          }else{
            $ajax['data'] = '请上传jpg格式的图片！';
            $this->ajaxReturn($ajax);die;
          }
          
        }

        $data = array();
        $data['images'] = implode($img_url,'#');
        
      }
      if(empty(trim($_POST['comment']))){
        $ajax['data'] = "问题内容不能为空";
        $this->ajaxReturn($ajax);die;
      }
      $data['age'] = $_POST['age'];
      $data['sex'] = $_POST['sex'];
      $data['member_id'] = $this->member['member_id'];
      $data['is_hidename'] = $_POST['niname']=='on'?1:0;
      $data['department_id'] = $_POST['department'];
      $data['doctor_id'] = $_POST['doctor_id'];
      $data['advice_comment'] = $_POST['comment'];
      $data['is_reply'] = 0;
      $data['send_time'] = date('Y-m-d H:i:s',time());
      
      $result = M('advice')->add($data);
      if ($result) {
        $ajax['error_code'] = 1;
        $ajax['data'] = "问题已经提交";
        $ajax['advice_id'] = $result;
        $this->ajaxReturn($ajax);die;
       }else{
        $ajax['data'] = "网络错误，重新填写";
        $this->ajaxReturn($ajax);die;
       } 
    }


    //咨询详情
    public function advice_info(){
      $advice_id = $_GET['advice_id'];
      if (empty($advice_id)) {
        echo '<script>alert("数据有误！");history.go(-1);</script>';
      }
      //查询问题
      $advice = M('advice')->where(array('advice_id'=>$advice_id))->find();
      //查询医生信息
      $doctor = M('doctor')->where(array('doctor_id'=>$advice['doctor_id']))->find();
      //查询用户信息
      $member = M('member','common_')->where(array('member_id'=>$advice['member_id']))->find();
      //查询用户头像
      $member['head_img'] = M('headimg','common_')->where(array('member_id'=>$advice['member_id']))->getField('base64_con');
      //查询所有回复
      $reply = M('advice_reply')->where(array('advice_id'=>$advice_id))->order('send_time asc')->select();
      if ($advice['member_id'] == $this->member['member_id']) {
        $this->assign('is_member','yes');
      }

      $this->assign('doctor',$doctor);
      $this->assign('member',$member);
      $this->assign('advice',$advice);
      $this->assign('reply',$reply);
      $this->display();
    }


    //回复
    public function reply(){
      $advice_id = $_POST['advice_id'];
      $reply_comment = $_POST['reply_comment'];
      $member_id = $this->member['member_id'];
      if (empty($advice_id) || empty($reply_comment) || empty($member_id)) {
        echo '<script>alert("数据有误！");history.back(-1);</script>';
      }

      if(empty(trim($reply_comment))){
        echo '<script>alert("问题内容不能为空！");history.back(-1);</script>';die;
      }

      $data = array(
            'advice_id' => $advice_id,
            'reply_comment' => $reply_comment,
            'member_id' => $member_id,
            'doctor_id' => 0,
            'send_time' => date('Y-m-d H:i:s',time())
        );

      $result = M('advice_reply')->add($data);
      if ($result) {
        echo '<script>alert("回复成功！");window.location.href="/Hospital/Advice/lists";</script>';
      }else{
        echo '<script>alert("回复失败，请重新回复！");window.location.href="/Hospital/Advice/advice_info?advice_id='.$advice_id.'";</script>';
      }

    }
}