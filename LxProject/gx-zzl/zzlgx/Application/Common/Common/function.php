<?php

//发送CURL请求
function CurlRequest($url,$https=true,$method='get',$data=null,$header=array()){
	//1.初始化url
	$ch = curl_init($url);
	//2.设置相关的参数
	//字符串不直接输出,进行一个变量的存储
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//判断是否为https请求
	if($https === true){
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	}
	//判断是否为post请求
	if($method == 'post'){
	  curl_setopt($ch, CURLOPT_POST, true);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	//判断是否为delete请求
	if($method == 'delete'){
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	//判断是否有http头参数
	if(!empty($header)){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	//3.发送请求
	$str = curl_exec($ch);
	//4.关闭连接
	curl_close($ch);
	//返回请求到的结果
	return $str;
}

//发送短信
/*
  $phone_num为手机号，$smsCode为短信验证码，$content为发送的内容(验证码为“0”时，$content才有效)
*/
function sendSMS($phone_num,$smsCode,$content=""){
	//检测
    if ($smsCode != 0) {
        if(!preg_match('/^1[0-9]{10}$/',$phone_num)){
            echo json_encode(array('error' => 1,'msg' => '请输入正确的手机号'));
            exit;
        }
    }
    
    //发送
	$url = 'http://qxt.1166.com.cn/qxtsys/recv_center';
    $CpName = 'lxzb_yzm';
    $CpPassword = 'aa123456';
    if ($smsCode == 0) {
        $content = "【在这里】".$content;
    }else{
        $content = "【在这里】您的验证码为：{$smsCode}，有效期5分钟。";
    }
    $data = 'CpName='.$CpName.'&CpPassword='.$CpPassword.'&DesMobile='.$phone_num.'&Content='.$content;
    $res = json_decode(CurlRequest($url,false,'post',$data),true);
    //判断
    if(isset($res['smsid']) && $res['code'] == 0){
        return true;
    }else{
        return false;
    }
}

//单文件上传
/*
    上传文件参数array(
            'file' => $_FILES,//可写可不写
            'type' => 'jpg,gif,png,jpeg',//文件类型，用英文逗号“,”分隔
            'path' => 'data/default/update/imgs',//文件的保存路径，相对于网站根目录的相对路径
            'size' => '1024',//文件大小，KB为单位
            'save_name' => '',//文件保存名称
    );
*/
function upload_file($array=array())
{
    $array['file'] = empty($array['file']) ? reset($_FILES) : $array['file'];
    $array['type'] = empty($array['type']) ? 'jpg,gif,png,jpeg' : strtolower($array['type']);
    $array['path'] = empty($array['path']) ? 'upload' : trim(trim($array['path'], '/'),'\\');
    $array['size'] = empty($array['size']) ? 1024 : (int)$array['size'];
    $array['save_name'] = empty($array['save_name']) ? chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . date('Ymd') . substr(microtime(),2,6) . '.' . end(explode('.',strtolower($array["file"]['name']))) : $array['save_name'] . '.' . end(explode('.',strtolower($array["file"]['name'])));
    $file = $array['file'];
    if (empty($file["name"])) 
    {
        return array('result'=>0,'msg'=>'没有上传文件');die;
    }

    if (!strstr($array['type'],end(explode('.',strtolower($file["name"]))))) 
    {
        return array('result'=>0,'msg'=>'文件类型错误！');die;
    }

    if ($file["size"] > $array['size'] * 1024) 
    {
    	if ($array["size"] >= 1024) {
    		return array('result'=>0,'msg'=>'上传文件不得超过' . ($array['size']/1024) . 'MB');die;
    	}else{
    		return array('result'=>0,'msg'=>'上传文件不得超过' . $array['size'] . 'KB');die;
    	}
    }

    if (file_exists($array['path']))
    {
        move_uploaded_file($file["tmp_name"],$array['path'] . "/" . $array["save_name"]);
        return (array('result' => 1,'file_path' => "/" . $array['path'] . "/" . $array["save_name"]));
    }else{
        if(mkdir($array['path'],0777,true))
        {
            move_uploaded_file($file["tmp_name"],$array['path'] . "/" . $array["save_name"]);
            return array('result' => 1,'file_path' => "/" . $array['path'] . "/" . $array["save_name"]);
        }else{
            return array('result' => 0,'msg'=>"/" . $array['path'] . ' 目录创建失败！');
        }
    }
}

//读取excel文件返回内容
function readExcelFile($filename){
    /** Error reporting */
    error_reporting(E_ALL);
    /** PHPExcel */
    require_once 'Public/plugin/phpexcel/Classes/PHPExcel.php';
    /** Action */
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($filename);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $highestRow = $objWorksheet->getHighestRow();
    $highestColumn = $objWorksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    $excelData = array();
    for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 0; $col < $highestColumnIndex; $col++) { 
             $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
    }
    return $excelData;
}

//多维数组排序
function sortArrByField(&$array, $field, $desc = false){
  $fieldArr = array();
  foreach ($array as $k => $v) {
    $fieldArr[$k] = $v[$field];
  }
  $sort = $desc == false ? SORT_ASC : SORT_DESC;
  array_multisort($fieldArr, $sort, $array);
}

//生成唯一订单号
function getOrderSN(){
    $timestamps = explode(' ',microtime());
    list($msec,$sec) = $timestamps;
    $msec = substr($msec,2);
    return $sec.$msec;
}

//垃圾龙信提供的接口传输加密方法
function quantum_encode($source, $key)
{
    $result = array();
    $random = md5(time());
    $source = urlencode($source);
    //des 加密
    $value = authcode($source, 'ENCODE', $random);
    $result['value'] = $value;
    //rsa加密
    $encrypt = '';
    openssl_public_encrypt($random, $encrypt, $key);
    $encrypt = base64_encode($encrypt);
    $result["key"] = $encrypt;
    return $result;
}

//垃圾龙信提供的接口传输解密方法
function quantum_decode($result, $key)
{
    $_key = $result->key;
    $_value = $result->value;
    $_key = base64_decode($_key);
    //rsa解密
    $decrypt = '';
    openssl_public_decrypt($_key, $decrypt, $key);
    //des解密
    $_value = authcode($_value, 'DECODE', $decrypt);
    return urldecode($_value);
}

//垃圾龙信提供的啥鸡巴玩意方法
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;   
       
    $keya = md5(substr($key, 0, 16));   
    $keyb = md5(substr($key, 16, 16));   
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';   
      
    $cryptkey = $keya.md5($keya.$keyc);   
    $key_length = strlen($cryptkey);   
      
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;   
    $string_length = strlen($string);   
      
    $result = '';   
    $box = range(0, 255);   
      
    $rndkey = array();   
    for($i = 0; $i <= 255; $i++) {   
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);   
    }   
      
    for($j = $i = 0; $i < 256; $i++) {   
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;   
        $tmp = $box[$i];   
        $box[$i] = $box[$j];   
        $box[$j] = $tmp;   
    }   
      
    for($a = $j = $i = 0; $i < $string_length; $i++) {   
        $a = ($a + 1) % 256;   
        $j = ($j + $box[$a]) % 256;   
        $tmp = $box[$a];   
        $box[$a] = $box[$j];   
        $box[$j] = $tmp;   
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));   
    }   
      
    if($operation == 'DECODE') {   
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {   
            return substr($result, 26);   
        } else {   
                return '';   
            }   
    } else {   
        return $keyc.str_replace('=', '', base64_encode($result));   
    }     
}

