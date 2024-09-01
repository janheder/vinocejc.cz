<?php
// práce s uživatelem 
Class Uzivatel
{

	
	public static function zalogujUzivatele()
	{
		
			  if($_POST['login_form_email'] && $_POST['login_form_pass'])
			  {
			  
			  $err_ref =  kontrola_ref();
			  if($err_ref)
			  {
				  return $err_ref;
				  exit();
			  }
			  
			  // kontrolujeme jestli nemá blokovanou IP
			  if(self::blokujIP())
			  { 
				  return 'Přístup odepřen'; 
				  exit();
			  }  
			  
			  // kontrolujeme jestli není zablokovaný na hodinu díky 3 neúspěšným pokusům
			  if(self::blokujPristup() >=3 )
			  { 
				  return 'Mnoho neúspěšných pokusů'; 
				  exit();
			  } 
			  

			  // kontrola 
			  $data = Db::queryRow('SELECT * FROM zakaznici WHERE aktivni=? AND uz_jmeno=? AND bez_registrace=? ', array(1,sanitize($_POST['login_form_email']),0));

				 if($data!==false && password_verify($_POST['login_form_pass'],$data['heslo'])!==false) 
				  {

					     $data_update = array('datum_posledniho_prihlaseni' => time());
						 $where_update = array('id' => $data['id']);
						 $query_update = Db::update('zakaznici', $data_update, $where_update);
						 
						 // slevová skupina
						 if($data['id_skupiny_slev'])
						 {
							$data_sleva = Db::queryRow('SELECT procento FROM zakaznici_skupiny WHERE id=? ', array($data['id_skupiny_slev']));
							$slevova_skupina = intval($data_sleva['procento']);
						 }
						 else
						 {
							$slevova_skupina = 0;
						 }
						 
							$data_uzivatel = array(
						     'id' => $data['id'],
						     'uz_jm' => $data['email'],
						     'jmeno' => $data['jmeno'],
						     'prijmeni' => $data['prijmeni'],
						     'email' => $data['email'],
						     'slevova_skupina' => $slevova_skupina,
						     'typ_ceny' => $data['cenova_skupina'],
						     'ip' => sanitize(getip()),
						     'login' => time()
						     );
						     
						  
							$_SESSION['uzivatel'] = $data_uzivatel;
							
							
							session_regenerate_id(true);
							
							return 'Přihlášení proběhlo úspěšně.'; 


				  } 
				  else
				  {
					  
					     $data_insert = array(
					    'uz_jm' => sanitize($_POST['login_form_email']),
					    'ip' => sanitize(getip()),
					    'datum' => time()
					     );
					     
					     $query = Db::insert('log_prihlaseni', $data_insert);
					     				     

				         return 'Zadané údaje nejsou správné';
				  
				  }
			  }
			  else
			  {
				 return 'Nevyplnili jste email a heslo';
			  }
			  
		  
		  
		
	}
	
	
	
	public static function blokujIP()
	{
		$ip = getip();
		$result = Db::queryAffected('SELECT id FROM zakazane_ip WHERE ip=? ', array($ip)); 
		return $result;
	} 
	
	
	
	public static function blokujPristup()
	{
		 $ip = getip();	
		 $cas = (time() - 3600 ); //za posledni hodinu 
		 $result = Db::queryAffected('SELECT id FROM log_prihlaseni WHERE ip=? AND datum >= ?', array($ip,$cas)); 
		 return $result;
	}
	
	
	public static function blokujPristupZapomenuteHeslo()
	{
		 $ip = getip();	
		 $cas = (time() - 3600 ); //za posledni hodinu 
		 $result = Db::queryAffected('SELECT id FROM log_prihlaseni_heslo WHERE ip=? AND datum >= ?', array($ip,$cas)); 
		 return $result;
	}
	
	
	
	public static function logOut()
	{
	 
	  unset($_SESSION['uzivatel']);
	  
	}
	
	


	
}

?>
