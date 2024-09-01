<?php

// dle DB tabulky presmerovani overujeme jestli dana URL nema pravidlo pro presmerovani

class Presmerovani
{

	public static function presmerujURL()
    {
		$url = sanitize($_SERVER['REQUEST_URI']); // ["REQUEST_URI"] = bez http a domény , $_SERVER['SCRIPT_URI'] = kompletní adresa
		
        $data = Db::queryRow('SELECT * FROM presmerovani WHERE aktivni=1 AND puvodni_url=? ORDER BY id DESC', array($url)); 
        if($data['nova_url']) 
        {
			
			header("HTTP/1.1 301 Moved Permanently"); 
			header("Location: ".$data['nova_url']);
			header("Connection: close");
			exit();
		}
        
    }
	
}
?>
