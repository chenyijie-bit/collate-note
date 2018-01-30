<?php
namespace Index\Common;

class Hw_MECService{
	
	//龙信属性
	private $Partner_ID = '000001';
	private $API_IP = '221.130.39.115';

	//MEC头信息
	public function getMECHeaderInfo(){
		//参数
		$Nonce = base64_encode(date('Y-m-d#$%^&*()H:i:s'));
		// $Nonce = 'eUZZZXpSczFycXJCNVhCWU1mS3ZScldOYg==';
		$Timestamp = date('Y-m-d').'T'.date('H:i:s').'Z';
		$PassWord = 'Huawei!23';
		$Username = 'ODBvUIC';
		//头信息
		$header = array();
		$header[] = 'Content-Type: application/json';
		$header[] = 'Accept: application/json';
		$header[] = 'Authorization: WSSE realm="MEC",profile="UsernameToken"';
		$header[] = 'X-WSSE: UsernameToken Username="'.$Username.'",PasswordDigest="'.base64_encode(sha256($Nonce.$Timestamp.$PassWord)).'",Nonce="'.$Nonce.'",Timestamp="'.$Timestamp.'"';
		return $header;
	}

	//MEC申请
	public function applyMECServiceRequest($params){
		$url = 'http://'.$this->API_IP.'/services/MEC/1.0/MECService';
		$data = '{ "Partner_ID": "'.$this->Partner_ID.'" , "User_ID": "'.$params['member_id'].'" , "UserIdentifier": { "MSISDN_masked": "'.$params['phone_num'].'" } , "PackageType": "longxin_unifyuse" }';
		$res = CurlRequest($url,false,'post',$data,$this->getMECHeaderInfo());
		return $res;
	}

	//MEC注销
	public function removeMECServiceRequest($params){
		$url = 'http://'.$this->API_IP.'/services/MEC/1.0/MECService';
		$data = '{ "Partner_ID": "'.$this->Partner_ID.'" , "User_ID": "'.$params['member_id'].'" , "UserIdentifier": { "MSISDN_masked": "'.$params['phone_num'].'" , "PublicIP": "'.$_SERVER['REMOTE_ADDR'].'" },"PackageType": "longxin_unifyuse" }';
		$res = CurlRequest($url,false,'delete',$data,$this->getMECHeaderInfo());
		return $res;
	}

	//MEC状态
	public function reportMECServiceStatusEventRequest($params){
		$url = 'http://'.$this->API_IP.'/services/MEC/1.0/MECService';
		//header头信息
		$header = array();
		$header[] = 'Content-Type: application/json';
		$header[] = 'Accept: application/json Timestamp="'.base64_encode(date('Y-m-d#$%^&*()H:i:s')).'"';
		//数据
		$data = '{ "Partner_ID": "'.$this->Partner_ID.'" , "User_ID": "'.$params['member_id'].'" , "UserIdentifier": { "MSISDN_masked": "'.$params['phone_num'].'" } , "PackageType": "longxin_unifyuse" , "MECServiceStatus": "0" }';
		$res = CurlRequest($url,false,'post',$data,$header);
	}
}