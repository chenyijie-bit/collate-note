<?php
namespace Admin\Controller;

class SystemController extends CommonController {

    //系统管理
    public function system_mange()
    {   
        if(IS_GET){
            $result = M('system_config')->select();
            $this->assign('result',$result[0]);
            $this->display();
        }elseif(IS_POST){
            $data = $_POST;
            $sql = <<<SAVE
            update system_config set activate_price = {$data['activate_price']}, reward_piao = {$data['reward_piao']}, security = {$data['security']}, poundage = {$data['poundage']}
SAVE;
            $affected_rows = M()->execute($sql);
            if(is_int($affected_rows)){
                echo json_encode(array('error' => 0,'msg' => '保存成功'));
                exit;  
            }else{
                echo json_encode(array('error' => 1,'msg' => '保存失败'));
                exit;
            }
        }
    }

}