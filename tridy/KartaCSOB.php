<?php
// třída pro komunikaci s platební bránou ČSOB
// v eAPI 1.8

class KartaCSOB
{

public $url; 
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
		$this->url = "https://api.platebnibrana.csob.cz/api/v1.9"; // iapi.iplatebnibrana.cz - testovací
		$this->return_url = __URL__."/platba";
		$this->merchant_id = $merchant_id;
		$this->public_key = $public_key;
		$this->private_key = $private_key;
		$this->private_key_password = $private_key_password;
		$this->mena = $mena; // CZK 
		$this->jazyk = $jazyk; // CZ 
		$this->cena_celkem = (int) ($cena_celkem * 100); // * 100
		$this->datum = date("YmdHis"); //YYYYMMDDHHMMSS
		
		   
	}
	
	
	
	
	function dataKosik() 
	{

	    $data_kosik = array(
	             0 => array(
	            "name" => "Nákup na ".__DOMENA__,
	            "quantity" => 1,
	            "amount" => $this->cena_celkem,
	            "description" => "nákup"
	        )
	
	    );
	    
	    return $data_kosik;
    
	}
	
	
	
	function createPaymentInitData($cisl_obj, $id_zak, $kosik, $merchant_data) 
	{

	    $data = array(
	        "merchantId" => $this->merchant_id,
	        "orderNo" => $cisl_obj,
	        "dttm" => $this->datum,
	        "payOperation" => "payment",
	        "payMethod" => "card",
	        "totalAmount" => $this->cena_celkem,
	        "currency" => $this->mena,
	        "closePayment" => 1,
	        "returnUrl" => $this->return_url,
	        "returnMethod" => "POST",
	        "cart" => $kosik,
	        "merchantData" => base64_encode($merchant_data),
	        "language" => $this->jazyk
	    );
	    
	    // úprava 27.6.2024
	    // doplněny údaje zákazníka ADDINFO
	    
	    $data_z = Db::queryRow('SELECT * FROM zakaznici WHERE id=? ', array(intval($id_zak)));
	    if($data_z['jmeno'])
	    {
			$data["customer"] = array(
		    "name" => $data_z['jmeno']." ".$data_z['prijmeni'],
			"email" => $data_z['email'],
			"mobilePhone" => "+420.".telefon_karta($data_z['telefon']));
		}
		
	
	    
		
		
	    if (!is_null($id_zak) && $id_zak != '0') 
	    {
	        $data["customerId"] = $id_zak;
	    }
	
	    $data["signature"] = $this->signPaymentInitData($data);
	
	    return $data;
    
	}
	
	
	
	
	function signPaymentInitData($data) 
	{

	    $cart2Sign = $data["cart"][0]["name"] . "|" . $data["cart"][0]["quantity"] . "|" . $data["cart"][0]["amount"] . "|" . $data["cart"][0]["description"];
	
	    $data2Sign = $data["merchantId"] . "|" . $data["orderNo"] . "|" . $data["dttm"] . "|" . $data["payOperation"] . "|" . $data["payMethod"] . "|" . $data["totalAmount"]
	            . "|" . $data["currency"] . "|" . ($data["closePayment"] ? 'true' : 'false') . "|" . $data["returnUrl"] . "|" . $data["returnMethod"] . "|" . $cart2Sign;
	            
	    // DO PODPISU PŘIDÁME ÚDAJE ZÁKAZNÍKA
	    $data2Sign = $data2Sign."|".$data["customer"]["name"]."|".$data["customer"]["email"]."|".$data["customer"]["mobilePhone"];        
	
	    $merchantData = $data["merchantData"];
	    
	    if (!is_null($merchantData)) 
	    {
	        $data2Sign = $data2Sign . "|" . $merchantData;
	    }
	
	    if (isset($data["customerId"]) && $data["customerId"] != '0') 
	    {
	        $data2Sign = $data2Sign . "|" . $data["customerId"];
	    }
	
	    $data2Sign = $data2Sign . "|" . $data["language"];
	
	    if ($data2Sign [strlen($data2Sign) - 1] == '|') 
	    {
	        $data2Sign = substr($data2Sign, 0, strlen($data2Sign) - 1);
	    }
	
	    return $this->sign($data2Sign);
	
	}
	
	
	
	function createGetParams($payId) 
	{
		
	    $text = $this->merchant_id . "|" . $payId . "|" . $this->datum;
	    $signature = $this->sign($text);
	    return $this->merchant_id . "/" . $payId . "/" . $this->datum . "/" . urlencode($signature);
    
	}



	function preparePutRequest($payId) 
	{
		
	    $data = array(
	        "merchantId" => $this->merchant_id,
	        "payId" => $payId,
	        "dttm" => $this->datum
	    );
	    $text = $this->merchant_id . "|" . $payId . "|" . $this->datum;
	    $data['signature'] = $this->sign($text);
	    return $data;
    
	}
	
	
	
	function verifyResponse($response) 
	{
			
	    $text = $response['payId'] . "|" . $response['dttm'] . "|" . $response['resultCode'] . "|" . $response['resultMessage'];
	
	    if (!is_null($response['paymentStatus'])) 
	    {
	        $text .= "|" . $response['paymentStatus'];
	    }
	
	    if (isset($response['authCode']) && !is_null($response['authCode'])) 
	    {
	        $text .=  "|" . $response['authCode'];
	    }
	
	    if (isset($response['merchantData']) && !is_null($response['merchantData'])) 
	    {
	        $text .=  "|" . $response['merchantData'];
	    }
	
	    return $this->verify($text, $response['signature']);
    
	}
	
	
	
	function sign($text) 
	{

		
		$fp = fopen("./__karta/".$this->private_key, "r");
		if(!$fp) 
		{
			throw new Exception("Private Key not found");
		}
		$private = fread($fp, filesize("./__karta/".$this->private_key));
		fclose($fp);
		$privateKeyId = openssl_get_privatekey($private, $this->private_key_password);
		openssl_sign($text, $signature, $privateKeyId,  OPENSSL_ALGO_SHA256);
		$signature = base64_encode($signature);
		openssl_free_key($privateKeyId);
		return $signature;
	
	}



	function verify($text, $signatureBase64) 
	{
		
	    
	    $fp = fopen("./__karta/".$this->public_key, "r");
	    if (!$fp) 
	    {
	        throw new Exception("Public Key not found");
	    }
	    $public = fread($fp, filesize("./__karta/".$this->public_key));
	    fclose($fp);
	    $publicKeyId = openssl_get_publickey($public);
	    $signature = base64_decode($signatureBase64);
	    $res = openssl_verify($text, $signature, $publicKeyId, OPENSSL_ALGO_SHA256);
	    openssl_free_key($publicKeyId);
	    return (($res != '1') ? false : true);
    
	}




	
	
	
}

?>
