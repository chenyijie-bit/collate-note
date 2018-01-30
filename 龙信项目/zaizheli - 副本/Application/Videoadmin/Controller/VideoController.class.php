<?php
namespace Videoadmin\Controller;
use Think\Controller;

class VideoController extends CommonController {

    public function list(){
        //实例
    	$video = M('video');
        //搜索
        if(IS_POST){
            $sql = 'select video_video.*,video_type.type_name,video_class.class_name from video_video left join video_type on video_video.type_id = video_type.type_id left join video_class on video_video.class_id = video_class.class_id where 1 = 1';
            if($name = I('post.name')) $sql .= ' and video_video.name like "%'.$name.'%"';
            if($type_id = I('post.type_id')) $sql .= ' and video_type.type_id = '.$type_id;
            if($class_id = I('post.class_id')) $sql .= ' and video_class.class_id = '.$class_id;
        }else{
            $sql = "select video_video.*,video_type.type_name,video_class.class_name from video_video left join video_type on video_video.type_id = video_type.type_id left join video_class on video_video.class_id = video_class.class_id";
        }
        $sql .= " order by video_video.add_time desc";
        //总数
        $count = count($video->query($sql));
        //分页
        $page = new \Think\Page($count,$this->step);
        $sql .= ' limit '.$page->firstRow.','.$this->step;
        $res = $video->query($sql);
        
        $type = M('type')->select();
        $class = M('class')->select();
        $this->assign('type',$type);
        $this->assign('class',$class);
        $this->assign('res',$res);
        $this->assign('count',$count);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    public function add(){
        if(IS_GET){
            $type = M('type')->select();
            $class = M('class')->select();
            $this->assign('class',$class);
            $this->assign('type',$type);
            $this->display();
        }else{
            $inputNum = 0;
            foreach(I('post.') as $v){
                if(empty($v)) $inputNum += 1;
                if($inputNum > 1){
                    $this->error('请填写完整');
                }
            }
            $video = M('video');
            //如果有文件上传
            if($_FILES['pic_path']['error'] == 0 ){
                $upload = new \Think\Upload();// 实例化上传类    
                $upload->maxSize = 3145728 ;// 设置附件上传大小    
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
                $upload->rootPath =  './Public/';
                $upload->savePath =  'Video/index/upload/picture/'; // 设置附件上传目录    // 上传文件     
                $info = $upload->upload();
                if(!$info) {// 上传错误提示错误信息        
                    $this->error($upload->getError());    
                }
                $data = $video->create();
                $data['pic_path'] = '/Public/'.$info['pic_path']['savepath'].$info['pic_path']['savename'];
                $data['add_time'] = date('Y-m-d H:i:s');
            }else{
                $data = $video->create();
                $data['add_time'] = date('Y-m-d H:i:s');
            }
            //save更新数据 
            $success = $video->add($data);
            if($success){ 
                $this->success('添加成功','/Videoadmin/video/list');
            }else{ 
                $this->error('添加失败');
            }
        }
    }

    public function edit(){
        if(IS_GET){
            $video_id = I('get.video_id');
            if(!$video_id){
                $this->redirect('/Videoadmin/video/list');
            }
            $res = M('video')->find($video_id);
            $type = M('type')->select();
            $class = M('class')->select();
            $this->assign('class',$class);
            $this->assign('res',$res);
            $this->assign('type',$type);
            $this->display();
        }else{
            $data = I('post.');
            $video = M('video');
            //如果有文件上传
            // if($_FILES['pic_path']['error'] == 0 ){
            //     $upload = new \Think\Upload();// 实例化上传类    
            //     $upload->maxSize = 3145728 ;// 设置附件上传大小    
            //     $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            //     $upload->rootPath =  './Public/';
            //     $upload->savePath =  'Video/index/upload/picture/'; // 设置附件上传目录    // 上传文件     
            //     $info = $upload->upload();
            //     if(!$info) {// 上传错误提示错误信息        
            //         $this->error($upload->getError());    
            //     }
            //     $data['pic_path'] = '/Public/'.$info['pic_path']['savepath'].$info['pic_path']['savename'];
            //     //删旧
            //     $old = $video->field('pic_path')->find($data['video_id']);
            //     unlink('.'.$old['pic_path']);
            // }
            //save更新数据 
            $success = $video->where('video_id='.$data['video_id'])->save($data);
            if($success){ 
                $this->success('修改成功','/Videoadmin/video/list');
            }else{ 
                $this->error('修改失败');
            }
        }
    }
}