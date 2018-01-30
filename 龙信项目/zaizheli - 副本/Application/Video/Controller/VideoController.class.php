<?php
namespace Video\Controller;
use Think\Controller;

class VideoController extends CommonController {

    public function list(){
    	if(IS_GET){
    		if(!$type_id = I('get.type_id')){
	    		$this->redirect('/video');
	    	}
    		$type = M('type')->select();
    		$res = M('video')->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = '.$type_id.' order by video_video.add_time desc limit 0,10');
    		$this->assign('type',$type);
    		$this->assign('res',$res);
    		$this->display();
    	}else{
    		$param = I('post.');
    		if(count($param) < 2 || !is_numeric($param['type_id']) || !is_numeric($param['p'])){
    			echo json_encode(array('error' => 1,'msg' => '参数错误'));
    			return false;
    		}
    		$type_id = $param['type_id'];
    		$step = ($param['p'] - 1) * 10;
    		$sql = 'select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = '.$type_id.' order by video_video.add_time desc limit '.$step.',10';
    		$res = M('video')->query($sql);
    		if(!$res){
    			echo json_encode(array('error' => 2,'msg' => '加载完了'));
    			return false;
    		}else{
    			echo json_encode(array('error' => 0,'res' => $res));
    			return false;
    		}
    	}
    }

    public function search(){
    	if(IS_GET){
            $video = M('video');
    		if(!$keywords = I('get.keywords')){
                //查热搜
                $hotSearch = $video->field('video_id,name')->where('search_num > 0')->order('search_num desc')->limit(10)->select();
                $this->assign('hotSearch',$hotSearch);
	    		$this->display('search_input');
                return false;
	    	}
            //搜索历史
            $searchLogNum = count($_COOKIE['searchLog']);
            switch ($searchLogNum) {
                case 0:
                    setcookie('searchLog[0]',$keywords,time()+60*60*24*7);
                    break;
                case 1:
                    setcookie('searchLog[1]',$keywords,time()+60*60*24*7);
                    break;
                case 2:
                    setcookie('searchLog[2]',$keywords,time()+60*60*24*7);
                    break;
                case 3:
                    setcookie('searchLog[3]',$keywords,time()+60*60*24*7);
                    break;
                case 4:
                    setcookie('searchLog[3]',$keywords,time()+60*60*24*7);
                    setcookie('searchLog[2]',$_COOKIE['searchLog'][3],time()+60*60*24*7);
                    setcookie('searchLog[1]',$_COOKIE['searchLog'][2],time()+60*60*24*7);
                    setcookie('searchLog[0]',$_COOKIE['searchLog'][1],time()+60*60*24*7);
                    break;
            }
            //设热搜
            $where['name'] = array('LIKE','%'.$keywords.'%');
            $video->where($where)->limit(1)->setInc('search_num',1);
            //搜索结果
    		$res = $video->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.name like "%'.$keywords.'%" order by video_video.add_time desc limit 0,10');
    		$this->assign('res',$res);
    		$this->display();
    	}else{
            //翻页
    		$param = I('post.');
    		if(count($param) < 2 || !$param['keywords'] || !is_numeric($param['p'])){
    			echo json_encode(array('error' => 1,'msg' => '参数错误'));
    			return false;
    		}
    		$keywords = $param['keywords'];
    		$step = ($param['p'] - 1) * 10;
    		$sql = 'select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.name like "%'.$keywords.'%" order by video_video.add_time desc limit '.$step.',10';
    		$res = M('video')->query($sql);
    		if(!$res){
    			echo json_encode(array('error' => 2,'msg' => '加载完了'));
    			return false;
    		}else{
    			echo json_encode(array('error' => 0,'res' => $res));
    			return false;
    		}
    	}
    }

    public function clearSearchLog(){
        $b0 = setcookie('searchLog[0]',null,time()-60*60);
        $b1 = setcookie('searchLog[1]',null,time()-60*60);
        $b2 = setcookie('searchLog[2]',null,time()-60*60);
        $b3 = setcookie('searchLog[3]',null,time()-60*60);
        if($b0&&$b1&&$b2&&$b3){
            echo json_encode(array('error' => 0));
            return false;
        }else{
            echo json_encode(array('error' => 1,'msg' => '清空失败'));
            return false;
        }
    }

