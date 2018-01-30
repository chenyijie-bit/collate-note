<?php
namespace Business\Controller;

class GoodsController extends CommonController {

	//优先执行
    protected function _initialize()
    {
        //全局限制是否是本人商品ID的判断
        $goods_id = I('goods_id');
        $action_name = array('exchange','exchange_detail','updata_agent','extend'); //共享商品、详情、取消代理 除外
        if(!empty($goods_id) && !in_array(ACTION_NAME,$action_name)){
            $isHave = M('goods')->where(array('goods_id' => $goods_id,'business_id' => $this->business_id()))->find();
            if(!$isHave){
                echo json_encode(array('error' => 1,'msg' => '非法的参数'));
                exit;
            }
        }
    }
    
    //商品列表
    public function lists()
    {   
        //条件
        $where = 'business_id = '.$this->business_id().' and is_del = 0';
    	//统计商品
    	$count = M('goods')->where($where)->count();
    	//基础语句
        $sql = "select * from goods where ".$where;
        //类型
        if(I('get.goods_type_id')){
            $goods_type_id = I('get.goods_type_id');
            $sql .= " and goods_type_id = {$goods_type_id}";
            $where .= " and goods_type_id = {$goods_type_id}";
        }
        //共享
        if(I('get.is_gx')){
            $is_gx = I('get.is_gx');
            $sql .= " and is_gx = {$is_gx}";
            $where .= " and is_gx = {$is_gx}";
        }
        //上架
        if(I('get.status')){
            $status = I('get.status');
            $sql .= " and status = {$status}";
            $where .= " and status = {$status}";
        }
        //关键字
        if(I('get.keywords')){
            $keywords = I('get.keywords');
            $sql .= " and goods_name like '%{$keywords}%'";
            $where .= " and goods_name like '%{$keywords}%'";
        }
        //拼排序
        $sql .= " order by add_time desc";
        //统计商品
        $where_count = M('goods')->where($where)->count();
        //拼分页
        $page = new \Think\Page($where_count,$this->step);
        $sql .= ' limit '.$page->firstRow.','.$this->step;
        $goods = M('goods')->query($sql);
    	//商品类型
    	$goods_type = M('goods_type')->select();
        //所有分组
        $groups = M('group')->where(array('business_id'=>$this->business_id(),'is_del'=>1))->select();
        //所有商品及其分组
        $goods_ids = array(0);
        foreach ($goods as $key => $value) {
            $goods_ids[] = $value['goods_id'];
        }
        $goods_groups = M('group_goods')->where(array('goods_id'=>array('in',$goods_ids)))->select();
        foreach ($goods_groups as $k => $v) {
            $new_goods_groups[$v['goods_id']][] = $v['group_id'];
        }
        $existing_groups = array();
        foreach ($new_goods_groups as $key => $value) {
            $existing_groups[$key] = array_unique($new_goods_groups[$key]);
        }

    	$this->assign('goods',$goods);
    	$this->assign('goods_type',$goods_type);
    	$this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('where_count',$where_count);
    	$this->assign('groups',$groups);
        $this->assign('existing_groups',$existing_groups);
        $this->display();
    }

