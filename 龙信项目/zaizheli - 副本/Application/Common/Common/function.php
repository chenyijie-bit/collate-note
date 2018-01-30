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
//得到真实IP
function getTrueIp(){
	$socket = socket_create(AF_INET, SOCK_STREAM, 6);
	$ret = socket_connect($socket,'ns1.dnspod.net',6666);
	$buf = socket_read($socket, 16);
	socket_close($socket);
	return $buf;
}
//对图片进行base64
function base64EncodeImage($image_file){
  $base64_image = '';
  $image_info = getimagesize($image_file);
  $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
  $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
  return $base64_image;
}
//用sha-256算法对字符串进行加密
function sha256($str){
	return hash("sha256",$str,true);
}