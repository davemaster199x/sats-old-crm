<?php

class cast128 {
	
	var $mykey = "";
	
	function setkey($key)
	{
		$this->mykey = $key;
		return 1;
	}
	
	function encrypt($pass) 
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB); //get vector size on ECB mode 
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); //Creating the vector
		$cryptedpass = mcrypt_encrypt (MCRYPT_RIJNDAEL_256, $this->mykey, $pass, MCRYPT_MODE_ECB, $iv); //Encrypting using MCRYPT_RIJNDAEL_256 algorithm 
		return $cryptedpass;
	}

	function decrypt($enpass) 
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB); 
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decryptedpass = mcrypt_decrypt (MCRYPT_RIJNDAEL_256, $this->mykey, $enpass, MCRYPT_MODE_ECB, $iv); //Decrypting...
		return rtrim($decryptedpass);
	}
} ?>
