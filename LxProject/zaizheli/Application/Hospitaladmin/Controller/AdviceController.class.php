<?php
namespace Hospitaladmin\Controller;
use Think\Controller;

class AdviceController extends CommonController {

    public function list(){
        //实例
    	$advice = M('advice');
        //搜索
        if(IS_POST){
            $sql = 'select hospital_advice.*,hospital_department.department_name,hospital_doctor.doctor_name,common_member.nick_name from hospital_advice,hospital_department,hospital_doctor,common_member where hospital_advice.member_id = common_member.member_id and hospital_advice.doctor_id = hospital_doctor.doctor_id and hospital_advice.department_id = hospital_department.department_id and hospital_doctor.hospital_id = '.$_SESSION['hospital_admin']['hospital_id'];
            if($advice_comment = I('post.advice_comment')) $sql .= ' and hospital_advice.advice_comment like "%'.$advice_comment.'%"';
            if($department_id = I('post.department_id')) $sql .= ' and hospital_department.department_id = '.$department_id;
        }else{
            $sql = "select hospital_advice.*,hospital_department.department_name,hospital_doctor.doctor_name,common_member.nick_name from hospital_advice,hospital_department,hospital_doctor,common_member where hospital_advice.member_id = common_member.member_id and hospital_advice.doctor_id = hospital_doctor.doctor_id and hospital_advice.department_id = hospital_department.department_id and hospital_doctor.hospital_id = ".$_SESSION['hospital_admin']['hospital_id'];
        }
        $sql .= " order by hospital_advice.send_time desc";
        //总数
        $count = count($advice->query($sql));
        //已经回复条数
        $replyNum = $advice->where('is_reply = 1')->count();
        //分页
        $page = new \Think\Page($count,$this->step);
        $sql .= ' limit '.$page->firstRow.','.$this->step;
        $res = $advice->query($sql);
        $department = M('department')->select();
        $this->assign('department',$department);
        $this->assign('replyNum',$replyNum);
        $this->assign('res',$res);
        $this->assign('count',$count);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    public function reply(){
        if(IS_GET){
            $advice_id = I('get.advice_id');
            if(!$advice_id){
                $this->redirect('/Hospitaladmin/Advice/list');
            }
            $sql = "select hospital_advice.*,hospital_department.department_name,hospital_doctor.doctor_name,common_member.nick_name from hospital_advice,hospital_department,hospital_doctor,common_member where hospital_advice.member_id = common_member.member_id and hospital_advice.doctor_id = hospital_doctor.doctor_id and hospital_advice.department_id = hospital_department.department_id and hospital_doctor.hospital_id = ".$_SESSION['hospital_admin']['hospital_id']." and hospital_advice.advice_id = ".$advice_id;
            $theAdvice = M('advice')->query($sql);
            $theAdviceReply = M('advice_reply')->where('advice_id = '.$advice_id)->order('send_time asc')->select();
            $this->assign('theAdvice',$theAdvice[0]);
            $this->assign('theAdviceReply',$theAdviceReply);
            $this->display();
        }else{
            $data = I('post.');
            $data['send_time'] = date('Y-m-d H:i:s');
            $advice_reply = M('advice_reply');
            //save更新数据 
            $success = $advice_reply->where('advice_id='.$data['advice_id'])->add($data);
            if($success){
                //更新问诊表状态
                M('advice')->where('advice_id ='.$data['advice_id'])->save(array('is_reply' => 1));
                $this->success('回复成功','/Hospitaladmin/Advice/reply?advice_id='.$data['advice_id']);
            }else{ 
                $this->error('回复失败');
            }
        }
    }
}