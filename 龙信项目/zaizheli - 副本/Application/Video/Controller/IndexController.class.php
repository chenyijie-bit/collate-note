<?php
namespace Video\Controller;
use Think\Controller;

class IndexController extends CommonController {

    public function index(){
        //进之前先清引用
        $_SESSION['REFERER_SITE'] = null;
        $_SESSION['SITE_URL'] = null;
        //别站引用本站
        if(strpos($_SERVER['HTTP_REFERER'],'site_url=')) $_SESSION['REFERER_SITE']['URL'] =  $_SERVER['HTTP_REFERER'];
        if($site_type = @$_GET['site_type']){
            switch($site_type){
                case 'hospital':
                    $_SESSION['REFERER_SITE']['NAME'] = '问诊';
                    $_SESSION['REFERER_SITE']['COLOR'] = '#68a0ff';
                    break;
            }
        }
        //本站调用别站
        if(isset($_GET['site_url'])){
            switch ($_GET['site_url']) {
                case 'chat':
                    $_SESSION['SITE_URL'] = 'http://www.zzlhi.com/chat';
                    break;
                default:
                    # code...
                    break;
            }
        }
        //从首页导航进入
        if($_GET['into'] == 'index'){
            $_SESSION['REFERER_SITE'] = null;
            $_SESSION['SITE_URL'] = null;
        }
    	//幻灯
    	$carousel = M('carousel')->select();
    	$this->assign('carousel',$carousel);
    	//电影院
    	$dy = M('video')->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = 1 order by video_video.add_time desc limit 0,5');
    	$this->assign('dy',$dy);
    	//类别
    	$type = M('type')->select();
    	$this->assign('type',$type);
    	//推荐
    	$level = M('video')->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_video.level,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = 1 order by video_video.level desc limit 0,5');
    	$this->assign('level',$level);
    	//TOP10
    	$top10 = M('video')->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_video.level,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = 1 order by video_video.zan_num desc limit 0,10');
    	$this->assign('top10',$top10);
    	//电视剧
    	$dsj = M('video')->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = 2 order by video_video.add_time desc limit 0,5');
    	$this->assign('dsj',$dsj);
    	//综艺
    	$zy = M('video')->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = 3 order by video_video.add_time desc limit 0,5');
    	$this->assign('zy',$zy);
    	//新闻
    	$xw = M('video')->query('select video_video.video_id,video_video.name,video_video.synopsis,video_video.pic_path,video_video.zan_num,video_class.class_name from video_video left join video_class on video_video.class_id = video_class.class_id where video_video.type_id = 4 order by video_video.add_time desc limit 0,5');
        
    	$this->assign('xw',$xw);
    	$this->display();
    }
}