//垃圾龙信提供的啥鸡巴玩意curl啊
function curl_post($url, $post_data = '', $timeout = 5){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, 1);

    if ($post_data != '') {

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    curl_setopt($ch, CURLOPT_HEADER, false);

    $file_contents = curl_exec($ch);

    curl_close($ch);

    return $file_contents;
}

//查询企业信息接口
function longxinApi($type,$value){
    if($type == 'geren'){
        $SERVICE = "getIndividualInfo";
        $PARAMS = array('keyword' => $value);
    }elseif($type == 'qiye'){
        $SERVICE = "getRegisterInfo";
        $PARAMS = array('entName' => $value);
    }else{
        echo json_encode(array('error' => 1,'msg' => '参数错误'));
        exit;
    }
    $UID = "37272991c07f40ba9fcfe2b6e4b3fb5d";
    $API = "http://121.52.214.35/service";
    $json = json_encode(array(
        'uid' => $UID,
        'service' => $SERVICE,
        'params' => $PARAMS
    ));
    $post_data_json = quantum_encode($json,'-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmSwhVODeVxPTkrTl+Z1F
ryhs32iW1w/pWHmTOO9Eo1cOXWkFSPC1r9+W829zHo3MuxxoOu8lIBaHvGhG+sqc
c1X3tvRhIWma8J19pDYxIk1L1y2Sro08yYVAnXq6J9mXGq+iSueEYtB+0jMy6Gv2
6pmmHQukZD2kFZAfF7D3y0sInVyiuEMysfcYiWZ4j+zEd4wZ51eVAfsQKaazti2J
k4ZRZtBLgrOUV6aG1UVK3Q4osJAaEREAUyrY75ptmV9b2R/uKU2CH94I62FiNFGF
d/Zgj/ihIRAJn7ZN5cSPdZW1aVekynRmmhmxV7sSyJUHwkhzacErMf/hajMR33OS
ewIDAQAB
-----END PUBLIC KEY-----');
    $p_arr['uid'] = $UID;
    $p_arr['data'] = json_encode($post_data_json);
    $rs = curl_post($API, http_build_query($p_arr));
    $rs = gzdecode($rs);
    $rs = urldecode($rs);
    if(!strpos($rs, "errorCode")){
        $rs = quantum_decode(json_decode($rs),'-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmSwhVODeVxPTkrTl+Z1F
ryhs32iW1w/pWHmTOO9Eo1cOXWkFSPC1r9+W829zHo3MuxxoOu8lIBaHvGhG+sqc
c1X3tvRhIWma8J19pDYxIk1L1y2Sro08yYVAnXq6J9mXGq+iSueEYtB+0jMy6Gv2
6pmmHQukZD2kFZAfF7D3y0sInVyiuEMysfcYiWZ4j+zEd4wZ51eVAfsQKaazti2J
k4ZRZtBLgrOUV6aG1UVK3Q4osJAaEREAUyrY75ptmV9b2R/uKU2CH94I62FiNFGF
d/Zgj/ihIRAJn7ZN5cSPdZW1aVekynRmmhmxV7sSyJUHwkhzacErMf/hajMR33OS
ewIDAQAB
-----END PUBLIC KEY-----');
        $result = json_decode($rs,true);
        if(count($result['RESULTDATA']) == 0){
            echo json_encode(array('error' => 1,'msg' => '没有查询到信息'));
            exit;
        }else{
            return $result['RESULTDATA'][0];
            exit;
        }
    }else{
        echo json_encode(array('error' => 1,'msg' => '查询出错'));
        exit;
    }
}

//去除所有符号
function filter_mark($text){ 
    if(trim($text)=='')return ''; 
    $text=preg_replace("/[[:punct:]\s]/",' ',$text); 
    $text=urlencode($text); 
    $text=preg_replace("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99|%EF%BD%9E|%EF%BC%8E|%EF%BC%88)+/",' ',$text); 
    $text=urldecode($text); 
    return trim($text); 
}

//去除所有符号包括中文全角空格
function strFilter($str){
    $str = str_replace('`', '', $str);
    $str = str_replace('·', '', $str);
    $str = str_replace('~', '', $str);
    $str = str_replace('!', '', $str);
    $str = str_replace('！', '', $str);
    $str = str_replace('@', '', $str);
    $str = str_replace('#', '', $str);
    $str = str_replace('$', '', $str);
    $str = str_replace('￥', '', $str);
    $str = str_replace('%', '', $str);
    $str = str_replace('^', '', $str);
    $str = str_replace('……', '', $str);
    $str = str_replace('&', '', $str);
    $str = str_replace('*', '', $str);
    $str = str_replace('(', '', $str);
    $str = str_replace(')', '', $str);
    $str = str_replace('（', '', $str);
    $str = str_replace('）', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('_', '', $str);
    $str = str_replace('——', '', $str);
    $str = str_replace('+', '', $str);
    $str = str_replace('=', '', $str);
    $str = str_replace('|', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);
    $str = str_replace('【', '', $str);
    $str = str_replace('】', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('}', '', $str);
    $str = str_replace(';', '', $str);
    $str = str_replace('；', '', $str);
    $str = str_replace(':', '', $str);
    $str = str_replace('：', '', $str);
    $str = str_replace('\'', '', $str);
    $str = str_replace('"', '', $str);
    $str = str_replace('“', '', $str);
    $str = str_replace('”', '', $str);
    $str = str_replace(',', '', $str);
    $str = str_replace('，', '', $str);
    $str = str_replace('<', '', $str);
    $str = str_replace('>', '', $str);
    $str = str_replace('《', '', $str);
    $str = str_replace('》', '', $str);
    $str = str_replace('.', '', $str);
    $str = str_replace('。', '', $str);
    $str = str_replace('/', '', $str);
    $str = str_replace('、', '', $str);
    $str = str_replace('?', '', $str);
    $str = str_replace('？', '', $str);
    $str = str_replace('　', '', $str);
    $str = str_replace(' ', '', $str);
    $str = str_replace(array("\r\n", "\r", "\n"), "", $str);
    $str = str_replace(' ', '', $str);
    return trim($str);
}