    //商品编辑
    public function edit()
    {
        if (!I('goods_id','get')) {
            echo '<script>alert("参数错误！");window.location.href="/Business/Goods/lists";</script>';
            exit;
        }
        if (IS_POST) {
            $data = $_POST;
            $goods_id = $_POST['goods_id'];
            unset($data['goods_id']);
            $_array = array(
                  "goods_name" => "未填写商品名称",
                  "imgs" => "必须上传封面主图",
                  "description" => "未填写商品描述",
                  "goods_type_id" => "未选择商品类型",
                  "piao" => "未填写商品价格",
                  "nums" => "未填写商品库存",
                  "rebate" => "未填写商品反利百分比",
                  "status" => "未选择商品上下架状态",
                  "is_gx" => "未选择商品是否共享"
                );
            if (empty($data['imgs']['0'])) {
                echo json_encode(array('error' => 1,'msg' => $_array['imgs']));
                exit;
            }
            foreach ($data as $key => $value) {
                if (empty($value) && $key != 'imgs') {
                    echo json_encode(array('error' => 1,'msg' => $_array[$key]));
                    exit;
                }
                
                if($key == 'imgs'){
                    $data['pics'] = $data['imgs']['0'];
                    unset($data['imgs']['0']);
                    if (count($data['imgs'])>0) {
                        $data['pics'] .= ';'.implode(';',$data['imgs']);
                    }
                    unset($data['imgs']);
                }
                
            }
            $data['business_id'] = $this->business_id();
            $data['is_del'] = 0;
            $data['rebate'] = $data['rebate'] / 100;
            $data['area_id'] = session('business')['area_id'];
            $data['city_id'] = session('business')['city_id'];
            $data['province_id'] = session('business')['province_id'];
            $result = M('goods')->where(array('goods_id'=>$goods_id))->save($data);
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '商品保存成功！'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '商品保存失败！'));
                exit; 
            }
            exit;
        }
        $goods_types = M('goods_type')->select();
        $goods_info = M('goods')->where(array('goods_id'=>I('goods_id','get')))->find();
        $this->assign('goods_types',$goods_types);
        $this->assign('goods_info',$goods_info);
        $this->display();
    }

    //上架/下架
    public function updateStatus()
    {
        if (IS_POST) {
            $goods_id = I('goods_id','post');
            $status = I('status','post');
            if (!$goods_id || ($status != 1 && $status != 2)) {
                echo json_encode(array('error' => 1,'msg' => '商品参数错误！'));
                exit;
            }
            if ($status == 1) {
                $data = array('status' => $status);
            }else{
                $data = array(
                    'status' => $status,
                    'is_gx' => 2,
                    'is_jian' => 2
                );
            }
            $result = M('goods')->where(array('goods_id' => $goods_id))->save($data);
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => ''));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => $status==1?'商品上架失败！':'商品下架失败！'));
                exit;
            }
        }
    }

    //商品添加
    public function add()
    {
        if (IS_POST) {
            $data = $_POST;
            
            $_array = array(
                  "goods_name" => "未填写商品名称",
                  "imgs" => "必须上传封面主图",
                  "description" => "未填写商品描述",
                  "goods_type_id" => "未选择商品类型",
                  "piao" => "未填写商品价格",
                  "nums" => "未填写商品库存",
                  "rebate" => "未填写商品反利百分比",
                  "status" => "未选择商品上下架状态",
                  "is_gx" => "未选择商品是否共享"
                );
            if (empty($data['imgs']['0'])) {
                echo json_encode(array('error' => 1,'msg' => $_array['imgs']));
                exit;
            }
            foreach ($data as $key => $value) {
                if (empty($value) && $key != 'imgs') {
                    echo json_encode(array('error' => 1,'msg' => $_array[$key]));
                    exit;
                }
                
                if($key == 'imgs'){
                    $data['pics'] = $data['imgs']['0'];
                    unset($data['imgs']['0']);
                    if (count($data['imgs'])>0) {
                        $data['pics'] .= ';'.implode(';',$data['imgs']);
                    }
                    unset($data['imgs']);
                }
                
            }
            $data['business_id'] = $this->business_id();
            $data['is_del'] = 0;
            $data['rebate'] = $data['rebate'] / 100;
            $data['add_time'] = date('Y-m-d H:i:s',time());
            $data['area_id'] = session('business')['area_id'];
            $data['city_id'] = session('business')['city_id'];
            $data['province_id'] = session('business')['province_id'];
            $result = M('goods')->add($data);
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '商品添加成功！'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '商品添加失败！'));
                exit; 
            }
            exit;
        }
        $goods_types = M('goods_type')->select();
        $this->assign('goods_types',$goods_types);
        $this->display();
    }

    //商品添加图片
    public function add_imgs()
    {
        if (IS_POST) {
            $base64_data = $_POST['data'];
            if (empty($base64_data)) {
                echo json_encode(array('error' => 1,'msg' => '请选择图片'));
                exit;
            }
            if ($base64_data == base64_encode(base64_decode($base64_data))) {
                echo json_encode(array('error' => 1,'msg' => '图片格式错误'));
                exit;
            }
            //  $base_img是获取到前端传递的src里面的值，也就是我们的数据流文件
            //$base_img = str_replace('data:image/jpeg;base64,', '', $base64_data);
            $base_img = explode(',', $base64_data)[1];
            //  设置文件路径和文件名称
            $_input['path'] = 'Public/business/goods/'.$this->business_id().'/'.date('Y_m_d',time());
            $_input['save_name'] = chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . date('Ymd') . substr(microtime(),2,6) . '.jpg';
            $path = $_input['path'].'/'.$_input['save_name'];
            //  创建将数据流文件写入我们创建的文件内容中
            if (!is_dir($path)){  
                //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
                $res=mkdir($_input['path'],0777,true);
            }
            $file_path['result'] = file_put_contents($path, base64_decode($base_img));
            if ($file_path['result']) {
                echo json_encode(array('error' => 0,'file_path' => '/'.$path));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '图片上传失败'));
                exit;
            }
            exit;
        }
    }

    //商品删除
    public function delete()
    {   
        if (IS_POST) {
            $goods_id = I('goods_id','post');
            if (!$goods_id) {
                echo json_encode(array('error' => 1,'msg' => '商品参数错误！'));
                exit;
            }
            $result = M('goods')->where(array('goods_id' => $goods_id))->setField('is_del','1');
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '删除成功！'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '删除失败！'));
                exit;
            }
        }
    }

    //商品推广
    public function extend()
    {   
        //本功能已经在前端实现
        $this->display();
    }

    //共享商品池
    public function exchange()
    {   
        if(IS_GET){
            //省市区
            foreach (M('province')->select() as $key => $value) {
                $provinces[$value['province_id']] = $value['province_name'];
            }
            if(I('get.province_id')){
                foreach (M('city')->where('province_id = '.$_GET['province_id'])->select() as $key => $value) {
                    $citys[$value['city_id']] = $value['city_name'];
                }
            }
            if(I('get.city_id')){
                foreach (M('area')->where('city_id = '.$_GET['city_id'])->select() as $key => $value) {
                    $areas[$value['area_id']] = $value['area_name'];
                }
            }
            //条件
            $where = 'is_del = 0 and is_gx = 1 and status = 1';
            $where1 = 'is_del = 0 and is_gx = 1 and status = 1';
            if (I('get.business_id')) {
                $business_id = I('get.business_id');
                $where .= " and business_id = {$business_id}";
            }
            //统计商品
            $count = M('goods')->where($where)->count();
            //基础语句
            $sql = "select * from goods where ".$where;
            //类型
            if(I('get.goods_type_id')){
                $goods_type_id = I('get.goods_type_id');
                $sql .= " and goods_type_id = {$goods_type_id}";
                $where1 .= " and goods_type_id = {$goods_type_id}";
            }
            //省
            if(I('get.province_id')){
                $province_id = I('get.province_id');
                $sql .= " and province_id = {$province_id}";
                $where1 .= " and province_id = {$province_id}";
            }
            //市
            if(I('get.city_id')){
                $city_id = I('get.city_id');
                $sql .= " and city_id = {$city_id}";
                $where1 .= " and city_id = {$city_id}";
            }
            //区
            if(I('get.area_id')){
                $area_id = I('get.area_id');
                $sql .= " and area_id = {$area_id}";
                $where1 .= " and area_id = {$area_id}";
            }
            //关键字
            if(I('get.keywords')){
                $keywords = I('get.keywords');
                $sql .= " and goods_name like '%{$keywords}%'";
                $where1 .= " and goods_name like '%{$keywords}%'";
            }
            //价格
            if (I('get.price')) {
                $prices = explode('~',I('get.price'));
                $prices[0] = (int)($prices[0]);
                $prices[1] = (int)($prices[1]);
                $sql .= " and piao >= {$prices[0]}";
                $where1 .= " and piao >= {$prices[0]}";
                if ($prices[1]) {
                    $sql .= "  and piao <= {$prices[1]}";
                    $where1 .= "  and piao <= {$prices[1]}";
                }
            }
            //商户id
            if (I('get.business_id')) {
                $business_id = I('get.business_id');
                $sql .= " and business_id = {$business_id}";
                $where1 .= " and business_id = {$business_id}";
            }
            //拼排序
            $sql .= " order by add_time desc";
            //统计条件商品
            $where_count = M('goods')->where($where1)->count();
            //拼分页
            $page = new \Think\Page($where_count,12);
            $sql .= ' limit '.$page->firstRow.',12';
            $goods = M('goods')->query($sql);
            //商品类型
            $goods_type = M('goods_type')->select();
            //是否已代理
            $where = array('business_id' => $this->business_id());
            $haveAgents = M('agent')->where($where)->select();
            foreach ($haveAgents as $key => $value) {
                $myAgents[] = $value['goods_id'];
            }
            foreach ($goods as $k => $v) {
                $goods[$k]['is_agent'] =  in_array($v['goods_id'],$myAgents) ? 1 : 0;
                $goods[$k]['is_me'] =  $v['business_id'] == $this->business_id() ? 1 : 0;
            }
            if (I('get.business_id')) {
                $exchange_name = M('business')->where(array('business_id'=>I('get.business_id')))->getField('exchange_name');
            }
            $this->assign('goods',$goods);
            $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
            $this->assign('count',$count);
            $this->assign('goods_type',$goods_type);
            $this->assign('provinces',$provinces);
            $this->assign('citys',$citys);
            $this->assign('areas',$areas);
            $this->assign('exchange_name',$exchange_name);
            $this->display();
        }elseif(IS_POST){
            $post_id = I('post.goods_id');
            if(M('agent')->where(array('business_id' => $this->business_id(),'goods_id' => $post_id))->count()){
                echo json_encode(array('error' => 1,'msg' => '数据错误！您已经代理过'));
                exit;
            }
            $data = array(
                'business_id' => $this->business_id(),
                'goods_id' => $post_id,
                'add_time' => date('Y-m-d H:i:s')
            );
            if(M('agent')->add($data)){
                echo json_encode(array('error' => 0,'msg' => '代理此商品成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '代理此商品失败'));
                exit;
            }
        }
    }

    //共享商品详情
    public function exchange_detail()
    {   
        if (!I('goods_id','get')) {
            echo '<script>alert("参数错误！");window.location.href="/Business/Goods/lists";</script>';
            exit;
        }
        $goods_info = M('goods')->where(array('goods_id'=>I('goods_id','get')))->find();
        $goods_info['goods_type_name'] = $goods_types = M('goods_type')->where(array('goods_type_id'=>$goods_info['goods_type_id']))->getField('goods_type_name');
        $goods_business = M('business')->where(array('business_id'=>$goods_info['business_id']))->find();
        $goods_info['company_name'] = $goods_business['company_name'];
        $goods_info['exchange_name'] = $goods_business['exchange_name'];
        $goods_info['phone_num'] = $goods_business['phone_num'];
        $goods_info['pics'] = explode(';',$goods_info['pics']);
        $where = array('business_id' => $this->business_id(),'goods_id'=>I('goods_id','get'));
        $goods_info['is_agent'] = M('agent')->where($where)->select()?1:0;
        $this->assign('goods_info',$goods_info);
        $this->assign('province',M('province')->find($goods_info['province_id'])['province_name']);
        $this->assign('city',M('city')->find($goods_info['city_id'])['city_name']);
        $this->assign('area',M('area')->find($goods_info['area_id'])['area_name']);
        $this->assign('this_business_id',$this->business_id());
        $this->display();
    }

    //我的商城信息
    public function myexchangeinfo()
    {   
        //我的商品数
        $goodNum = M('goods')->where(array('business_id' => $this->business_id(),'is_del' => 0))->count();
        //我代理的商品数
        $agentNum = M('agent')->where(array('business_id' => $this->business_id()))->count();
        //共享商品数
        $gxNum[1] = M('goods')->where(array('business_id' => $this->business_id(),'is_del' => 0,'is_gx' => 1))->count();
        $gxNum[2] = $goodNum - $gxNum[1];
        //上架商品数
        $statusNum[1] = M('goods')->where(array('business_id' => $this->business_id(),'is_del' => 0,'status' => 1))->count();
        $statusNum[2] = $goodNum - $statusNum[1];
        //商品类别数
        $goods_type = M('goods_type')->select();
        $goodsTypesOld = M('goods')->field('goods_type_id')->where(array('business_id' => $this->business_id(),'is_del' => 0))->group('goods_type_id')->select();
        $goodsType['name'] = "";
        $goodsType['value'] = "";
        foreach ($goodsTypesOld as $key => $value) {
            $goodsType['name'] .= '"'.$goods_type[$value['goods_type_id'] - 1]['goods_type_name'].'",';
            $goodsType['value'] .= '"'.M('goods')->where(array('business_id' => $this->business_id(),'is_del' => 0,'goods_type_id' => $value['goods_type_id']))->count().'",';
        }
        $this->assign('goodNum',$goodNum);
        $this->assign('agentNum',$agentNum);
        $this->assign('gxNum',$gxNum);
        $this->assign('statusNum',$statusNum);
        $this->assign('goodsType',$goodsType);
        $this->assign('business_id',$this->business_id());
        $this->display();
    }

    //我代理的商品
    public function myagent()
    {
        $where = array('business_id' => $this->business_id());
        $haveAgents = M('agent')->where($where)->select();
        foreach ($haveAgents as $key => $value) {
            $myAgents[] = $value['goods_id'];
            $jian_array[$value['goods_id']] = $value['is_jian'];
        }
        if (count($myAgents)<1) {
            $myAgents[] = 0;
        }
        //条件
        $where = 'goods_id in ('.implode(',',$myAgents).') and business_id != '.$this->business_id().' and is_del = 0';
        //统计商品
        $count = M('goods')->where($where)->count();
        //基础语句
        $sql = "select * from goods where ".$where;
        //类型
        if(I('get.goods_type_id')){
            $goods_type_id = I('get.goods_type_id');
            $sql .= " and goods_type_id = {$goods_type_id}";
            $where .= " and goods_type_id = {$goods_type_id}";
        }
        //上架
        if(I('get.status')){
            $status = I('get.status');
            $sql .= " and status = {$status}";
            $where .= " and status = {$status}";
        }
        //关键字
        if(I('get.keywords')){
            $keywords = I('get.keywords');
            $sql .= " and goods_name like '%{$keywords}%'";
            $where .= " and goods_name like '%{$keywords}%'";
        }
        //拼排序
        $sql .= " order by add_time desc";
        //统计商品
        $where_count = M('goods')->where($where)->count();
        //拼分页
        $page = new \Think\Page($where_count,$this->step);
        $sql .= ' limit '.$page->firstRow.','.$this->step;
        $goods = M('goods')->query($sql);
        //商品类型
        $goods_type = M('goods_type')->select();

        //所有分组
        $groups = M('group')->where(array('business_id'=>$this->business_id(),'is_del'=>1))->select();
        //所有商品及其分组
        $goods_ids = array(0);
        foreach ($goods as $key => $value) {
            $goods_ids[] = $value['goods_id'];
        }
        $goods_groups = M('group_goods')->where(array('goods_id'=>array('in',$goods_ids)))->select();
        foreach ($goods_groups as $k => $v) {
            $new_goods_groups[$v['goods_id']][] = $v['group_id'];
        }
        $existing_groups = array();
        foreach ($new_goods_groups as $key => $value) {
            $existing_groups[$key] = array_unique($new_goods_groups[$key]);
        }
        $this->assign('goods',$goods);
        $this->assign('goods_type',$goods_type);
        $this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
        $this->assign('count',$count);
        $this->assign('where_count',$where_count);
        $this->assign('groups',$groups);
        $this->assign('existing_groups',$existing_groups);
        $this->assign('jian_array',$jian_array);
        $this->display();
    }

    //取消代理商品
    public function updata_agent()
    {
        $goods_id = I('goods_id','post');
        if (!$goods_id) {
            echo json_encode(array('error' => 1,'msg' => '商品参数错误！'));
            exit;
        }
        $result = M('agent')->where(array('goods_id' => $goods_id))->delete();
        if (is_int($result)) {
            echo json_encode(array('error' => 0,'msg' => '取消代理成功！'));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '取消代理失败！'));
            exit;
        }
    }

    //推荐、取消推荐代理商品
    public function agent_jian()
    {
        if (empty($this->business_id()) || empty($_POST['good_id']) || empty($_POST['is_jian'])) {
            echo json_encode(array('error' => 1,'msg' => '参数错误！'));
            exit;
        }else{
            if ($_POST['is_jian'] == 1) {
                $str = '推荐';
            }else{
                $str = '取消推荐';
            }
            $result = M('agent')->where(array('business_id'=>$this->business_id(),'goods_id'=>$_POST['good_id']))->setField('is_jian',$_POST['is_jian']);
            if (is_int($result)) {
                echo json_encode(array('error' => 0,'msg' => $str.'成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => $str.'失败'));
                exit;
            }
        }
    }
    //分组列表
    public function group_list()
    {
        $where = array(
                'business_id' => $this->business_id(),
                'is_del' => 1
            );
        $count = M('group')->where($where)->count();
        if ($_GET['keywords']) {
            $where['group_name'] = array('like','%'.$_GET['keywords'].'%');
        }
        
        $groups = M('group')->where($where)->select();
        $where_count = count($groups);
        //总商品种类
        $sql = 'select group_id,count(*) as count from group_goods where business_id = '.$this->business_id().' group by group_id';
        $result = M()->query($sql);
        $all_count = array();
        foreach ($result as $key => $value) {
            $all_count[$value['group_id']] = $value['count'];
        }
        //我的商品种类
        $sql1 = 'select gg.group_id,count(*) as count from group_goods gg,goods g where g.business_id = '.$this->business_id().' and g.goods_id=gg.goods_id and gg.business_id='.$this->business_id().' group by gg.group_id';
        $result1 = M()->query($sql1);
        $my_count = array();
        foreach ($result1 as $key => $value) {
            $my_count[$value['group_id']] = $value['count'];
        }
        //我的代理商品种类
        $sql2 = 'select gg.group_id,count(*) as count from group_goods gg,goods g where g.business_id<>'.$this->business_id().' and g.goods_id=gg.goods_id and gg.business_id='.$this->business_id().' group by gg.group_id';
        $result2 = M()->query($sql2);
        $other_count = array();
        foreach ($result2 as $key => $value) {
            $other_count[$value['group_id']] = $value['count'];
        }
        $this->assign('all_count',$all_count);
        $this->assign('my_count',$my_count);
        $this->assign('other_count',$other_count);
        $this->assign('groups',$groups);
        $this->assign('count',$count);
        $this->assign('where_count',$where_count);
        $this->display();
    }

    //分组详情
    public function group_detail()
    {
        if (empty($_GET['group_id'])) {
            $this->display();
        }else{
            $group_goods = M('group_goods')->where(array('group_id'=>$_GET['group_id'],'business_id'=>$this->business_id()))->select();
            $goods_ids = array();
            foreach ($group_goods as $key => $value) {
                $goods_ids[] = $value['goods_id'];
            }
            $goods_ids_str = implode(',',$goods_ids);
            if (empty($goods_ids_str)) {
                $goods_ids_str = '0';
            }
            //条件
            $where = 'is_del = 0 and goods_id in ('.$goods_ids_str.')';
            //统计商品
            $count = M('goods')->where($where)->count();
            //基础语句
            $sql = "select * from goods where ".$where;
            //类型
            if(I('get.goods_type_id')){
                $goods_type_id = I('get.goods_type_id');
                $sql .= " and goods_type_id = {$goods_type_id}";
                $where .= " and goods_type_id = {$goods_type_id}";
            }
            //共享
            if(I('get.is_gx')){
                $is_gx = I('get.is_gx');
                $sql .= " and is_gx = {$is_gx}";
                $where .= " and is_gx = {$is_gx}";
            }
            //上架
            if(I('get.status')){
                $status = I('get.status');
                $sql .= " and status = {$status}";
                $where .= " and status = {$status}";
            }
            //关键字
            if(I('get.keywords')){
                $keywords = I('get.keywords');
                $sql .= " and goods_name like '%{$keywords}%'";
                $where .= " and goods_name like '%{$keywords}%'";
            }
            //拼排序
            $sql .= " order by add_time desc";
            //统计商品
            $where_count = M('goods')->where($where)->count();
            //拼分页
            $page = new \Think\Page($where_count,$this->step);
            //$sql .= ' limit '.$page->firstRow.','.$this->step;
            $goods = M('goods')->query($sql);
            //商品类型
            $goods_type = M('goods_type')->select();

            $this->assign('goods',$goods);
            $this->assign('goods_type',$goods_type);
            //$this->assign('page',array('page' => $page->show(),'firstRow' => $page->firstRow));
            $this->assign('count',$count);
            $this->assign('where_count',$where_count);
            $this->assign('business_id',$this->business_id());
            $this->display();
        }
    }

    //添加分组
    public function add_group()
    {
        if (IS_POST) {
            if (empty($_POST['group_name'])) {
                echo json_encode(array('error' => 1,'msg' => '请填写分组名称'));
                exit;
            }
            $is_repeat = M('group')->where(array('group_name'=>$_POST['group_name'],'business_id'=>$this->business_id(),'is_del'=>1))->find();
            if ($is_repeat) {
                echo json_encode(array('error' => 1,'msg' => '分组名称已存在'));
                exit;
            }
            $result = M('group')->add(array('group_name'=>$_POST['group_name'],'business_id'=>$this->business_id(),'add_time'=>date('Y-m-d H:i:s')));
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '添加分组成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '添加分组失败'));
                exit;
            }
        }
    }

    //从分组中删除商品
    public function group_goods_delete()
    {
        if (!empty($_POST['group_id']) && !empty($_POST['good_id'])) {
            $result = M('group_goods')->where(array('group_id'=>$_POST['group_id'],'goods_id'=>$_POST['good_id'],'business_id'=>$this->business_id()))->delete();
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '删除分组成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '删除分组失败'));
                exit;
            }
        }else{
            echo json_encode(array('error' => 1,'msg' => '参数错误！'));
            exit; 
        }
    }
    //删除分组
    public function group_del()
    {
        if (!empty($_POST['group_id'])) {
            $result = M('group')->where(array('group_id'=>$_POST['group_id']))->setField('is_del',2);
            if ($result) {
                echo json_encode(array('error' => 0,'msg' => '删除分组成功'));
                exit;
            }else{
                echo json_encode(array('error' => 1,'msg' => '删除分组失败'));
                exit;
            }
        }else{
            echo json_encode(array('error' => 1,'msg' => '参数错误！'));
            exit; 
        }
    }
    //推广分组
    public function group_extend()
    {
        $group_name = M('group')->where(array('group_id'=>$_GET['group_id']))->getField('group_name');
        $count = M('group_goods')->distinct(true)->where(array('group_id'=>$_GET['group_id'],'business_id'=>$this->business_id()))->field('goods_id')->count();
        $this->assign('group_name',$group_name);
        $this->assign('count',$count);
        $this->display();
    }

    //分配商品到分组
    public function set_group()
    {
        $goods_id = $_POST['good_id'];
        $group_id = $_POST['group_id'];
        if (empty($goods_id) || empty($group_id)) {
            echo json_encode(array('error' => 1,'msg' => '参数错误！'));
            exit;
        }
        $data = array(
                'goods_id' => $goods_id,
                'group_id' => $group_id,
                'business_id' => $this->business_id(),
                'add_time' => date('Y-m-d H:i:s')
            );
        $result = M('group_goods')->add($data);
        if ($result) {
            echo json_encode(array('error' => 0,'msg' => '加入分组成功'));
            exit;
        }else{
            echo json_encode(array('error' => 1,'msg' => '加入分组失败'));
            exit;
        }
    }
}