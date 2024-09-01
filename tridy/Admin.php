<?php
// prace s adminem
Class Admin
{

	public static function adminLogin($prava)
	{

		//kontroluje jestli je uzivatel zalogovan a ma dostatecna prava pro zobrazeni dane stranky
		if($_SESSION['admin']['login'])
		{
			
			// nejdříve kontrolujeme jestli souhlasí IP
			if($_SESSION['admin']['ip'] != getip())
			{
				self::logOut();
				header("location: /admin/");
				exit();
			}
			
			if($_SESSION['admin']['prava'] < $prava)
			{
				// nema prava pro zobrazeni
				echo 'Pro zobrazení této stránky nemáte oprávnění';
				exit();
			}
			
			//var_dump($prava);
			//var_dump($_GET['p']);

			
		}
		else
		{
			if($_SERVER['REQUEST_URI']!='/admin/')
			{
			  
				$_SESSION['admin_referer'] = $_SERVER['REQUEST_URI'];
			   
			   header("location: /admin/");
			   exit();
		    }
		}
		
	}
	
	
	
	public static function zalogujAdmin()
	{
		$sess_id = session_id();
		
		$zeme = geoip_country_code_by_name($_SERVER['REMOTE_ADDR']);
	
		 if($_POST['submit'] && !$_SESSION['admin']['login'])
		  {
			  if($_POST['uz_jm'] && $_POST['heslo'])
			  {
			  
			    $err_ref =  kontrola_ref();
			    if($err_ref)
			    {
				  echo $err_ref;
				  exit();
			    }
  
			  // kontrola 
			  $data = Db::queryRow('SELECT * FROM admin WHERE aktivni=1 AND uz_jm=? ', array(addslashes($_POST['uz_jm'])));

				 if($data!==false && password_verify($_POST['heslo'],$data['heslo'])!==false) 
				  {
					  // údaje souhlasí
					  
					    $data_admin = array(
					     'id' => $data['id'],
					     'uz_jm' => $data['uz_jm'],
					     'heslo' => $data['heslo'],
					     'prava' => $data['prava'],
					     'ip' => getip(),
					     );
					     
					  
						$_SESSION['admin'] = $data_admin;
					  
					  // dále kontrolujeme IP a otisk prohlížeče
					  // pokud není nalezen tak odešleme na email admina vygenerovaný PIN
					  // ten musí do 5ti minut zadat
					  $data_k = Db::queryRow('SELECT * FROM admin_otisk WHERE id_admin=? AND ip=? AND otisk=?', 
					  array($data['id'],sanitize(getip()),sanitize($_SERVER['HTTP_USER_AGENT'])));

					  if(!$data_k) 
					  {
					        $pin = self::generujPIN();
					        $cas_60_min = (time() - 3600);
					        
					     
					        // odesleme pin
							$bodymail = "PIN pro přístup do administrace projektu ".$_SERVER['SERVER_NAME'].": ".$pin."\nPlatnost PINu je 5 minut\nNa tento email neodpovídejte - je generován automaticky";
			
							$eml = new Email('plaintext',false);
							$eml->nastavSubject('PIN pro pristup do administrace '.$_SERVER['SERVER_NAME']);
							$eml->nastavBody($bodymail);
							$eml->nastavFrom('robot@'.$_SERVER['SERVER_NAME']);
							$eml->nastavTo($data['email']);
							$vysledek = $eml->odesliEmail();
							
							if(!$vysledek)
							{
								echo 'Nepodařilo se odeslat email s PINem<br />';
								exit();
	
							}

							
							// smažeme předchozí PIN
							$query_d = Db::deleteAll('admin_pin', 'ip="'.sanitize(getip()).'" AND id_admin='.intval($data['id']).' AND datum > '.$cas_60_min.' ');	
						
							
							// ulozime PIN do databaze
							$data_insert_pin = array(
						    'pin' => $pin,
						    'id_admin' => $data['id'],
						    'datum' => time(),
						    'ip' => sanitize(getip())
						     );
						     
						     $query_insert_pin = Db::insert('admin_pin', $data_insert_pin);
					     
							 
							 $data_insert_log = array(
						    'id_admin' => $data['id'],
						    'zalogovan' => 3,
						    'uz_jm' => addslashes($_POST['uz_jm']),
						    'heslo' => '',
						    'ip' => sanitize(getip()),
						    'zeme' => $zeme,
						    'browser' => sanitize($_SERVER['HTTP_USER_AGENT']),
						    'sess_id' => $sess_id,
						    'datum' => time()
						     );
						     
						     $query_insert_log = Db::insert('admin_log_prihlaseni', $data_insert_log);
							 $pin_id = $query_insert_log;
							 
							 $_SESSION['pin_id'] = $pin_id;
					         Header('Location: pin.php');
					         exit();
			
					     
					  }
					  
					    // log událostí
					    $data_insert = array(
					    'id_admin' => $data['id'],
					    'uz_jm' => $data['uz_jm'],
					    'udalost' => 'login',
					    'url' => sanitize($_SESSION['admin_referer']),
					    'ip' => sanitize(getip()),
					    'datum' => time()
					     );
					     
					     $query_insert = Db::insert('admin_log_udalosti', $data_insert);
					     

						     
						
						 
					     
					    $_SESSION['admin']['login'] = time();
						session_regenerate_id(true);
						
						$sess_id = session_id();
						
						// log přihlášení
					    $data_insert_log = array(
						    'id_admin' => $data['id'],
						    'zalogovan' => 1,
						    'uz_jm' => addslashes($_POST['uz_jm']),
						    'heslo' => '',
						    'ip' => sanitize(getip()),
						    'zeme' => $zeme,
						    'browser' => sanitize($_SERVER['HTTP_USER_AGENT']),
						    'sess_id' => $sess_id,
						    'datum' => time()
						     );
						     
						$query_insert_log = Db::insert('admin_log_prihlaseni', $data_insert_log);
						  
						// update posledního přihlášení u admina    
					    $data_update = array('datum_posledniho_prihlaseni' => time(),'ip_posledniho_prihlaseni' => sanitize(getip()),'sess_id' => $sess_id);
						$where_update = array('id' => $data['id']);
						$query_update = Db::update('admin', $data_update, $where_update);
 
						if($_SESSION['admin_referer'])
						{
						    header("location: ".sanitize($_SESSION['admin_referer']));
							exit();
						}

				  } 
				  else
				  {
						  if(self::blokujPristup() >= 3)
						  {
							  $posledni = 1;
						  }
						  else
						  {
							  $posledni = 0; 
						  }
						 	
					      $data_insert_log = array(
						    'id_admin' => 0,
						    'zalogovan' => 0,
						    'uz_jm' => addslashes($_POST['uz_jm']),
						    'ip' => sanitize(getip()),
						    'zeme' => $zeme,
						    'browser' => sanitize($_SERVER['HTTP_USER_AGENT']),
						    'sess_id' => $sess_id,
						    'datum' => time(),
						    'typ' => 1,
						    'posledni' => $posledni
						     );
					     
					     $query_insert_log = Db::insert('admin_log_prihlaseni', $data_insert_log);
					     				     

						// odesleme info o pokusu o login
						if(__ODESLAT_EMAIL_ADMIN_LOGIN__==1)
						{
							$bodymail = "IP: ".getip()."\nUživatelské jméno: ".sanitize($_POST['uz_jm'])."\nHeslo: ".sanitize($_POST['heslo'])."\nDoména: ".sanitize($_SERVER['SERVER_NAME'])."\nProhlížeč a OS: ".sanitize($_SERVER['HTTP_USER_AGENT'])."\nČas útoku: ".date("d.m.Y H:i:s")."\nLokace útočníka: https://www.infosniper.net/index.php?ip_address=".getip()."&k=&lang=1";
			
							$eml = new Email('plaintext',false);
							$eml->nastavSubject('Pokus o prunik do administrace '.$_SERVER['SERVER_NAME']);
							$eml->nastavBody($bodymail);
							$eml->nastavFrom('robot@'.$_SERVER['SERVER_NAME']);
							$eml->nastavTo(__ADMIN_EMAIL__);
							$eml->nastavBcc('info@w-software.com');
							$vysledek = $eml->odesliEmail();
							
							if(!$vysledek)
							{
								$err .= 'Nepodařilo se odeslat email<br />';
	
							}
							
						}

						 
						  
				  $err .= "zadané údaje nejsou správné";
				  
				  }
			  }
			  else
			  {
				 $err .= "nevyplnili jste uživatelské jméno a heslo";
			  }
			  
			  return $err;
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
		 $cas = (time() - 3600 ); // za posledni hodinu 
		 $result = Db::queryAffected('SELECT id FROM admin_log_prihlaseni WHERE typ=? AND zalogovan=? AND ip=? AND datum >= ?', array(1,0,$ip,$cas)); 
		 return $result;
	}
	
	
	
	public static function logOut()
	{
		$sess_id = session_id();
		
		 $data = array(
	    'id_admin' => $_SESSION['admin']['id'],
	    'uz_jm' => $_SESSION['admin']['uz_jm'],
	    'udalost' => 'logout',
	    'url' => $_SERVER['HTTP_REFERER'],
	    'ip' => getip(),
	    'datum' => time()
	     );
	     
	  $query = Db::insert('admin_log_udalosti', $data);
	  
	  
	  $data_insert_log = array(
	    'id_admin' => $_SESSION['admin']['id'],
	    'zalogovan' => 1,
	    'uz_jm' => addslashes($_SESSION['admin']['uz_jm']),
	    'heslo' => '',
	    'ip' => sanitize(getip()),
	    'typ' => 2,
	    'sess_id' => $sess_id,
	    'datum' => time()
	     );
      $query_insert_log = Db::insert('admin_log_prihlaseni', $data_insert_log);
					    
					    
	  unset($_SESSION['admin']);
	  unset($_SESSION['pin_id']);
	  unset($_SESSION['admin_referer']);
	  
	  
	  
	  
	}
	
	
	
	public static function generujPIN()
	{
	
		$alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9');
		$pin = $alphabet[rand(0, 34)].$alphabet[rand(0, 34)].$alphabet[rand(0, 34)].$alphabet[rand(0, 34)].$alphabet[rand(0, 34)];
    
		return $pin;
	
	}
	
	
	
	public static function platnostSesny($sess_id)
	{
		if($sess_id)
		{
		  if(file_exists($_SERVER['TMP'].'/sess_'.$sess_id))
		  {	
			  // uprava kdy kontrolujeme jestli je sešna platná
			  if(fileatime($_SERVER['TMP'].'/sess_'.$sess_id) < (time() - 7200))
			  {
				   unlink($_SERVER['TMP'].'/sess_'.$sess_id);
				   return '<span class="r">N</span>'; 
			  }
			  else
			  {
				  return '<span class="g">A</span> &nbsp;&nbsp;<a href="?p=admin&action=delete-sess&ids='.$sess_id.'">smazat</a>';
			  }
			  
			   
		  }
		  else
		  {
			 return '<span class="r">N</span>'; 
		  }
	    }
	    else
	    {
			return '<span class="r">N</span>';
		}
	}
	
	
	public static function zalogujPIN()
	{

		
		if($_SESSION['admin']['id'] && $_SESSION['admin']['uz_jm'] && $_SESSION['admin']['heslo']  && $_SESSION['admin']['prava'] && $_SESSION['admin']['ip'])
		{
			$zeme = geoip_country_code_by_name($_SERVER['REMOTE_ADDR']);
			
			if($_SESSION['admin']['ip'] == sanitize(getip()))
			{
				$sess_id = session_id();
				$cas_5_min = (time() - 300); // aktualni cas - 5 minut
				$cas_60_min = (time() - 3600); // aktualni cas - 60 minut
				
				if($_POST['submit2'] && $_POST['pin'])
				{
	
				
				 $data_insert_log = array(
			    'id_admin' => $_SESSION['admin']['id'],
			    'id_pin' => $_SESSION['pin_id'],
			    'ip' => sanitize(getip()),
			    'datum' => time()
			     );
		     
		        $query_insert_log = Db::insert('admin_log_pin', $data_insert_log);
	
						
				// kontrolujeme  pocet pokusu
				$query_p = Db::queryAffected('SELECT id FROM admin_log_pin WHERE id_admin=? AND ip=? AND datum > '.$cas_60_min.' 
				AND id_pin=?', array(intval($_SESSION['admin']['id']),sanitize(getip()),intval($_SESSION['pin_id']))); 
	
			    $pocet = $query_p;
			    
			       
			    
			    if($pocet > 3)
			    {
					if(__ODESLAT_EMAIL_ADMIN_LOGIN__==1)
					{
						$bodymail = "PIN pro přístup do administrace projektu ".$_SERVER['SERVER_NAME']." byl zadán 5x\nIP adresa: ".sanitize(getip())."\nUž. jméno: ".$_SESSION['user']."\nLokace útočníka: https://www.infosniper.net/index.php?ip_address=".sanitize(getip())."&k=&lang=1" ;
		
						$eml = new Email('plaintext',false);
						$eml->nastavSubject('Blokace IP - zadani PINu do administrace '.$_SERVER['SERVER_NAME']);
						$eml->nastavBody($bodymail);
						$eml->nastavFrom('robot@'.$_SERVER['SERVER_NAME']);
						$eml->nastavTo(__ADMIN_EMAIL__);
						$eml->nastavBcc('info@w-software.com');
						$vysledek = $eml->odesliEmail();
						
						
					}
	
					
					// zablokujeme IP
					$data_insert_ip = array(
				    'ip' => sanitize(getip())
				     );
			        $query_insert_ip = Db::insert('zakazane_ip', $data_insert_ip);
	
					// do logu
					$data_insert_log = array(
					    'id_admin' => $_SESSION['admin']['id'],
					    'zalogovan' => 2,
					    'uz_jm' => addslashes($_SESSION['admin']['uz_jm']),
					    'heslo' => '',
					    'ip' => sanitize(getip()),
					    'zeme' => $zeme,
					    'browser' => sanitize($_SERVER['HTTP_USER_AGENT']),
					    'sess_id' => $sess_id,
					    'datum' => time(),
					    'posledni' => 1
					     );
				    $query_insert_log = Db::insert('admin_log_prihlaseni', $data_insert_log);
				    
	
					unset($_SESSION['admin']);
					unset($_SESSION['pin_id']);
				
					//die('Přístup odepřen');
					Header('Location: /error/403.html');
				}
				
				
				
				// zkontrolujeme PIN
				$data_p = Db::queryRow('SELECT * FROM admin_pin WHERE id_admin=? AND ip=? AND datum > '.$cas_5_min.' AND pin=? ORDER BY id DESC LIMIT 1 ', 
				array(intval($_SESSION['admin']['id']),sanitize(getip()),addslashes($_POST['pin']) ));

				 if($data_p!==false) 
				  {
					    // údaje souhlasí
						 $_SESSION['admin']['login'] = time();
						 session_regenerate_id(true);
						 $sess_id = session_id();
						 
						 
						 // do logu
						$data_insert_log = array(
						    'id_admin' => $_SESSION['admin']['id'],
						    'zalogovan' => 1,
						    'uz_jm' => addslashes($_SESSION['admin']['uz_jm']),
						    'heslo' => '',
						    'ip' => sanitize(getip()),
						    'zeme' => $zeme,
						    'browser' => sanitize($_SERVER['HTTP_USER_AGENT']),
						    'sess_id' => $sess_id,
						    'datum' => time()
						     );
					    $query_insert_log = Db::insert('admin_log_prihlaseni', $data_insert_log);
						
						// log událostí
					    $data_insert = array(
					    'id_admin' =>  $_SESSION['admin']['id'],
					    'uz_jm' => addslashes($_SESSION['admin']['uz_jm']),
					    'udalost' => 'login',
					    'url' => sanitize($_SESSION['admin_referer']),
					    'ip' => sanitize(getip()),
					    'datum' => time()
					     );
					     
					     $query_insert = Db::insert('admin_log_udalosti', $data_insert);
					     
						
						// otisk prohlížeče
					   $data_insert_otisk = array(
						    'ip' => sanitize(getip()),
						    'otisk' => sanitize($_SERVER['HTTP_USER_AGENT']),
						    'id_admin' => intval($_SESSION['admin']['id']),
						    'datum' => time()
						     );
					    $query_insert_otisk = Db::insert('admin_otisk', $data_insert_otisk);
					    
					    
					    // update posledního přihlášení u admina    
					     $data_update = array('datum_posledniho_prihlaseni' => time(),'ip_posledniho_prihlaseni' => sanitize(getip()),'sess_id' => $sess_id);
						 $where_update = array('id' => intval($_SESSION['admin']['id']));
						 $query_update = Db::update('admin', $data_update, $where_update);
						 
						 // vše je v pořádku, můžeme do admina
						 Header('Location: /admin/');
					
				  
				  }
				  else
				  {

					
				    	$data_insert_log = array(
						    'id_admin' => $_SESSION['admin']['id'],
						    'zalogovan' => 2,
						    'uz_jm' => addslashes($_SESSION['admin']['uz_jm']),
						    'heslo' => '',
						    'ip' => sanitize(getip()),
						    'zeme' => $zeme,
						    'browser' => sanitize($_SERVER['HTTP_USER_AGENT']),
						    'sess_id' => $sess_id,
						    'datum' => time()
						     );
					    $query_insert_log = Db::insert('admin_log_prihlaseni', $data_insert_log);
					
					    $err = 'PIN nesouhlasí nebo vypršel čas.<br />Nový PIN vygenerujete <a href="/admin/">zde</a>';
				  }
				  
				  
			  }
			    
		  
		    $login_form = new Html();
			echo $login_form->pridejDoKodu('{login_error}',$err); 
			echo $login_form->generujKod('_pinform'); 
		     
		     
		     
		
		  }
		  else
		  {
			header("location: /error/403.html");
			exit();	
		  }

		    
		}
		else
		{
			header("location: /error/403.html");
			exit();	
		}
		
		
		
		


	
	
	
	
	}
	

	
}

?>
