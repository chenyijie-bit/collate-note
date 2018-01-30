<?php
namespace Business\Controller;

class MemberController extends CommonController {
    
    //会员列表
    public function lists()
    {   
        //总人数，查子表用安全id
        $countNum = M('member_'.$this->safe_business_id())->where('is_formal = 1')->count();
        //基础语句，查子表用安全id
        $sql = "select * from member_{$this->safe_business_id()} where is_formal = 1";
        //名字
        if(I('get.keywords')){
            $keywords = I('get.keywords');
            $sql .= " and name like '%{$keywords}%' or phone_num like '%{$keywords}%'";
        }
        //性别
        if(is_numeric(I('get.sex'))){
            $sex = I('get.sex');
            $sql .= " and sex = {$sex}";
        }
        //级别
        if(I('get.level')){
            $level = I('get.level');
            $sql .= " and level = '{$level}'";
        }
        //入会时间
        if ($_GET['join_start_time']) {
            $sql .= " and join_time >= '{$_GET['join_start_time']}'";
        }
        if ($_GET['join_end_time']) {
            $sql .= " and join_time <= '{$_GET['join_end_time']}'";
        }
        if ($_GET['join_start_time'] && $_GET['join_end_time']) {
            $sql .= " and join_time >= '{$_GET['join_start_time']}' and join_time <= '{$_GET['join_end_time']}'";
        }
        //生日
        if ($_GET['birth_start_time']) {
            $sql .= " and birth_time >= '{$_GET['birth_start_time']}'";
        }
        if ($_GET['birth_end_time']) {
            $sql .= " and birth_time <= '{$_GET['birth_end_time']}'";
        }
        if ($_GET['birth_start_time'] && $_GET['birth_end_time']) {
            $sql .= " and birth_time >= '{$_GET['birth_start_time']}' and birth_time <= '{$_GET['birth_end_time']}'";
        }
        //未来30天内生日
        if ($_GET['futurebirth'] == 'true') {
            //$sql .= " and concat(DATE_FORMAT(now(),'%Y-'),DATE_FORMAT(birth_time,'%m-%d')) > DATE_FORMAT(now(),'%Y-%m-%d') and concat(DATE_FORMAT(now(),'%Y-'),DATE_FORMAT(birth_time,'%m-%d')) <= DATE_FORMAT(DATE_ADD(now(),INTERVAL 30 DAY),'%Y-%m-%d')";
            $sql .= " and concat( DATE_FORMAT(now(),'%Y-'), DATE_FORMAT(birth_time,'%m-%d') ) > IF( DATE_FORMAT(now(),'%m-%d') < DATE_FORMAT(birth_time,'%m-%d'), DATE_FORMAT(now(),'%Y-%m-%d'), concat( DATE_FORMAT( DATE_SUB( now(),INTERVAL 1 YEAR ),'%Y-' ), DATE_FORMAT(birth_time,'%m-%d') ) ) and IF( DATE_FORMAT(now(),'%m-%d') < DATE_FORMAT(birth_time,'%m-%d'), concat( DATE_FORMAT(now(),'%Y-'), DATE_FORMAT(birth_time,'%m-%d') ), concat( DATE_FORMAT( DATE_ADD( now(),INTERVAL 1 YEAR ),'%Y-' ), DATE_FORMAT(birth_time,'%m-%d') ) ) <= DATE_FORMAT( DATE_ADD(now(),INTERVAL 30 DAY), '%Y-%m-%d' )";
        }
        //拼排序
        $sql .= " order by join_time desc";
        //拼分页，查子表用安全id
        // $page = new \Think\Page($countNum,$this->step);
        // $sql .= ' limit '.$page->firstRow.','.$this->step;
        $members = M('member_'.$this->safe_business_id())->query($sql);
        //发放共享币数
        foreach ($members as $key => $value) {
            //性别
            $members[$key]['sex'] = $value['sex'] == 1 ? '男' : '女';
            //本店
            $theWhere = array('business_id' => $this->business_id(),'phone_num' => $value['phone_num']);
            $members[$key]['the_piao'] = M('dispense')->where($theWhere)->sum('add_piao');
            if(!$members[$key]['the_piao']) $members[$key]['the_piao'] = 0;
            //全部店
            $members[$key]['all_piao'] = M('dispense')->where('phone_num = '.$value['phone_num'])->sum('add_piao');
            if(!$members[$key]['all_piao']) $members[$key]['all_piao'] = 0;
        }
        //所有的短信模板
        $this->assign('sms_models',C('group_SMS_model'));
        $this->assign('members',$members);
        $this->assign('countNum',$countNum);
        // $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    //添加会员
    public function add()
    {   
        if(IS_POST){
            $data = I('post.');
            //重复，查子表用安全id
            if(M('member_'.$this->safe_business_id())->find($data['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '重复！手机号 '.$data['phone_num'].' 已存在'));
                exit;
            }
            //删空
            if(!$data['phone_num']){
                echo json_encode(array('error' => 1,'msg' => '错误！手机号不可为空'));
                exit;
            }
            //正误
            if(!preg_match('/^1[0-9]{10}$/',$data['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '错误！必须输入11位的手机号'));
                exit;
            }
            //添加时间
            $data['add_time'] = date('Y-m-d H:i:s');
            $data['notice'] = mb_substr($data['notice'], 0, 255);
            //事务开启，查子表用安全id
            M('member_'.$this->safe_business_id())->startTrans();
            //添加子表，查子表用安全id
            M('member_'.$this->safe_business_id())->add($data);
            //添加主表并判断是否重复，查子表用安全id
            if(M('member')->find($data['phone_num'])){
            	M('member_'.$this->safe_business_id())->commit();
                echo json_encode(array('error' => 0,'msg' => '添加成功'));
                exit;
            }else{
            	if(M('member')->add($data)){ //查子表用安全id
	                M('member_'.$this->safe_business_id())->commit();
	                echo json_encode(array('error' => 0,'msg' => '添加成功'));
	                exit;
	            }else{ //查子表用安全id
	                M('member_'.$this->safe_business_id())->rollback();
	                echo json_encode(array('error' => 1,'msg' => '添加失败'));
	                exit;
	            }
            }
        }
        $this->display();
    }

    //会员编辑
    public function edit()
    {   
        if(IS_POST){
            $data = I('post.');
            //删空
            if(!$data['phone_num']){
                echo json_encode(array('error' => 1,'msg' => '错误！手机号不可为空'));
                exit;
            }
            //正误
            if(!preg_match('/^1[0-9]{10}$/',$data['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '错误！必须输入11位的手机号'));
                exit;
            }
            //本店，查子表用安全id
            $findTheMember = M('member_'.$this->safe_business_id())->find($data['phone_num']);
            if(!$findTheMember){
                echo json_encode(array('error' => 1,'msg' => '错误！不可以更改非本店的用户'));
                exit;
            }elseif($findTheMember['is_formal'] == 2){
                echo json_encode(array('error' => 1,'msg' => '错误！不可以更改临时会员'));
                exit;
            }
            $data['notice'] = mb_substr($data['notice'], 0, 255);
            //事务开启，查子表用安全id
            M('member_'.$this->safe_business_id())->startTrans();
            //添加子表，查子表用安全id
            M('member_'.$this->safe_business_id())->where('phone_num = '.$data['phone_num'])->save($data);
            //添加主表，查子表用安全id
            if(is_numeric(M('member')->where('phone_num = '.$data['phone_num'])->save($data))){
                M('member_'.$this->safe_business_id())->commit();
                echo json_encode(array('error' => 0,'msg' => '更改成功'));
                exit;
            }else{
                M('member_'.$this->safe_business_id())->rollback(); //查子表用安全id
                echo json_encode(array('error' => 1,'msg' => '更改失败，或您未做变动'));
                exit;
            }
        }else{
            $data = I('get.');
            $result = M('member_'.$this->safe_business_id())->find($data['phone_num']); //查子表用安全id
            $this->assign('result',$result);
            $this->display();
        }
    }

    //会员删除
    public function delete()
    {   
        $data = I('post.phone_nums');
        $phone_nums = is_array($data) ? implode($data,',') : $data;
        $deled = M('member_'.$this->safe_business_id())->delete($phone_nums);
        if($deled){
            echo json_encode(array('error' => 0,'msg' => '删除成功'));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '删除失败'));
            exit;
        }
    }

    //会员转正
    public function doformal()
    {   
        $data = I('post.phone_nums');
        $phone_nums = is_array($data) ? implode($data,',') : $data;
        $phone_nums = explode(',',$phone_nums);
        $deled = M('member_'.$this->safe_business_id())->where(array('phone_num' => array('in',$phone_nums)))->save(array('is_formal' => 1));
        if($deled){
            echo json_encode(array('error' => 0,'msg' => '转正成功'));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '转正失败'));
            exit;
        }
    }

    //会员明细
    public function detail()
    {   
        $phone_num = $_GET['phone_num'] ? $_GET['phone_num'] : 0;
        $where = array('business_id' => $this->business_id(),'phone_num' => $phone_num);
        $result = M('dispense')->where($where)->order('add_time desc')->select();
        foreach ($result as $key => $value) {
            if($value['type'] == 1 && empty($value['app_id'])){
                $result[$key]['type'] = '商家赠送';
            }elseif($value['type'] == 2 && $value['app_id'] >= 1){
                $app = M('app')->find($value['app_id']);
                $result[$key]['type'] = '活动获得（'.$app['app_name'].'）';
            }
        }
        $this->assign('result',$result);
        $this->display();
    }

    //临时会员
    public function not_formal_list()
    {   
        //总人数，查子表用安全id
        $countNum = M('member_'.$this->safe_business_id())->where('is_formal = 2')->count();
        //基础语句，查子表用安全id
        $sql = "select * from member_{$this->safe_business_id()} where is_formal = 2";
        //名字
        if(I('get.keywords')){
            $keywords = I('get.keywords');
            $sql .= " and name like '%{$keywords}%' or phone_num like '%{$keywords}%'";
        }
        //性别
        if(is_numeric(I('get.sex'))){
            $sex = I('get.sex');
            $sql .= " and sex = {$sex}";
        }
        //注册时间
        if ($_GET['add_start_time']) {
            $sql .= " and add_time >= '{$_GET['add_start_time']}'";
        }
        if ($_GET['add_end_time']) {
            $sql .= " and add_time <= '{$_GET['add_end_time']}'";
        }
        if ($_GET['add_start_time'] && $_GET['add_end_time']) {
            $sql .= " and add_time >= '{$_GET['add_start_time']}' and add_time <= '{$_GET['add_end_time']}'";
        }
        //拼排序
        $sql .= " order by add_time desc";
        //拼分页，查子表用安全id
        // $page = new \Think\Page($countNum,$this->step);
        // $sql .= ' limit '.$page->firstRow.','.$this->step;
        $members = M('member_'.$this->safe_business_id())->query($sql);
        $this->assign('members',$members);
        $this->assign('countNum',$countNum);
        // $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->display();
    }

    //会员统计
    public function member_count()
    {   
        $year = I('get.year') ? I('get.year') : date('Y');
        //男女比例
        $where = array(
            'join_time' => array(
                array('gt',$year.'-01-01'),
                array('lt',$year.'-12-31'),
                'and'
            ),
            'is_formal' => 1
        );
        $sex[0] = intval(M('member_'.$this->safe_business_id())->where($where)->where('sex = 0')->count()); //查子表用安全id
        $sex[1] = intval(M('member_'.$this->safe_business_id())->where($where)->where('sex = 1')->count()); //查子表用安全id
        //级别比例
        $where = array(
            'join_time' => array(
                array('gt',$year.'-01-01'),
                array('lt',$year.'-12-31'),
                'and'
            ),
            'is_formal' => 1
        );
        $level['A'] = intval(M('member_'.$this->safe_business_id())->where($where)->where('level = "A"')->count()); //查子表用安全id
        $level['B'] = intval(M('member_'.$this->safe_business_id())->where($where)->where('level = "B"')->count()); //查子表用安全id
        $level['C'] = intval(M('member_'.$this->safe_business_id())->where($where)->where('level = "C"')->count()); //查子表用安全id
        $level['D'] = intval(M('member_'.$this->safe_business_id())->where($where)->where('level = "D"')->count()); //查子表用安全id
        //月份比例
        for($i=1;$i<=12;$i++){
            $where = array(
                'join_time' => array(
                    array('gt',$year.'-'.$i.'-01'),
                    array('lt',$year.'-'.$i.'-31'),
                    'and'
                ),
                'is_formal' => 1
            );
            $monthCount[$i] = M('member_'.$this->safe_business_id())->where($where)->count(); //查子表用安全id
        }
        //解析
        $this->assign('sex',$sex);
        $this->assign('monthCount',$monthCount);
        $this->assign('level',$level);
        $this->display();
    }

    //共享币发放
    public function piao_send($data = null)
    {   
        //提交发放
        if(IS_POST || !empty($data)){ //被批量发放方法调用
            //接收
            if(empty($data)){
                $data = I('post.');
                $data['add_piao'] = intval(floor($data['add_piao']));
            }
            if(!$data){
                echo json_encode(array('error' => 1,'msg' => '系统错误'));
                exit;
            }
            //删空
            if(!$data['phone_num']){
                echo json_encode(array('error' => 1,'msg' => '错误！手机号不可为空'));
                exit;
            }
            //正误
            if(!preg_match('/^1[0-9]{10}$/',$data['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '错误！必须输入11位的手机号'));
                exit;
            }
            //本店，查子表用安全id
            if(!M('member_'.$this->safe_business_id())->find($data['phone_num'])){
                echo json_encode(array('error' => 1,'msg' => '错误！'.$data['phone_num'].' 并不是您店的用户，请先添加'));
                exit;
            }
            $data['business_id'] = $this->business_id();
            $data['type'] = 1; //商家发放
            $data['add_time'] = $data['add_time'].' '.date('H:i:s');
            //目前共享币数量
            $piao = M('business')->where('business_id = '.$this->business_id())->getField('piao');
            if($piao < $data['add_piao']){
                echo json_encode(array('error' => 1,'msg' => '共享币余额不足！您必须进行充值才可以继续操作'));
                exit;
            }

            //发放记录表开启事务
            M('dispense')->startTrans();
            //总会员表开启事务
            M('member')->startTrans();
            //共享币明细表开启事务
            M('piao_log')->startTrans();

            //生日
            if($data['check_birth'] == true){
                $theMember = M('member_'.$this->safe_business_id())->find($data['phone_num']);
                $theYearBirth = date('-m-d') < substr($v['birth_time'],4)?date('Y').substr($theMember['birth_time'],4):date('Y',strtotime("+1 year")).substr($theMember['birth_time'],4);
                if(($theYearBirth > date('Y-m-d')) && ($theYearBirth <= date('Y-m-d',time()+60*60*24*30)) && (date('Y-m-d') >= $theMember['birth_send_time'])){
                    $birthIng = true;
                }else{
                    $birthIng = false;
                }
                if($birthIng == true){
                    M('member_'.$this->safe_business_id())->startTrans();
                    $nextYear = date('Y') + 1;
                    $nextBirth = $nextYear.substr($theMember['birth_time'],4);
                    M('member_'.$this->safe_business_id())->where('phone_num = '.$data['phone_num'])->save(array('birth_send_time' => $nextBirth));
                }
            }

            //添加到发放记录表
            M('dispense')->add($data);
            //更改总会员表
            M('member')->where('phone_num = '.$data['phone_num'])->setInc('piao',$data['add_piao']);
            //添加共享币明细表
            M('piao_log')->add(array('business_id' => $this->business_id(),'piao' => $data['add_piao'],'type' => 2,'phone_num' => $data['phone_num'],'description' => '发放共享币','add_time' => date('Y-m-d H:i:s')));

            //商户表减积分
            $is_setdec = M('business')->where('business_id = '.$this->business_id())->setDec('piao',$data['add_piao']);
            if(is_int($is_setdec)){
            	//提交发放记录表，子表用安全id
                M('member_'.$this->safe_business_id())->commit();
                //提交总会员表
                M('member')->commit();
                //提交共享币明细表
                M('piao_log')->commit();
                //提交生日字段
                if($data['check_birth'] == true){
                    if($birthIng == true){
                        M('member_'.$this->safe_business_id())->commit();
                    }
                }
                //提示
                echo json_encode(array('error' => 0,'msg' => '发放成功！您已被扣除相应共享币'));
                exit;
            }else{
            	//回滚发放记录表，子表用安全id
                M('member_'.$this->safe_business_id())->rollback();
                //回滚总会员表
                M('member')->rollback();
                //回滚共享币明细表
                M('piao_log')->rollback();
                //提交生日字段
                if($data['check_birth'] == true){
                    if($birthIng == true){
                        M('member_'.$this->safe_business_id())->rollback();
                    }
                }
                //提示
                echo json_encode(array('error' => 1,'msg' => '发放失败'));
                exit;
            }
        }else{
            //匹配手机号
            if(I('get.match_phone_num')){
                $match_phone_num = I('get.match_phone_num');
                $result = M('member_'.$this->safe_business_id())->field('phone_num')->where(array('phone_num' => array('like','%'.$match_phone_num.'%'),'is_formal' => 1))->select(); //子表用安全id
                echo json_encode(array('error' => 0,'res' => $result));
                exit;
            //显示共享币
            }else{
                //目前共享币数量
                $piao = M('business')->where('business_id = '.$this->business_id())->getField('piao');
                $security = M('business')->where('business_id = '.$this->business_id())->getField('security');
                $this->assign('piao',$piao);
                $this->assign('security',$security);
                $this->display();
            }
        }
    }

    //批量发放共享币
    public function batch_piao_send(){
        //接收
        $postData = I('post.');
        $phone_nums = $postData['phone_nums'];
        if(count($phone_nums) < 1){
            echo json_encode(array('error' => 1,'msg' => '数据错误！最少需要勾选一个会员'));
            exit;
        }
        if(count($phone_nums) == 1){
            $params['phone_num'] = $phone_nums[0];
            $params['pay_num'] = 0;
            $params['add_time'] = date('Y-m-d');
            $params['add_piao'] = intval(floor($postData['add_piao']));
            $params['check_birth'] = true;
            $this->piao_send($params);
        }elseif(count($phone_nums) > 1){
            $data['business_id'] = $this->business_id();
            $data['type'] = 1;
            $data['pay_num'] = 0;
            $data['add_time'] = date('Y-m-d H:i:s');
            $data['add_piao'] = intval(floor($postData['add_piao']));
            $data['check_birth'] = true;
            //目前共享币数量
            $piao = M('business')->where('business_id = '.$this->business_id())->getField('piao');
            if($piao < $data['add_piao'] * count($phone_nums)){
                echo json_encode(array('error' => 1,'msg' => '共享币余额不足 '.$data['add_piao'] * count($phone_nums).' 个！您必须进行充值才可以继续操作'));
                exit;
            }

            //发放记录表事务开启
            M('dispense')->startTrans();
            //总会员表开启事务
            M('member')->startTrans();
            //共享币明细表开启事务
            M('piao_log')->startTrans();
            //为生日子会员表开启事务
            M('member_'.$this->safe_business_id())->startTrans();
            $birthIngNums = 0;

            for($i=0;$i<count($phone_nums);$i++){
                //遍历的每人手机号
                $data['phone_num'] = $phone_nums[$i];
                //添加到发放记录表
                M('dispense')->add($data);
                //更改总会员表
                M('member')->where('phone_num = '.$data['phone_num'])->setInc('piao',$data['add_piao']);
                //添加共享币明细表
                M('piao_log')->add(array('business_id' => $this->business_id(),'piao' => $data['add_piao'],'type' => 2,'phone_num' => $data['phone_num'],'description' => '发放共享币','add_time' => date('Y-m-d H:i:s')));
                //生日
                if($data['check_birth'] == true){
                    $theMember = M('member_'.$this->safe_business_id())->find($data['phone_num']);
                    $theYearBirth = date('-m-d') < substr($v['birth_time'],4)?date('Y').substr($theMember['birth_time'],4):date('Y',strtotime("+1 year")).substr($theMember['birth_time'],4);
                    if(($theYearBirth > date('Y-m-d')) && ($theYearBirth <= date('Y-m-d',time()+60*60*24*30)) && (date('Y-m-d') >= $theMember['birth_send_time'])){
                        $birthIng = true;
                    }else{
                        $birthIng = false;
                    }
                    if($birthIng == true){
                        $nextYear = date('Y') + 1;
                        $nextBirth = $nextYear.substr($theMember['birth_time'],4);
                        M('member_'.$this->safe_business_id())->where('phone_num = '.$data['phone_num'])->save(array('birth_send_time' => $nextBirth));
                        $birthIngNums += 1;
                    }
                }
            }
            //商户表减积分
            if(M('business')->where('business_id = '.$this->business_id())->setDec('piao',$data['add_piao'] * count($phone_nums))){
                //提交发放记录表，子表用安全id
                M('member_'.$this->safe_business_id())->commit();
                //提交总会员表
                M('member')->commit();
                //提交共享币明细表
                M('piao_log')->commit();
                //提交生日字段
                if($data['check_birth'] == true){
                    if($birthIngNums >= 1){
                        M('member_'.$this->safe_business_id())->commit();
                    }else{
                        M('member_'.$this->safe_business_id())->rollback();
                    }
                }
                //提示
                echo json_encode(array('error' => 0,'msg' => '发放成功！您已被扣除相应共享币'));
                exit;
            }else{
                //回滚发放记录表，子表用安全id
                M('member_'.$this->safe_business_id())->rollback();
                //回滚总会员表
                M('member')->rollback();
                //回滚共享币明细表
                M('piao_log')->rollback();
                //提交生日字段
                if($data['check_birth'] == true){
                    M('member_'.$this->safe_business_id())->rollback();
                }
                //提示
                echo json_encode(array('error' => 1,'msg' => '发放失败'));
                exit;
            }
        }
    }

    //通过excel导入会员
    public function addbyexcel(){
        if(IS_GET){
            $this->display('addbyexcel');
        }else{
            $file = $_FILES['file'];
            
            if(strtolower(end(explode('.',$file['name']))) != 'xls'){
                echo "<script>alert('文件格式错误！必须是xls格式');</script>";
                exit;
            }else{
                $tmpFile = $file['tmp_name'];
                $savePath = 'Public/business/exceltemp/';
                $saveName = time().'.xls';
                if(!is_dir($savePath)){
                    mkdir($savePath,0777,true);
                }
                if(!copy($tmpFile,$savePath.$saveName)){
                    echo "<script>alert('系统出错！文件上传失败');</script>";
                    exit;
                }else{
                    $result = readExcelFile($savePath.$saveName);
                    if(!is_array($result) || count($result) <= 4){
                        echo "<script>alert('系统出错，或文件没填写内容');</script>";
                        exit;
                    }
                    unset($result[1]);unset($result[2]);unset($result[3]);unset($result[4]);
                    echo "<script>alert('开始导入，请勿进行其他操作 ....');</script>";
                    //自身去重
                    $phoneNumResult = array();
                    foreach ($result as $key => $value) {
                        $value[1] = substr(strFilter($value[1]),0,11);
                        if(in_array($value[1],$phoneNumResult)){
                            unset($result[$key]);
                        }else{
                            $phoneNumResult[] = $value[1];
                        }
                    }
                    //数据库去重
                    $allMember = M('member')->getField('phone_num',true);
                    $myMember = M('member_'.$this->business_id())->getField('phone_num',true);
                    //新总会员
                    $allNum = count($result);
                    $stemp = 0;
                    M('member')->startTrans();
                    M('member_'.$this->business_id())->startTrans();
                    foreach ($result as $key => $value) {
                        $d = 25569;
                        $t = 24 * 60 * 60;
                        $data = array(
                            'phone_num' => $value[1],
                            'name' => strFilter($value[0]),
                            'sex' => $value[2] == '男' ? 1 : 0,
                            'add_time' => date('Y-m-d H:i:s'),
                            'join_time' => gmdate('Y-m-d',($value[3]-$d) * $t),
                            'birth_time' => gmdate('Y-m-d',($value[4]-$d) * $t),
                            'level' => $value[5],
                            'is_formal' => 1,
                        );
                        if(!in_array($value[1],$allMember) && !in_array($value[1],$myMember)){
                            M('member')->add($data);
                            M('member_'.$this->business_id())->add($data);
                            $stemp += 1;
                        }elseif(in_array($value[1],$allMember) && !in_array($value[1],$myMember)){
                            M('member_'.$this->business_id())->add($data);
                            $stemp += 1;
                        }
                    }
                    if($stemp >= $allNum){
                        M('member')->commit();
                        M('member_'.$this->business_id())->commit();
                        echo "<script>alert('恭喜，导入完成！请打开会员列表页查看');</script>";
                        unlink($savePath.$saveName);
                        exit;
                    }else{
                        M('member')->rollback();
                        M('member_'.$this->business_id())->rollback();
                        echo "<script>alert('错误，导入失败！系统出错请稍后再试');</script>";
                        unlink($savePath.$saveName);
                        exit;
                    }
                }
            }
        }
    }

    //会员订单
    public function orders()
    {
        $phone_num = $_GET['phone_num'];
        if (empty($phone_num)) {
            $phone_num = 'abcdefghijklmnopqrstuvwxyz';
        }
        $member_name = M('member_'.$this->business_id())->where(array('phone_num'=>$phone_num))->getField('name');
        $where['phone_num'] = $phone_num;
        $where['_string'] = '(business_id='.$this->business_id().' OR sell_business_id='.$this->business_id().')';
        //统计总订单数
        $count = M('order')->where($where)->count();
        if ($_GET['start_time']) {
            $where['add_time'] = array('egt',$_GET['start_time']);
        }
        if ($_GET['end_time']) {
            $where['add_time'] = array('elt',date('Y-m-d',(strtotime($_GET['end_time'])+24*3600)));
        }
        if ($_GET['start_time'] && $_GET['end_time']) {
            $where['add_time'] = array(array('egt',$_GET['start_time']),array('elt',date('Y-m-d',(strtotime($_GET['end_time'])+24*3600))),'and');
        }
        if ($_GET['order_sn']) {
            $where['order_sn'] = $_GET['order_sn'];
        }
        if ($_GET['keywords']) {
            $where['goods_name'] = array('like','%'.$_GET['keywords'].'%');
        }
        //统计条件订单数
        $where_count = M('order')->where($where)->count(); 
        //分页
        $page = new \Think\Page($where_count,$this->step);
        $result = M('order')->where($where)->limit($page->firstRow,$this->step)->order('add_time desc')->select();
        //快递
        $transports = M('transport')->where('transport_id > 0')->select();

        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('where_count',$where_count);
        $this->assign('result',$result);
        $this->assign('business_id',$this->business_id());
        $this->assign('transports',$transports);
        $this->assign('member_name',$member_name);
        $this->display();
    }

    //群发短信
    public function groupSMS(){
        //var_dump($_POST);
        $phone_nums = $_POST['phone_nums'];
        if (count($phone_nums) <= 0) {
            echo json_encode(array('error' => 1,'msg' => '未检测到您选中的手机号'));
            exit;
        }
        //检测手机号是否全部正确
        foreach ($phone_nums as $key => $value) {
            if(!preg_match('/^1[0-9]{10}$/',$value)){
                echo json_encode(array('error' => 1,'msg' => '“ '.$value.' ”不是正确的手机号'));
                exit;
            }
        }
        $sms_models = C('group_SMS_model');
        $search = (int)$_POST['send_info'];
        if (!$sms_models[$search]) {
            echo json_encode(array('error' => 1,'msg' => '您选择的短信模板错误'));
            exit;
        }
        $yupiao = M('business')->where(array('business_id'=>$this->business_id()))->getField('piao');
        if ($yupiao < count($phone_nums)) {
            echo json_encode(array('error' => 1,'msg' => '共享币余额不足'));
            exit;
        }

        //开启事物
        M()->startTrans();
        //扣短信费用
        $kou_piao =  M('business')->where(array('business_id'=>$this->business_id()))->setDec('piao',count($phone_nums));
        if (!$kou_piao) {
            //事物回滚
            M()->rollback();
            echo json_encode(array('error' => 1,'msg' => '发送失败'));
            exit;
        }
        //记录共享币的日志
        $save_data = array(
                        'business_id' => $this->business_id(),
                        'piao' => count($phone_nums),
                        'type' => 2,
                        'description' => '短信通知',
                        'add_time' => date('Y-m-d H:i:s')
                    );
        $piao_log = M('piao_log')->add($save_data);
        if (!$piao_log) {
            //事物回滚
            M()->rollback();
            echo json_encode(array('error' => 1,'msg' => '发送失败'));
            exit; 
        }
        //发送短信
        $phone_num = implode(',',$phone_nums);
        $is_send = sendSMS($phone_num,0,$sms_models[$search]['content']);
        if ($is_send) {
            //提交事物
            M()->commit();
            echo json_encode(array('error' => 0,'msg' => '发送成功'));
            exit;
        }else{
            //事物回滚
            M()->rollback();
            echo json_encode(array('error' => 1,'msg' => '发送失败'));
            exit;
        }
        

    }
}