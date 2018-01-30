<?php
namespace Hospital\Controller;
use Think\Controller;

class CommonController extends Controller {

	protected $member;

    public function _initialize(){
    	//主站返回token
        if(isset($_GET['logedMemberToken']) && !empty($_GET['logedMemberToken'])) $logedMemberToken = $_GET['logedMemberToken'];
        //作为内网时的登录
        if(isset($logedMemberToken)){
            //跳视频站时必须参数
            $_SESSION['logedMemberToken'] = $logedMemberToken;
            $logedMemberTokenArr = explode('#', base64_decode($logedMemberToken));
            $where = array(
                'member_id' => $logedMemberTokenArr[0],
                'password' => $logedMemberTokenArr[1]
            );
            $theMember = M('member','common_')->where($where)->find();
            //首次登录时唯一登录标识
            if($theMember) $this->member = $_SESSION['member'] = $theMember;
        }
        //登录后的唯一登录标识
        if(isset($_SESSION['member'])){
            $this->member = $_SESSION['member'];
        }
    	//头像
        if($this->member['member_id']){
            $headerImg = M('headimg','common_')->field('base64_con')->where('member_id = '.$this->member['member_id'])->find();
            $this->member['base64_con'] = $_SESSION['member']['base64_con'] = !empty($headerImg['base64_con']) ? $headerImg['base64_con'] : '/Public/Common/Member/picture/default.jpg';
        }
    	//全局网站配置
    	$this->assign('webConfig',M('config','common_')->find(1)); 
    }

    //CURL请求
    protected function https_request($url, $data = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}