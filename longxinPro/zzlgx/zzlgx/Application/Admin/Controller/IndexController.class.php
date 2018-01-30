<?php
namespace Admin\Controller;

class IndexController extends CommonController {
    
    //后台首页
    public function index()
    {
        //查询企业基本信息
        $info = M('admin')->find($this->admin_id());
        $this->assign('info',$info);
        $this->display();
    }

    //后台默认缺省页
    public function index_base()
    {
        $this->display();
    }
}