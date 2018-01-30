<?php
namespace Index\Common;

class AES128_cbc_code{

	private $privateKey = 'longxin_huawei12'; //密码
	private $iv = 'abcd@1234_012mec'; //偏移量

	/**
     * 加密方法
     * @param string $str
     * @return string
     */
    public function encode($str){
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$this->privateKey,$str,MCRYPT_MODE_CBC,$this->iv);
        return base64_encode($encrypted);
    }
      
    /**
     * 解密方法
     * @param string $str
     * @return string
     */
    public function decode($str){
        $encryptedData = base64_decode($str);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$this->privateKey,$encryptedData,MCRYPT_MODE_CBC,$this->iv);
        return $decrypted;
    }
}