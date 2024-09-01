<?php

// karta ČSOB - prezentační vrstva


if(kontrola_ref())
{
 echo kontrola_ref();
 die();	
}
elseif($_POST['r_u'] && $_SESSION['reg_kosik']['id_obj'] && $_SESSION['reg_kosik']['id_zak'])
{
	$error = '';
	$K = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
	$cena_zbozi = $K->kosikCenaCelkem();
	
	// doprava
	if($_SESSION['doprava']['cena'])
	{
	   $doprava_cena_vyber = intval($_SESSION['doprava']['cena']);
	}
	else
	{
	   $doprava_cena_vyber = 0;
	}
	
	// platba
	if($_SESSION['platba']['cena'])
	{
	   $platba_cena_vyber = intval($_SESSION['platba']['cena']);
	}
	else
	{
	   $platba_cena_vyber = 0;
	}
	
	// slevovký kód
	$kod_castka = 0;
	if($_SESSION['slevovy_kod']['id'])
	{
	    if($_SESSION['slevovy_kod']['typ']==1)
	    {
		   // částka
		   $kod_castka = $_SESSION['slevovy_kod']['castka'];

		}
		elseif($_SESSION['slevovy_kod']['typ']==2)
		{
		   // procento
		   $kod_procento = $_SESSION['slevovy_kod']['castka'];
		   $kod_castka = round(($cena_zbozi/100 * intval($kod_procento)));
		}
	}
	
	
	$cena_celkem = round($cena_zbozi + $doprava_cena_vyber + $platba_cena_vyber - $kod_castka);
	
	    // zjistíme jestli má nějaké zboží v košíku 
		$data_k = Db::queryRow('SELECT sum(pocet) AS POCET FROM kosik WHERE 1 '.$K->sql_kosik.' ', array());
		if($data_k['POCET'] > 0)
		{
				// platba kartou
				
				$karta = new KartaCSOB(__CSOB_PUB_KEY__, __CSOB_MERCHANTNUMBER__, __CSOB_PRIV_KEY__, __CSOB_PRIV_KEY_PASSWORD__, 'CZK', 'CZ' , $cena_celkem);
				$kosik = $karta->dataKosik();
	            $data = $karta->createPaymentInitData($_SESSION['reg_kosik']['id_obj'], $_SESSION['reg_kosik']['id_zak'], $kosik, $_SESSION['reg_kosik']['id_obj']);

	            $ch = curl_init($karta->url.'/payment/init');
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	                'Content-Type: application/json',
	                'Accept: application/json;charset=UTF-8'
	            ));
	
	            $result = curl_exec($ch);
	
	            if (curl_errno($ch)) 
	            {
	                $error .= 'payment/init failed, reason: ' . htmlspecialchars(curl_error($ch)).'<br>';
	            }
	
	            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	            
	            if ($httpCode != 200) 
	            {
	                $error .= 'payment/init failed, http response: ' . htmlspecialchars($httpCode).'<br>';   
	            }
	
	            curl_close($ch);
	            
	
	            $result_array = json_decode($result, true);
	            
	            if (is_null($result_array ['resultCode'])) 
	            {
	                $error .= 'payment/init failed, missing resultCode<br>';

	            }
	
	            if ($karta->verifyResponse($result_array) == false) 
	            {
	                $error .= 'payment/init failed, unable to verify signature<br>';
	            }
	
	            if ($result_array ['resultCode'] != '0') 
	            {
	                $error .= 'payment/init failed, reason: ' . htmlspecialchars($result_array ['resultMessage']).'<br>';

	            }
	            
	            if($error)
	            {
				    // chyby
				    $nadpis = 'Platba kartou';
				    $obsah = $error;
				  
				    $sablonka = new Sablonka();	
					// generujeme SEO meta
					$Seo = new Seo($this->skript,$_SESSION['staticke_stranky'],$this->parametry,$this->get_parametry);
					$sablonka->pridejDoSablonky('{seometa}',$Seo->generujSeo(),'html');
				
					
					$Topmenu = new TopMenu($this->skript,$this->parametry);
					$sablonka->pridejDoSablonky('{infolista}',$Topmenu->infoLista(2),'html'); 
					$sablonka->pridejDoSablonky('{top_menu}',$Topmenu->zobrazTopMenu(),'html'); 
					$sablonka->pridejDoSablonky('{menu_kategorii}',$Topmenu->menuKategorieUvod(),'html'); 
					
					$Kosik = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
					$sablonka->pridejDoSablonky('{kosik}',$Kosik->kosikTop(),'html');
				
					$sablonka->pridejDoSablonky('{drobinka}',$Seo->generujDrobinku(),'html');
					$sablonka->pridejDoSablonky('{nadpis}',$nadpis,'html');
					$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');
				
					
					// info okno
					$Infookno = new InfoOkno($this->skript);
					$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');
				
					 echo $sablonka->generujSablonku('txt'); 
				}
				else
				{
					$payId = $result_array ['payId'];
					$params = $karta->createGetParams($payId);
					$url_brana = htmlspecialchars($karta->url . '/payment/process/' . $params, ENT_QUOTES);
					header('Location: '.$url_brana);

				}
	
	           
               
	      

		}
		else
		{
		
			$nadpis = 'Platba kartou';
		    $obsah = 'V košíku nemáte aktuálně žádné zboží.';
		  
		    $sablonka = new Sablonka();	
			// generujeme SEO meta
			$Seo = new Seo($this->skript,$_SESSION['staticke_stranky'],$this->parametry,$this->get_parametry);
			$sablonka->pridejDoSablonky('{seometa}',$Seo->generujSeo(),'html');
		
			
			$Topmenu = new TopMenu($this->skript,$this->parametry);
			$sablonka->pridejDoSablonky('{infolista}',$Topmenu->infoLista(2),'html'); 
			$sablonka->pridejDoSablonky('{top_menu}',$Topmenu->zobrazTopMenu(),'html'); 
			$sablonka->pridejDoSablonky('{menu_kategorii}',$Topmenu->menuKategorieUvod(),'html'); 
			
			$Kosik = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
			$sablonka->pridejDoSablonky('{kosik}',$Kosik->kosikTop(),'html');
		
			$sablonka->pridejDoSablonky('{drobinka}',$Seo->generujDrobinku(),'html');
			$sablonka->pridejDoSablonky('{nadpis}',$nadpis,'html');
			$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');
		
			
			// info okno
			$Infookno = new InfoOkno($this->skript);
			$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');
		
			 echo $sablonka->generujSablonku('txt'); 
	 
		}
}
else
{
    $nadpis = 'Platba kartou';
    $obsah = 'Nejsou zadány potřebné údaje. Vraťte se prosím do košíku a projděte všemi kroky.';
  
    $sablonka = new Sablonka();	
	// generujeme SEO meta
	$Seo = new Seo($this->skript,$_SESSION['staticke_stranky'],$this->parametry,$this->get_parametry);
	$sablonka->pridejDoSablonky('{seometa}',$Seo->generujSeo(),'html');

	
	$Topmenu = new TopMenu($this->skript,$this->parametry);
	$sablonka->pridejDoSablonky('{infolista}',$Topmenu->infoLista(2),'html'); 
	$sablonka->pridejDoSablonky('{top_menu}',$Topmenu->zobrazTopMenu(),'html'); 
	$sablonka->pridejDoSablonky('{menu_kategorii}',$Topmenu->menuKategorieUvod(),'html'); 
	
	$Kosik = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
	$sablonka->pridejDoSablonky('{kosik}',$Kosik->kosikTop(),'html');

	$sablonka->pridejDoSablonky('{drobinka}',$Seo->generujDrobinku(),'html');
	$sablonka->pridejDoSablonky('{nadpis}',$nadpis,'html');
	$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');

	
	// info okno
	$Infookno = new InfoOkno($this->skript);
	$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

	 echo $sablonka->generujSablonku('txt'); 
}

        
?>
