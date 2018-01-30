<?php
namespace Index\Controller;
use Think\Controller;

class UserController extends CommonController {

    public function _initialize(){
        parent:: _initialize();
        //用户中心全局登录限制
        if(!$this->member['member_id']){
            header('location:http://www.zzlhi.com/log/action/in');
            die('未登录拦截');
        }
    }

    //我的资料
    public function index(){
        $memberInfo = M('member','common_')->field('nick_name,member_id')->find($this->member['member_id']);
        $base64_con = M('headimg','common_')->field('base64_con')->where('member_id = '.$this->member['member_id'])->find();
        $memberInfo['base64_con'] = $base64_con['base64_con'] ? $base64_con['base64_con'] : '/Public/Common/Member/picture/default.jpg';
        $this->assign('member',$memberInfo);
        $this->display();
    }

    //退出
    public function logout(){
        unset($_SESSION['member']);
        header('location:/');
    }

    //修改密码
    public function rePassword(){
        if(IS_GET){
            $memberInfo = M('member','common_')->field('nick_name,member_id')->find($this->member['member_id']);
            $base64_con = M('headimg','common_')->field('base64_con')->where('member_id = '.$this->member['member_id'])->find();
            $memberInfo['base64_con'] = $base64_con['base64_con'] ? $base64_con['base64_con'] : '/Public/Common/Member/picture/default.jpg';
            $this->assign('member',$memberInfo);
            $this->display();
        }else{
            $params = I('post.');
            parse_str($params['data'],$data);
            if(empty($data['password'])){
                echo json_encode(array('error' => 1,'msg' => '请输入密码'));
                return false;
            }
            if(strlen($data['password']) <= 5){
                echo json_encode(array('error' => 1,'msg' => '最少要输入6位'));
                return false;
            }
            $saveBool = M('member','common_')->where('member_id = '.$this->member['member_id'])->save(array('password' => md5($data['password'])));
            if($saveBool){
                echo json_encode(array('error' => 0,'msg' => '修改成功，请牢记您的密码'));
                return false;
            }else{
                echo json_encode(array('error' => 1,'msg' => '系统错误'));
                return false;
            }
        }
    }

    //修改资料
    public function editInfo(){
        if(IS_GET){
            $member_id = $this->member['member_id'];
            $res = M('member','common_')->field('nick_name,birthday,sex,phone_num')->find($member_id);
            $base64_con = M('headimg','common_')->field('base64_con')->where('member_id = '.$member_id)->find();
            $res['base64_con'] = $base64_con['base64_con'] ? $base64_con['base64_con'] : '/Public/Common/Member/picture/default.jpg';
            $this->assign('res',$res);
            $this->display();
        }else{
            $member_id = $this->member['member_id'];
            $member = M('member','common_');
            //如果有文件上传
            if($_FILES['headimg_path']['error'] === 0){
                //大小
                if($_FILES['headimg_path']['size'] > 102400){
                    echo "<script>alert('文件最大只能100k')</script>";
                    return false;
                }
                //类型
                if($_FILES['headimg_path']['type'] !== 'image/jpeg'){
                    echo "<script>alert('文件只支持jpg格式')</script>";
                    return false;
                }
                //base64
                $base64_img = base64EncodeImage($_FILES['headimg_path']['tmp_name']);
                $headimg = M('headimg','common_');
                if($headimg->where('member_id = '.$this->member['member_id'])->find()){
                    $headimg->where('member_id = '.$this->member['member_id'])->save(array('base64_con' => $base64_img));
                }else{
                    $headimg->add(array('member_id' => $this->member['member_id'],'base64_con' => $base64_img));
                }
            }
            $data = $member->create();
            //save更新数据
            $success = $member->where('member_id='.$this->member['member_id'])->save($data);
            echo "<script>alert('保存成功')</script>";
            return false;
        }
    }
}