<?php
// trida pro odesilani SMS přes sms-sluzba.cz


class Sms{
 	private $login, $password, $smsgateapi_url, $params, $encoding;

	function __construct($login, $password, $encoding="UTF-8") {
		$this->login = $login;
	    $this->password = $password;
	    $this->encoding = $encoding;
	    $this->params = Array();
	}
	
	public function confirm_message($params){
		$query_string = http_build_query($params);
		$handle = fopen($this->get_url(__SMS_API_CONFIRMER__)."&".$query_string,'rb',false);
		return $this->send_request($handle);
	}
	
	public function get_incoming_messages($types=array()){
		$query_string = http_build_query($types);
		$handle = fopen($this->get_url(__SMS_API_SENDER__)."&".$query_string,'rb',false);
		return $this->send_request($handle);
	}
	
	public function send_message($recipient,$text, $send_at = null, $dr_request = null ){
		if ($dr_request == null)
			$dr_request = __SMS_DEFAULT_DR_REQUEST__;
		if ($send_at == null)
			$send_at =  date('YmdHis');
		$xml = "<outgoing_message><dr_request>".$dr_request."</dr_request><send_at>".$send_at."</send_at><text>".$text."</text><recipient>".$recipient."</recipient></outgoing_message>";
		return $this->send_xml_request($xml);
	}
	
	
	private function get_params_for_xml_request($data) {
	    $params = array('http' => array(
	      'method' => 'POST',
	      'content' => $data,
		'header' => 'Content-type: text/xml'
	    ));
	    return stream_context_create($params);
	}
	
	private function send_xml_request($data){
		$handle = fopen($this->get_url(__SMS_API_RECEIVER__),'rb',false,$this->get_params_for_xml_request($data));
		return $this->send_request($handle);
	}
	
	private function send_request($handle) {
	    $contents = '';
	    if (!$handle)
	    	return false;
	    while (!feof($handle)) {
	    	$contents .= fread($handle, __SMS_BLOCK_SIZE__);
	    }
	    fclose($handle);
		return $contents;
	}
	
	private function get_url($type) { return __SMS_API_URL__.$type."?login=".$this->login."&password=".$this->password."&affiliate=".__SMS_AFF__;}
	
		  

}
?>