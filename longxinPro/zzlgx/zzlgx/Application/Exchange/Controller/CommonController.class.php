<?php

namespace Exchange\Controller;

use Think\Controller;

class CommonController extends Controller {

    //优先执行
    protected function _initialize()
    {
        //必须获得商户id简写b，或者商品id简写g，或者分组id简写t，写在父类里
        if(isset($_GET['b'])){
            if(M('business')->find($_GET['b'])){
                unset($_SESSION['exchange']['group_id']);
                unset($_SESSION['exchange']['goods_id']);
                $_SESSION['exchange']['business_id'] = $_GET['b'];
                if (isset($_GET['d'])) {
                    $_SESSION['exchange']['goods_id'] = $_GET['d'];
                }
            }else{
                header('location:/exchange?b=10001');
                exit;
            }
        }
        if(isset($_GET['t'])){
            if(M('group')->find($_GET['t'])){
                unset($_SESSION['exchange']['goods_id']);
                unset($_SESSION['exchange']['business_id']);
                $_SESSION['exchange']['group_id'] = $_GET['t'];
            }else{
                header('location:/exchange?b=10001');
                exit;
            }
        }
        if(isset($_GET['g'])){
            if(M('goods')->find($_GET['g'])){
                unset($_SESSION['exchange']['group_id']);
                unset($_SESSION['exchange']['business_id']);
                $_SESSION['exchange']['goods_id'] = $_GET['g'];
            }else{
                header('location:/exchange?b=10001');
                exit;
            }
        }
        if (empty($_SESSION['exchange']['member_name']) && $_SESSION['exchange']['phone_num']) {
            header('location:/exchange/log/completion');
            exit;
        }

        if (empty($_SESSION['exchange']['pay_pass']) && $_SESSION['exchange']['phone_num']) {
            header('location:/exchange/log/pay_pass');
            exit;
        }
    }
    
    //获取会员手机号
    protected function phone_num()
    {
        return $_SESSION['exchange']['phone_num'] ? $_SESSION['exchange']['phone_num'] : '';
    }

    //获取当前商户id
    protected function business_id()
    {   
        return $_SESSION['exchange']['business_id'];
        exit;
    }

    //获取商品组id
    protected function group_id()
    {   
        return $_SESSION['exchange']['group_id'];
        exit;
    }

    //获取链接中商品id
    protected function goods_id()
    {   
        return $_SESSION['exchange']['goods_id'];
        exit;
    }

}