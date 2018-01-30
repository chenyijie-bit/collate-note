<?php
namespace Video\Controller;
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

    //个人中心
     public function index(){
        $memberInfo = M('member','common_')->field('nick_name,member_id')->find($this->member['member_id']);
        //头像
        $base64_con = M('headimg','common_')->field('base64_con')->where('member_id = '.$this->member['member_id'])->find();
        $this->assign('memberInfo',$memberInfo);
        $this->assign('base64_con',$base64_con['base64_con'] ? $base64_con['base64_con'] : '/Public/Common/Member/picture/default.jpg');
        $this->display();
    }

    //我的收藏
    public function myFavorite(){
        if(IS_GET){
            $video_ids = M('favorite')->field('video_id')->where('member_id = '.$this->member['member_id'])->select();
            $video = M('video');
            $videos = array();
            foreach ($video_ids as $v) {
                $videos[] = $video->field('video_id,name,pic_path')->find($v['video_id']);
            }
            $this->assign('res',$videos);
            $this->display();
        }else{
            $data['video_id'] = I('post.video_id');
            $data['member_id'] = $this->member['member_id'];
            if(M('favorite')->where($data)->delete()){
                echo json_encode(array('error' => 0,'msg' => '已取消'));
                return false;
            }else{
                echo json_encode(array('error' => 1,'msg' => '取消收藏失败'));
                return false;
            }
        }
    }

    //最近浏览
    public function viewed(){
        if(IS_GET){
            $viewedRes = M('viewed')->query('select video_video.name,video_video.pic_path,video_video.synopsis,video_video.video_id,video_viewed.view_time from video_viewed left join video_video on video_viewed.video_id = video_video.video_id where video_viewed.member_id = '.$this->member['member_id'].' order by video_viewed.view_time desc');
            $this->assign('res',$viewedRes);
            $this->display();
        }else{
            if(!$params = I('post.')){
                echo json_encode(array('error' => 1,'msg' => '参数错误'));
                return false;
            }
            //清空
            if($params['method'] == 1){
                $delBool = M('viewed')->where('member_id ='.$this->member['member_id'])->delete();
                if($delBool){
                    echo json_encode(array('error' => 0));
                    return false;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '没有数据'));
                    return false;
                }
            //删指定
            }elseif($params['method'] == 2){
                $where['member_id'] = array('eq',$this->member['member_id']);
                $where['video_id'] = array('eq',$params['video_id']);
                $delBool = M('viewed')->where($where)->delete();
                if($delBool){
                    echo json_encode(array('error' => 0));
                    return false;
                }else{
                    echo json_encode(array('error' => 1,'msg' => '没有数据'));
                    return false;
                }
            }
        }
    }

    //我的订单
    public function orders(){
        $res = M('order')->where('member_id = '.$this->member['member_id'])->order('create_time desc')->select();
        foreach ($res as $k => $v) {
            $temp = M('video')->field('name,video_id')->find($res[$k]['video_id']);
            $res[$k]['name'] = $temp['name'];
            $res[$k]['video_id'] = $temp['video_id'];
        }
        $this->assign('res',$res);
        $this->display();
    }

    //删除订单
    public function del_order(){
        $order_id = $_POST['order_id'];
        if (empty($order_id)) {
            $ajax = array('error_code'=>1,'msg'=>'订单错误');
            $this->ajaxReturn($ajax);
        }
        $res = M('order')->where('member_id = '.$this->member['member_id'].' and order_id = '.$order_id)->find();
        if (!$res) {
            $ajax = array('error_code'=>1,'msg'=>'订单错误');
            $this->ajaxReturn($ajax);
        }
        $dell = M('order')->where('order_id = '.$order_id)->delete();
        if($dell){
            $ajax = array('error_code'=>0,'msg'=>'已删除');
            $this->ajaxReturn($ajax);
        }else{
            $ajax = array('error_code'=>1,'msg'=>'删除订单失败');
            $this->ajaxReturn($ajax);
        }
            
    }

}