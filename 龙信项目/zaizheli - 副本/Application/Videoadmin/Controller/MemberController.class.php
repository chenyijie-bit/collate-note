<?php
namespace Videoadmin\Controller;
use Think\Controller;

class MemberController extends CommonController {
    public function list(){
        //实例
    	$member = M('member','common_');
        //搜索
        $sql = 'select * from common_member where 1 = 1';
        if(IS_POST){
            if($phone_num = I('post.phone_num')) $sql .= ' and common_member.phone_num like "%'.$phone_num.'%"';
            $this->assign('phone_num',$phone_num);
        }
        //总数
        $count = count($member->query($sql));
        //分页
        $page = new \Think\Page($count,$this->step);
        $sql .= ' limit '.$page->firstRow.','.$this->step;
        $res = $member->query($sql);

        $this->assign('res',$res);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    public function edit(){
        if(IS_GET){
            $member_id = I('get.member_id');
            if(!$member_id){
                $this->redirect('/videoadmin/member/list');
            }
            $res = M('member','common_')->find($member_id);
            $this->assign('res',$res);
            $this->display();
        }else{
            $member_id = I('post.member_id');
            $member = M('member','common_');
            if(isset($data['password']) && !empty($data['password'])){
                $data = $member->create();
                $data['password'] = md5($data['password']);
            }else{
                $data = $member->create();
            }
            //save更新数据 
            $success = $member->where('member_id='.$data['member_id'])->save($data);
            if($success){ 
                $this->success('修改成功','/videoadmin/member/list');
            }else{ 
                $this->error('修改失败');
            }
        }
    }
}