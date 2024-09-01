<?php
// třída pro komunikaci s platební bránou WebPay

class KartaWebPay
{

private $url; 
private $public_key;
private $merchant_id;  
private $private_key;
private $private_key_password; 
public $return_url;
public $mena;
public $jazyk;
public $datum;
private $cena_celkem;


	function __construct($public_key, $merchant_id, $private_key, $private_key_password, $mena, $jazyk, $cena_celkem)
	{
		$this->url = "https://3dsecure.gpwebpay.com/rb/order.do";
		$this->return_url = __URL__."/platba";
		$this->merchant_id = $merchant_id;
		$this->public_key = $public_key;
		$this->private_key = $private_key;
		$this->private_key_password = $private_key_password;
		$this->mena = $mena; // 203
		$this->jazyk = $jazyk // CZ; 
		$this->cena_celkem = (int) ($cena_celkem * 100); // * 100
		$this->datum = date("YmdHis"); //YYYYMMDDHHMMSS
   
	}
	
	
	
	function CSignature()
	{
	  $fp = fopen("./__karta/".$this->private_key, "r");
	  $this->privatni = fread($fp, filesize("./__karta/".$this->private_key));
	  fclose($fp);

	  $fp = fopen("./__karta/".$this->public_key, "r");
	  $this->verejny = fread($fp, filesize("./__karta/".$this->public_key));
	  fclose($fp);
	}
	
	
	
	function sign($text)
	{
	  $pkeyid = openssl_get_privatekey($this->privatni, $this->private_key_password);
	  openssl_sign($text, $signature, $pkeyid);
	  $signature = base64_encode($signature);
	  openssl_free_key($pkeyid);
	  return $signature;
	}
	
	
	
	function verify($text, $signature)
	{
	  $pubkeyid = openssl_get_publickey($this->verejny);
	  $signature = base64_decode($signature);
	  $vysledek = openssl_verify($text, $signature, $pubkeyid);
	  openssl_free_key($pubkeyid);
	  return (($vysledek==1) ? true : false);
	}
	
	
}

?>