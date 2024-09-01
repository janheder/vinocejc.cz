<?php
// návrat z platební brány ČSOB - prezentační vrstva

$nadpis = 'Platba kartou';
$_GET['krok'] = 5;
   
// úprava brány z 17.1.2022 - nové prohlížeče mažou cookies po opuštění stránky
// již nemůžeme kontrolovat přihlášení, protože ID sešny, která je uložena v cookies je smazaná

//var_dump($_SESSION);
 
 
if($_POST['signature'] && $_POST['merchantData'])
{ 
	
	$cislo_objednavky = base64_decode($_POST['merchantData']);
	
	// zjistíme kdy byla objednávka realizována a pokud se volá skript později než 30 minut po objednávce tak vypíšeme chybu
	$data_ot = Db::queryRow('SELECT datum, id_zakaznik FROM objednavky WHERE cislo_obj=? ', array(intval($cislo_objednavky)));
	if($data_ot['datum'])
	{
	   $cas_nyni = time();	
	   if(($cas_nyni - 3600) > $data_ot['datum'])
	   {
	      die('Neplatná operace');
	   }
	}
	
	
    $K = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
	$cena_zbozi = $K->kosikCenaCelkem();
    $karta = new KartaCSOB(__CSOB_PUB_KEY__, __CSOB_MERCHANTNUMBER__, __CSOB_PRIV_KEY__, __CSOB_PRIV_KEY_PASSWORD__, 'CZK', 'CZ' , $cena_zbozi);
    
    // pouze paymentStatus = 4 znamená, že byla potvrzena !!!!!
    
    if($_POST['resultCode']==0 && ($_POST['paymentStatus']==4 ||  $_POST['paymentStatus']==7 ||  $_POST['paymentStatus']==8))
    {
	            if ($karta->verifyResponse($_POST) == false) 
	            {
	                $obsah = 'Platba kartou <b style="color: red;"> NEBYLA úspěšná</b>. '.strip_tags($_POST['resultMessage']).'<br>Zkuste prosím projít všemi kroky košíku a znovu provést platbu, případně vybrat jiný způsob platby.';
	            }
	            else
	            {
					// pokud máme v sešně platný slevový kód tak musíme odečíst
		            $kod_castka = 0;
		            if($_SESSION['slevovy_kod']['id'])
		            {
					    if($_SESSION['slevovy_kod']['typ']==1)
					    {
						   // částka
						   $kod_castka = $_SESSION['slevovy_kod']['castka'];
						   $kod = $_SESSION['slevovy_kod']['kod'];
						}
						elseif($_SESSION['slevovy_kod']['typ']==2)
						{
						   // procento
						   $kod_procento = $_SESSION['slevovy_kod']['castka'];
						   $kod = $_SESSION['slevovy_kod']['kod'].' ( sleva '.$_SESSION['slevovy_kod']['castka'].'%)';
						   
						   // musíme zjistit cenu za košík *****************************************************
						    
						   $kod_castka = round(($cena_zbozi/100 * intval($kod_procento)));
						}
					}
			
					$obsah = '  <div class="cart-empty">
					            <h1 class="cart-empty__title">Platba kartou byla úspěšná</h1>
					            <p class="cart-empty__text">Proces nákupu je tímto ukončen. Rekapitulace objednávky byla odeslána na Váš email.</p>
					            <a class="btn --green" href="/">Zpět do obchodu</a>
					            <div class="cart-empty__alert"> <img src="/img/icons/info.svg" alt="Informace">
					               <div class="cart-empty__alert-text">Pokud máte problém s objednávkou, kontaktujte nás na '.__FORM_EMAIL__.' nebo na telefonním čísle '.__TELEFON__.'</div>
					            </div>
					          </div>';
					
					// změníme stav u této objednávky na úspěšná platba kartou
					if($cislo_objednavky)
					{
						$data_update = array('id_stav' =>8,'card'=>'Úspěšná platba kartou');
						$where_update = array('cislo_obj' => intval($cislo_objednavky));
						$query_update = Db::update('objednavky', $data_update, $where_update);
					}
					
					// odešleme email
					$K->objednavka($data_ot['id_zakaznik'],$kod_castka,1,1);
					
					// google el. obchod
					$obsah .= $K->googleElObchod();	
					
					// sklik konverze
					$obsah .= $K->sklikKonverze();	
					
					// zboží konverze
					$obsah .= $K->zboziKonverze();
					
					// heureka konverze
				    $obsah .= $K->heurekaKonverze();
				    
				    // heureka ověřeno zákazníky
					if($_SESSION['reg_kosik']['nesouhlas_heureka']==0 && __HEUREKA_ID_OVERENO_ZAKAZNIKY__)
					{
					     $url_heureka = 'https://www.heureka.cz/direct/dotaznik/objednavka.php?id='.__HEUREKA_ID_OVERENO_ZAKAZNIKY__.'&email='.$_SESSION['reg_kosik']['email'].'&orderid='.$this->id_obj;
						 file_get_contents($url_heureka);
					}
					
					// vymažeme údaje
					$K->smazatVseZKosiku();
					unset($_SESSION['slevovy_kod']);
					unset($_SESSION['reg_kosik']);	
					unset($_SESSION['doprava']);
					unset($_SESSION['platba']);
					
				}
	}
	else
	{
		$obsah = '<div class="cart-empty">
            <div class="cart-empty__img"> <img src="/img/cart-card.svg" alt="Karta zamítnuta"></div>
            <h1 class="cart-empty__title">Platba kartou nebyla úspěšná ';
            
            if($_POST['resultMessage']!='OK'){  $obsah .= strip_tags($_POST['resultMessage']);}
            
            $obsah .= '</h1>
            <p class="cart-empty__text">Zkuste projít všemi kroky košíku znovu a provést platbu.</p><a class="btn --green" href="/kosik">Zpět do košíku</a>
            <div class="cart-empty__alert"> <img src="/img/icons/info.svg" alt="Informace">
              <div class="cart-empty__alert-text">Pokud máte problém s objednávkou, kontaktujte nás na '.__FORM_EMAIL__.' nebo na telefonním čísle '.__TELEFON__.'</div>
            </div>
          </div>';
	}
}
else
{
	$obsah = 'Platnost sešny skončila, přihlaste se prosím znovu.';
}


  
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


        
?>
