<?php
define('__WEB_DIR__', '..');
require('./init.php');


switch ($_GET['typ'])
{

case 'naseptavac':

$term = sanitize($_POST['kw']);			
if($term)
{
	

 // produkty
 echo ' <div class="searchAutocomplete__section">';
 
 
  /*$data_p = Db::queryAll("SELECT P.id, P.nazev, P.str 
  FROM produkty P 
  LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
  WHERE P.aktivni=? AND V.aktivni_var=? AND (P.nazev LIKE '%".$term."%' OR P.popis_kratky LIKE '%".$term."%' OR V.kat_cislo_var LIKE '%".$term."%' OR V.nazev_var LIKE '%".$term."%' ) 
  GROUP BY P.id ORDER BY P.nazev LIMIT 5", array(1,1));*/
  

  $searchbox_arr = explode(' ',$term);

  $sb_sql_r = array();
  foreach( $searchbox_arr as $sb_key=>$sb_val)
  {
    $sb_sql_r[] = ' +'.$sb_val;
  }

  $sb_sql = implode(' ',$sb_sql_r);
  
  if(substr($sb_sql, -1)=='+')
  {
    $sb_sql = substr($sb_sql, 0, -1);
  }
  
				
  /*				
  $data_p = Db::queryAll("SELECT P.id, P.nazev, P.str
  FROM produkty P 
  LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
  WHERE P.aktivni=? AND V.aktivni_var=? 
  AND (MATCH(P.nazev) AGAINST ('".$term."*' IN BOOLEAN MODE) OR MATCH(V.kat_cislo_var) AGAINST ('".$term."*' IN BOOLEAN MODE))
  GROUP BY P.id ORDER BY P.id DESC LIMIT 5", array(1,1));*/
  
  $data_p = Db::queryAll("SELECT P.id, P.nazev, P.str
  FROM produkty P 
  LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
  WHERE P.aktivni=? AND V.aktivni_var=? 
  AND (MATCH(P.nazev) AGAINST ('".$sb_sql."*' IN BOOLEAN MODE) OR MATCH(V.kat_cislo_var) AGAINST ('".$sb_sql."*' IN BOOLEAN MODE))
  GROUP BY P.id ORDER BY P.id DESC LIMIT 5", array(1,1));
  if($data_p !== false ) 
  {
	  echo '<div class="searchAutocomplete__title">Produkty</div>';
	  
	  foreach ($data_p as $row_p) 
	  {
	     echo '<div class="searchAutocomplete__item"> <a class="searchAutocomplete__link" href="/produkty/'.$row_p['str'].'-'.$row_p['id'].'">'.$row_p['nazev'].'</a></div>';
	  }
  }
  
                  
 echo '</div>';
   
   
    // kategorie
 echo ' <div class="searchAutocomplete__section">';
 
 /*
  $data_k = Db::queryAll("SELECT id, nazev, str 
  FROM kategorie 
  WHERE aktivni=? AND (nazev LIKE '%".$term."%' OR popis LIKE '%".$term."%' ) 
  ORDER BY nazev LIMIT 5", array(1));*/
  $data_k = Db::queryAll("SELECT id, nazev, str 
  FROM kategorie 
  WHERE aktivni=? AND (MATCH(nazev) AGAINST ('".$sb_sql."*' IN BOOLEAN MODE))
  ORDER BY nazev LIMIT 5", array(1));
  if($data_k !== false ) 
  {
	  echo '<div class="searchAutocomplete__title">Kategorie</div>';
	  
	  foreach ($data_k as $row_k) 
	  {
	     echo '<div class="searchAutocomplete__item"> <a class="searchAutocomplete__link" href="/kategorie/'.$row_k['str'].'-'.$row_k['id'].'">'.$row_k['nazev'].'</a></div>';
	  }
  }
  
                  
 echo '</div>';               
                  
 
}		
 
break;



/***********************************************************************/

case 'kosik_zmena_poctu':

 if($_POST['idp'] && $_POST['idv'] && $_POST['pocet'])
 {
	if($_POST['pocet'] < 1)
    {
	  $_POST['pocet'] = 1;
	}
	
    $K = new Kosik('kosik','','',__TYP_CENY__,__SLEVOVA_SKUPINA__);
    $K->zmenPocetVKosiku($_POST['idp'],$_POST['idv'],$_POST['pocet']);
    $K->prepocetDopravaPlatba();
    echo 'OK';
 
 }
 

break;

/***********************************************************************/

case 'slevovy_kod':

 if($_POST['kod'])
 {
    $kod = sanitize($_POST['kod']);
    $data_kod = Db::queryRow('SELECT * FROM slevove_kody WHERE kod=? AND aktivni=? AND (datum_od <= '.time().' AND datum_do >= '.time().' )  ', array($kod,1));
    if($data_kod['kod'])
    {
	   // kód nemůžeme přidat do db košík, protože pokud je kód na procenta a zákazník by do košíku přidával další produkty nebo měnil počty tak by výpočet neseděl
	   // proto uložíme všechny údaje do sešny
	   // pokud je opakovane_pouziti=0 tak kód deaktivujeme
	   if($data_kod['opakovane_pouziti']==0)
	   {
	      $data_update = array('aktivni' => 0);
		  $where_update = array('id' => $data_kod['id']);
		  $query_update = Db::update('slevove_kody', $data_update, $where_update);
	   }
	   
	   $sl_kod_data = array(
						     'id' => $data_kod['id'],
						     'kod' => $data_kod['kod'],
						     'typ' => $data_kod['typ'],
						     'opakovane_pouziti' => $data_kod['opakovane_pouziti'],
						     'castka' => $data_kod['castka']
						     );
	   
	   $_SESSION['slevovy_kod'] = $sl_kod_data;
	   
	   echo '1';
	   
	}
	else
	{
	  echo '0';
	  
	}
 
 
 }
 

break;



/***********************************************************************/

case 'uplatni_slevovy_kod':

 if($_SESSION['slevovy_kod'])
 {
    // vypočítáme částku dle typu slevového kódu
    if($_SESSION['slevovy_kod']['typ']==1)
    {
	   // částka
	   $kod_castka = $_SESSION['slevovy_kod']['castka'];
	   $kod = $_SESSION['slevovy_kod']['kod'];
	   $kod_procento2 = '';
	}
	elseif($_SESSION['slevovy_kod']['typ']==2)
	{
	   // procento
	   $kod_procento = $_SESSION['slevovy_kod']['castka'];
	   $kod = $_SESSION['slevovy_kod']['kod'].' ( sleva '.$_SESSION['slevovy_kod']['castka'].'%)';
	   $kod_procento2 = round(($_SESSION['slevovy_kod']['castka'] / 100),2);
	   
	    // musíme zjistit cenu za košík *****************************************************
	    $cena_celkem = 0;
	    $K = new Kosik('kosik','','',__TYP_CENY__,__SLEVOVA_SKUPINA__);
	    $cena_celkem = $K->kosikCenaCelkem();
	    
	    $kod_castka = round(($cena_celkem/100 * intval($kod_procento)));
	}
	
	 $ret .= '<div class="cart-item --code">
                      <div class="cart-item-thumb-wrap">
                        <div class="cart-item-thumb"><img src="/img/sleva.png" alt="sleva" title="sleva" ></div>
                      </div>
                      
                       <div class="cart-item-content"> 
                           <div class="cart-item-main">Slevový kód '.$kod.'
                             <div class="cart-item-code-price" id="codePriceWrap">-<span id="codePrice" data-percentage="'.str_replace(',','.',$kod_procento2).'">'.round($kod_castka).'</span> '.__MENA__.'</div>

                           </div>
                        
                           <div class="cart-item-bottom">

                           </div> 
                      </div>
                    </div>';	
			

			echo $ret;


 }

 

break;


/***********************************************************************/

case 'vygeneruj_platbu':

 if($_POST['idd'])
 {
   
      $idd = intval($_POST['idd']); 
      $ret = '';
      $K = new Kosik('kosik','','',__TYP_CENY__,__SLEVOVA_SKUPINA__);
	  $cena_celkem = $K->kosikCenaCelkem();
	    
      $data_dp = Db::queryRow('SELECT platba_arr FROM doprava WHERE id=? AND aktivni=? ', array($idd,1));
	  if($data_dp)
	  {
	     $platba_arr = unserialize($data_dp['platba_arr']);
	     $data_platba = Db::queryAll('SELECT * FROM platba WHERE aktivni=? AND id IN('.implode(',',$platba_arr).') ORDER BY razeni ASC  ', array(1));
		 if($data_platba)
		 {
		     $xp = 1;
			 foreach($data_platba as $row_platba)
			 {
				
					if(__DOPRAVA_ZDARMA_TYP_2__==2)
					{
						  // zdarma i platba při splnění podmínek
						  
						  if(__DOPRAVA_ZDARMA_TYP__==1)
		                  {
						    // všechny produkt v košíku musí mít příznak doprava zdarma
						    if($K->dopravaZdarmaVse())
						    {
							   $platba_cena = 0;
							}
							else
							{
							   $platba_cena = $row_platba['cena'];
							}
						  }
						  else
						  {
						    // stačí jeden produkt s příznakem doprava zdarma
						    if($K->dopravaZdarma())
						    {
							   $platba_cena = 0;
							}
							else
							{
							   $platba_cena = $row_platba['cena'];
							}
						  }
						  
						  if($cena_celkem > __POSTOVNE_ZDARMA__)
						  {
						     $platba_cena = 0;
						  }
					}
					else
					{
						$platba_cena = $row_platba['cena'];
					}
											
											
				$ret .= '<label class="cart-table-shipping-item" for="payment-'.$xp.'" >
				  <input type="radio" id="payment-'.$xp.'" name="platba" value="'.$row_platba['id'].'" ';
				if($_SESSION['platba']['id']==$row_platba['id']){$ret .= ' checked ';} 
				$ret .= ' required>
				  <div class="cart-table-shipping-text">'.$row_platba['nazev'].'</div>
				  <div class="cart-table-shipping-price"><span>'.$platba_cena.'</span> '.__MENA__.'</div>
				</label>';
					
			    $xp++;
			 }
		 }
		 
		 echo $ret;
	  
	  }
 
 }
		
 
break;


/***********************************************************************/

case 'kontrola_uz_jm':

 $data = Db::queryAffected('SELECT id FROM zakaznici WHERE uz_jm=? ', array($_POST['uname']));
 echo $data;
		
		
 
break;

/***********************************************************************/


case 'kontrola_email':

 $data = Db::queryAffected('SELECT id FROM zakaznici WHERE aktivni=1 AND bez_registrace=0 AND email=?  ', array($_POST['uname']));
 echo $data;
		
		
		
break;


/***********************************************************************/


case 'kontrola_baliky':

 if($_POST['_p']==1)
 {
   // balík na poštu
   if(!$_SESSION['doprava']['id_cp_na_postu'])
   {
     echo 'Nevybrali jste pobočku pro balík na Poštu';
   }

 }
 elseif($_POST['_p']==2)
 {
	// balík na poštu
   if(!$_SESSION['doprava']['id_cp_balikovna'])
   {
     echo 'Nevybrali jste pobočku pro balík do Balíkovny';
   }
 }
 elseif($_POST['_p']==3)
 {
	// zásilkovna
   if(!$_SESSION['doprava']['id_zasilkovna'])
   {
     echo 'Nevybrali jste pobočku pro Zásilkovnu';
   }
 }
 elseif($_POST['_p']==4)
 {
	// ppl_parcel
   if(!$_SESSION['doprava']['id_ppl_parcel'])
   {
     echo 'Nevybrali jste pobočku pro PPL Parcel';
   }
 }
 elseif($_POST['_p']==5)
 {
	// dpd_pickup
   if(!$_SESSION['doprava']['id_dpd_pickup'])
   {
     echo 'Nevybrali jste pobočku pro DPD Pickup';
   }
 }
		
		
		
break;


/***********************************************************************/


case 'nastav_zasilkovnu':

 if($_POST['points'] && $_POST['idd'])
 {
	$point = json_decode($_POST['points'],true);
	
	$K = new Kosik('kosik','','',__TYP_CENY__,__SLEVOVA_SKUPINA__);
	$K->nastavSessDoprava(intval($_POST['idd']));
	
    $_SESSION['doprava']['id_zasilkovna'] = $point['id'];
    $_SESSION['doprava']['nazev_zasilkovna'] = $point['place'];
    $_SESSION['doprava']['ulice_zasilkovna'] = $point['street'];
    $_SESSION['doprava']['obec_zasilkovna'] = $point['city'];
    $_SESSION['doprava']['psc_zasilkovna'] = $point['zip'];
    $_SESSION['doprava']['id_stat_zasilkovna'] = array_search(strtoupper($point['country']), $_SESSION['staty_iso_arr']);
   
    echo '1';

 }
	
		
		
break;


/***********************************************************************/


case 'nastav_ppl_parcel':

 if($_POST['points'] && $_POST['idd'])
 {
	$point = json_decode($_POST['points'],true);
	
	$K = new Kosik('kosik','','',__TYP_CENY__,__SLEVOVA_SKUPINA__);
	$K->nastavSessDoprava(intval($_POST['idd']));
	
    $_SESSION['doprava']['id_ppl_parcel'] = $point['code'];
    $_SESSION['doprava']['nazev_ppl_parcel'] = $point['name'];
    $_SESSION['doprava']['ulice_ppl_parcel'] = $point['street'];
    $_SESSION['doprava']['obec_ppl_parcel'] = $point['city'];
    $_SESSION['doprava']['psc_ppl_parcel'] = $point['zipCode'];
    $_SESSION['doprava']['id_stat_ppl_parcel'] = array_search(strtoupper($point['country']), $_SESSION['staty_iso_arr']);
    

    echo '1';

 }
	
		
		
break;


/***********************************************************************/


case 'nastav_dpd_pickup':

 if($_POST['points'] && $_POST['idd'])
 {
	$point = json_decode($_POST['points'],true);
	
	$K = new Kosik('kosik','','',__TYP_CENY__,__SLEVOVA_SKUPINA__);
	$K->nastavSessDoprava(intval($_POST['idd']));
	
    $_SESSION['doprava']['id_dpd_pickup'] = $point['id'];
    $_SESSION['doprava']['nazev_dpd_pickup'] = $point['contactInfo']['name'];
    $_SESSION['doprava']['ulice_dpd_pickup'] = $point['location']['address']['street'];
    $_SESSION['doprava']['obec_dpd_pickup'] = $point['location']['address']['city'];
    $_SESSION['doprava']['psc_dpd_pickup'] = $point['location']['address']['zip'];
    $_SESSION['doprava']['id_stat_dpd_pickup'] = array_search(strtoupper($point['location']['address']['country']), $_SESSION['staty_iso_arr']);
    

    echo '1';

 }
	
		
		
break;



/***********************************************************************/

case 'smazat_postu':

 
 unset($_SESSION['doprava']['id_cp_na_postu']);
 unset($_SESSION['doprava']['nazev_cp_na_postu']);
 unset($_SESSION['doprava']['adresa_cp_na_postu']);
 unset($_SESSION['doprava']['psc_cp_na_postu']);
 
 unset($_SESSION['doprava']['id_cp_balikovna']);
 unset($_SESSION['doprava']['nazev_cp_balikovna']);
 unset($_SESSION['doprava']['adresa_cp_balikovna']);
 unset($_SESSION['doprava']['psc_cp_balikovna']);
 
 echo '1';		
		
		
break;


/***********************************************************************/

case 'smazat_zasilkovnu':

 
 unset($_SESSION['doprava']['id_zasilkovna']);
 unset($_SESSION['doprava']['nazev_zasilkovna']);
 unset($_SESSION['doprava']['ulice_zasilkovna']);
 unset($_SESSION['doprava']['obec_zasilkovna']);
 unset($_SESSION['doprava']['psc_zasilkovna']);
 unset($_SESSION['doprava']['id_stat_zasilkovna']);

 
 echo '1';		
		
		
break;


/***********************************************************************/


case 'smazat_ppl_parcel':

 
 unset($_SESSION['doprava']['id_ppl_parcel']);
 unset($_SESSION['doprava']['nazev_ppl_parcel']);
 unset($_SESSION['doprava']['ulice_ppl_parcel']);
 unset($_SESSION['doprava']['obec_ppl_parcel']);
 unset($_SESSION['doprava']['psc_ppl_parcel']);
 unset($_SESSION['doprava']['id_stat_ppl_parcel']);

 
 echo '1';		
		
		
break;


/***********************************************************************/

case 'smazat_dpd_pickup':

 
 unset($_SESSION['doprava']['id_dpd_pickup']);
 unset($_SESSION['doprava']['nazev_dpd_pickup']);
 unset($_SESSION['doprava']['ulice_dpd_pickup']);
 unset($_SESSION['doprava']['obec_dpd_pickup']);
 unset($_SESSION['doprava']['psc_dpd_pickup']);
 unset($_SESSION['doprava']['id_stat_dpd_pickup']);

 
 echo '1';		
		
		
break;


/***********************************************************************/

case 'registrace':
 
  $err = '';

 // zkontrolujeme povinné položky, duplicitu emailu a captcha
if(kontrola_ref())
{
   // chybný referer
}
else
{

 $data_email = Db::queryAffected('SELECT id FROM zakaznici WHERE aktivni=1 AND bez_registrace=0 AND email=?  ', array($_POST['email']));
 
 if(!$_POST['jmeno'])
 {
   $err .= 'Není vyplněno jméno<br>';
 }
 if(!$_POST['prijmeni'])
 {
   $err .= 'Není vyplněno příjmení<br>';
 }
 if(!$_POST['telefon'])
 {
   $err .= 'Není vyplněn telefon<br>';
 }
 if(!$_POST['email'])
 {
   $err .= 'Není vyplněn email<br>';
 }
 if(!$_POST['heslo'])
 {
   $err .= 'Není vyplněno heslo<br>';
 }
 if($data_email > 0)
 {
   $err .= 'Tato emailová adresa se již v databázi nachází. Zvolte jiný email nebo si nechejte zaslat zapomenuté heslo.<br>';
 }
 if(!$_POST['dodaci_ulice'])
 {
   $err .= 'Není vyplněna ulice<br>';
 }
 if(!$_POST['dodaci_cislo'])
 {
   $err .= 'Není vyplněno číslo popisné<br>';
 }
 if(!$_POST['dodaci_obec'])
 {
   $err .= 'Není vyplněna obec<br>';
 }
 if(!$_POST['dodaci_psc'])
 {
   $err .= 'Není vyplněno PSČ<br>';
 }
 if(!$_POST['captcha'])
 {
   $err .= 'Není zaškrtnutna captcha<br>';
 }
 
// kontrola captchy
$captcha_secret_key = __CAPTCHA_SECRET_KEY__;
$captcha = $_POST['captcha'];

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$captcha_secret_key."&response=".$captcha);
$response = json_decode($verify, true);
//if($response->success == true)
if($response['success']==1)
{
  // OK
}
else
{
   $err .= 'Chyba při ověření captcha<br>';
   //$err .= $_POST['captcha'];
}
 

 if(!$err)
 {
   // vše je vyplněno
   // můžeme uložit
   $options_pass = ['cost' => 12,];

		$data_insert = array(
				    'jmeno' => strip_tags($_POST['jmeno']),
					'prijmeni' => strip_tags($_POST['prijmeni']),
					'uz_jmeno' => strip_tags($_POST['email']),
					'heslo' => password_hash($_POST['heslo'], PASSWORD_BCRYPT, $options_pass),
					'email' => strip_tags($_POST['email']),
					'telefon' => strip_tags($_POST['telefon']),
					
					'dodaci_nazev' => strip_tags($_POST['dodaci_nazev']),
					'dodaci_ulice' => strip_tags($_POST['dodaci_ulice']),
					'dodaci_cislo' => strip_tags($_POST['dodaci_cislo']),
					'dodaci_obec' => strip_tags($_POST['dodaci_obec']),
					'dodaci_psc' => strip_tags($_POST['dodaci_psc']),
					'dodaci_id_stat' => intval($_POST['dodaci_id_stat']),
					
					'fakturacni_jmeno' => strip_tags($_POST['fakturacni_jmeno']),
					'fakturacni_firma' => strip_tags($_POST['fakturacni_firma']),
					'fakturacni_ulice' => strip_tags($_POST['fakturacni_ulice']),
					'fakturacni_cislo' => strip_tags($_POST['fakturacni_cislo']),
					'fakturacni_obec' => strip_tags($_POST['fakturacni_obec']),
					'fakturacni_psc' => strip_tags($_POST['fakturacni_psc']),
					'fakturacni_id_stat' => intval($_POST['fakturacni_id_stat']),
					'ic' => strip_tags($_POST['ic']),
					'dic' => strip_tags($_POST['dic']),
					
					'datum' => time(),
					'nl' => intval($_POST['nl']),
					'aktivni' => 0
				     );
			  
			  $query_insert = Db::insert('zakaznici', $data_insert);
			  
			  	          
			  $heslo_delka = strlen($_POST['heslo']);
			  $heslo_do_emailu = '';
			  for ($i = 1; $i <= $heslo_delka; $i++) 
			  {
				$heslo_do_emailu .= '*';
			  }
			  
			  // aktivační odkaz
			  $aktivacni_odkaz = __URL__.'/aktivace-uctu?eml_ver='.openssl_encrypt($query_insert.'|'.time(), __IV_CIPHERING__, __HESLO_ENCRYPT__, 0, __IV__);
			  		     
		      
		    
		      $body_reg = "Registrace na ".__URL__." ze dne ".date('d.m.Y')."\n\nKlikněte na odkaz zde: ".$aktivacni_odkaz."  pro aktivaci vašeho účtu. Aktivace je možná do 24 hodin od provedení registrace.\n\nRekapitulace zadaných údajů\n===================\n\nJméno: ".strip_tags($_POST['jmeno'])." ".strip_tags($_POST['prijmeni'])."\nE-mail: ".strip_tags($_POST['email'])."\nHeslo: ".$heslo_do_emailu."\n\nDodací adresa:\n".strip_tags($_POST['dodaci_nazev'])."\n".strip_tags($_POST['dodaci_ulice'])." ".strip_tags($_POST['dodaci_cislo'])."\n".strip_tags($_POST['dodaci_obec'])."\n".strip_tags($_POST['dodaci_psc'])."\n".$_SESSION['staty_arr'][intval($_POST['dodaci_id_stat'])]."\n\n";
			  
			  if($_POST['fakturacni_jmeno'] || $_POST['fakturacni_firma'] || $_POST['fakturacni_obec'])	
			  {
			    $body_reg .= "Fakturační adresa:\n".strip_tags($_POST['fakturacni_jmeno'])."\n".strip_tags($_POST['fakturacni_firma'])."\n".strip_tags($_POST['fakturacni_ulice'])." ".strip_tags($_POST['fakturacni_cislo'])."\n".strip_tags($_POST['fakturacni_obec'])."\n".strip_tags($_POST['fakturacni_psc'])."\n".$_SESSION['staty_arr'][intval($_POST['fakturacni_id_stat'])]."\nIČ: ".strip_tags($_POST['ic'])."\nDIČ: ".strip_tags($_POST['dic'])."\n";
			  }
			  
			  if($_POST['nl']==1)
			  {
			     $body_reg .= "Newsletter: ANO";
			  }
			  else
			  {
				 $body_reg .= "Newsletter: NE";
			  }
			  
			  $body_reg .= "\n\n".__KONTAKTY_PATA__;
			  

		    
		      $eml = New Email('plaintext',false);
			  $eml->nastavFrom(__EMAIL_FROM__);
			  $eml->nastavTo(sanitize($_POST['email']));
			  $eml->nastavSubject('Registrační údaje');
			  $eml->nastavBody($body_reg);
			  $eml_odeslani = $eml->odesliEmail();

 }
 else
 {
   echo $err;
 }

}


 
		
		
		
break;


/***********************************************************************/


case 'form_1':
 
  $err = '';

 // zkontrolujeme povinné položky a captcha
if(kontrola_ref())
{
   // chybný referer
}
else
{

 
 if(!$_POST['jmeno'])
 {
   $err .= 'Není vyplněno jméno<br>';
 }
 if(!$_POST['telefon'])
 {
   $err .= 'Není vyplněn telefon<br>';
 }
 if(!$_POST['email'])
 {
   $err .= 'Není vyplněn email<br>';
 }
 if(!$_POST['captcha'])
 {
   $err .= 'Není zaškrtnutna captcha<br>';
 }
 
// kontrola captchy
$captcha_secret_key = __CAPTCHA_SECRET_KEY__;
$captcha = $_POST['captcha'];

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$captcha_secret_key."&response=".$captcha);
$response = json_decode($verify, true);
//if($response->success == true)
if($response['success']==1)
{
  // OK
}
else
{
   $err .= 'Chyba při ověření captcha<br>';
   //$err .= $_POST['captcha'];
}
 

 if(!$err)
 {
   // vše je vyplněno
		      $body_eml = "Poptávka na ".__URL__." ze dne ".date('d.m.Y')."\n\nProdukt: ".strip_tags($_POST['produkt_nazev']).", ID: ".strip_tags($_POST['produkt_id'])."\nJméno: ".strip_tags($_POST['jmeno'])."\nE-mail: ".strip_tags($_POST['email'])."\nTelefon: ".strip_tags($_POST['telefon'])."\nDotaz: ".strip_tags($_POST['info']);

		      $eml = New Email('plaintext',false);
			  $eml->nastavFrom(sanitize($_POST['email']));
			  $eml->nastavTo(__FORM_EMAIL__);
			  $eml->nastavSubject('Poptávka');
			  $eml->nastavBody($body_eml);
			  $eml_odeslani = $eml->odesliEmail();

 }
 else
 {
   echo $err;
 }

}

		
break;


/***********************************************************************/

case 'kontrola_captcha':

$captcha_secret_key = __CAPTCHA_SECRET_KEY__;
$captcha = $_POST['captcha'];

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$captcha_secret_key."&response=".$captcha);
$response = json_decode($verify, true);

if($response->success == true)
{
	// v pořádku
}
else
{
	echo time();
}
 	
		
 
break;


/***********************************************************************/




default:
 
}


Db::close();
?>
