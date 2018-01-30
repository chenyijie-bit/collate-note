<?php
namespace Home\Controller;

class IndexController extends CommonController {
    
    public function index()
    {
        //官网首页，暂时跳转到商户后台
        header('location:/business');
    }

}