    public function play(){
    	//查询
    	$video_id = I('get.video_id');
    	if(!isset($video_id) || empty($video_id) || !is_numeric($video_id)){
    		$this->redirect('/vidoe/video/list');
    	}
    	$res = M('video')->query('select video_video.video_id,video_video.name,video_video.type_id,video_video.class_id,video_video.pic_path,video_video.synopsis,video_video.play_url,video_video.zan_num,video_video.pay_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.video_id = '.$video_id);
    	//去下标
    	$res = $res[0];
    	//拆集数
    	if(!strpos($res['play_url'],'#',0)){
    		$numArr = $res['play_url'];
    		unset($res['play_url']);
    		$res['play_url'][1] = $numArr;
    	}else{
    		$res['play_url'] = explode('#',$res['play_url']);
    		//下标1开始
    		for($i=1;$i<=count($res['play_url']);$i++){
    			$tempNumArr[$i] = $res['play_url'][$i-1];
    		}
    		$res['play_url'] = $tempNumArr;
    	}
    	//推荐
    	$where['video_id'] = array('neq',$res['video_id']);
    	$tuijian = M('video')->field('video_id,name,pic_path')->where(array('type_id' => $res['type_id'],'class_id' => $res['class_id']))->where($where)->order('zan_num desc')->limit('0,6')->select();
    	//已赞
    	$isZaned = M('zan')->where(array('video_id' => $res['video_id'],'member_id' => $this->member['member_id']))->count();
    	//已收藏
    	$isFavorited = M('favorite')->where(array('video_id' => $res['video_id'],'member_id' => $this->member['member_id']))->count();
    	//最近浏览
    	if($this->member['member_id']){
    		//找旧记录
    		$oldViewed = M('viewed')->where(array('video_id' => $res['video_id'],'member_id' => $this->member['member_id']))->count();
    		if($oldViewed >= 1){
    			M('viewed')->where(array('video_id' => $res['video_id'],'member_id' => $this->member['member_id']))->save(array('view_time' => date('Y-m-d H:i:s')));
    		}else{
    			M('viewed')->add(array('video_id' => $res['video_id'],'member_id' => $this->member['member_id'],'view_time' => date('Y-m-d H:i:s')));
    		}
    	}
        //是否购买过
        $where = array('member_id' => $this->member['member_id'],'status' => 1,'video_id' => $video_id);
        $buyEd = M('order')->where($where)->find();
    	//解析
    	$this->assign('res',$res);
    	$this->assign('tuijian',$tuijian);
    	$this->assign('isZaned',$isZaned);
        $this->assign('isFavorited',$isFavorited);
        $this->assign('member_id',$this->member['member_id']);
        $this->assign('buyEd',$buyEd);
    	$this->display();
    }

    //监听用户观看的影片以及为用户更新在线时间
    public function listenLeave(){
        if(IS_GET) $this->redirect('/');
        $data = I('post.');
        if($data['listen_id'] > 0){
            //记录观看时长
            M('listen')->where('listen_id = '.$data['listen_id'])->setInc('second',$data['second']);
            //记录登录时长
            M('private_loged')->where('member_id = '.$this->member['member_id'])->save(array('last_time' => date('Y-m-d H:i:s')));
        }else{
            $insertData = array(
                'member_id' => $data['member_id'],
                'video_id' => $data['video_id'],
                'second' => 1,
                'time' => date('Y-m-d H:i:s')
            );
            $listen_id = M('listen')->add($insertData);
            if($listen_id){
                echo json_encode(array(
                    'success' => 1,
                    'listen_id' => $listen_id
                ));
            }
        }
    }

    //为用户更新在线时间
    public function updatePrivateLoged(){
        if(IS_GET) $this->redirect('/');
        //记录登录时长
        M('private_loged')->where('member_id = '.$this->member['member_id'])->save(array('last_time' => date('Y-m-d H:i:s')));
    }

    public function zan(){
    	$param = I('post.');
    	if(!$this->member['member_id']){
    		echo json_encode(array('error' => 1,'msg' => '登录后才可点赞，是否去登录？'));
    		return false;
    	}
    	if(!$param['video_id'] || !is_numeric($param['video_id'])){
    		echo json_encode(array('error' => 1,'msg' => '参数错误'));
    		return false;
    	}
    	$zan = M('zan');
        //查重复
        $zanEd = $zan->where(array('video_id' => $param['video_id'],'member_id' => $this->member['member_id']))->count();
        if($zanEd){
            echo json_encode(array('error' => 1,'msg' => '您已经赞过'));
            return false;
        }
    	$video = M('video');
        //事务操作
    	$zan->startTrans();
    	//加赞表
    	$zanBool = $zan->add(array('video_id' => $param['video_id'],'member_id' => $this->member['member_id']));
    	$newZanNum = $zan->where(array('video_id' => $param['video_id']))->count();
    	//改赞数
    	$videoBool = $video->where(array('video_id' => $param['video_id']))->save(array('zan_num' => $newZanNum));
    	if($zanBool && $videoBool){
    		$zan->commit();
    		echo json_encode(array('error' => 0,'newZanNum' => $newZanNum));
    		return false;
    	}else{
    		$zan->rollback();
    		echo json_encode(array('error' => 1,'msg' => '系统错误'));
    		return false;
    	}
    }

    public function favorite(){
    	$param = I('post.');
    	if(!$this->member['member_id']){
    		echo json_encode(array('error' => 1,'msg' => '登录后才可进行收藏，是否去登录？'));
    		return false;
    	}
    	if(!$param['video_id'] || !is_numeric($param['video_id'])){
    		echo json_encode(array('error' => 1,'msg' => '参数错误'));
    		return false;
    	}
    	$favorite = M('favorite');
    	$favoriteBool = $favorite->add(array('video_id' => $param['video_id'],'member_id' => $this->member['member_id']));
    	if($favoriteBool){
    		echo json_encode(array('error' => 0));
    		return false;
    	}else{
    		echo json_encode(array('error' => 1,'msg' => '系统错误'));
    		return false;
    	}
    }
}