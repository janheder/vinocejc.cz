<?php
// trida košík
// do tridy predavame 2 typy parametru v polich
// změna 16.2.2023 - v adminu se nastavuje jestli jsou ceny s nebo bez DPH

class Kosik
{

public $parametry; // parametry za lomitky
public $get_parametry;  // klasicke GET parametry
public $skript; // nazev stranky
public $cenova_skupina_zakaznika;  
public $slevova_skupina_zakaznika;  

	
	function __construct($skript,$parametry,$get_parametry,$cenova_skupina_zakaznika='A',$slevova_skupina_zakaznika)
	{
		
	 $this->skript = $skript;
	 $this->parametry = $parametry;
	 $this->get_parametry = $get_parametry;
	 $this->typ_ceny = 'cena_'.$cenova_skupina_zakaznika;
	 $this->slevova_skupina = $slevova_skupina_zakaznika;
	 
	     if($_SESSION['uzivatel'])
	     {  
		    $id_zak = $_SESSION['uzivatel']['id'];
		    $this->id_zakaznik = $id_zak;
		   
		    if($id_zak > 0)
		     {
			     $this->sql_kosik = " AND id_zakaznik=".intval($id_zak)." ";
			 }
			 else
			 {
				 $this->sql_kosik = " AND id_sess='".session_id()."' ";
			 }
	     }
	     else
	     {
				$this->sql_kosik = " AND id_sess='".session_id()."' ";
				$this->id_zakaznik = 0;
		 }
		 
		 // kroky
		 if($_GET['krok'])
		 {
		     $this->krok = intval($_GET['krok']);
		 }
		 else
		 {
		     $this->krok = 1;
		 }
		 
		 // doprava
		 if($this->krok==3 && $_POST['doprava'] && (!$_SESSION['doprava']['id_cp_na_postu'] && !$_SESSION['doprava']['id_cp_balikovna'] && !$_SESSION['doprava']['id_zasilkovna'] && !$_SESSION['doprava']['id_ppl_parcel'] && !$_SESSION['doprava']['id_dpd_pickup']))
		 { 
		     $this->nastavSessDoprava($_POST['doprava']);
		 }
		 
		 // platba
		 if($this->krok==3 && $_POST['platba'])
		 {
		     $this->nastavSessPlatba($_POST['platba']);
		 }


	}
	
	
	
	
	
	public function vlozDoKosiku($id_produkt,$id_varianta,$pocet)
	{   
		
		if(!isset($pocet)){$pocet_p = 1;}	
		else{$pocet_p = intval($pocet);}
		
		
		if($id_produkt && $id_varianta)
		{
			  $id_pr = intval($id_produkt);
			  $id_var = intval($id_varianta);
			  
			  $data_kk = Db::queryRow('SELECT id, pocet FROM kosik WHERE id_produkt=? AND id_var=? '.$this->sql_kosik.' ', array($id_pr,$id_var));
			  if($data_kk['id'])
			  {
			    // tento produkt je již v košíku
			    $data_update = array('pocet' => ($data_kk['pocet'] + $pocet_p));
				$where_update = array('id' => $data_kk['id']);
				$query_update = Db::update('kosik', $data_update, $where_update);
				
			  }
			  else
			  {
			    // není v košíku
			     $data_insert = array(
				'id_zakaznik' => $this->id_zakaznik,
			    'id_produkt' => $id_pr,
			    'id_var' => $id_var,
			    'pocet' => $pocet_p,
			    'id_sess' => session_id(),
			    'datum' => time()
			     );
				$query_insert = Db::insert('kosik', $data_insert);
				
			  }
			
			
		}
	 }  
   
 
 
 

 
	public function smazatZKosiku($id_produkt,$id_varianta)
	{			
		   if($id_produkt && $id_varianta)
		   {
			  $id_pr = intval($id_produkt);
			  $id_var = intval($id_varianta);
			  
			  $query_delete = Db::deleteAll('kosik', 'id_produkt="'.$id_pr.'" AND id_var='.$id_var.' '.$this->sql_kosik.' ');	
			  
		   }
	} 
	 
	 
	 
	public function smazatVseZKosiku()
	{			

			  $query_delete = Db::deleteAll('kosik', '1 '.$this->sql_kosik.' ');	
		   
	} 
	   
	   
	   
	   
	   
	   
	public function zmenPocetVKosiku($id_produkt,$id_varianta,$pocet)
	{
	
		   if($id_produkt && $id_varianta && $pocet)
			{
				  $id_pr = intval($id_produkt);
				  $id_var = intval($id_varianta);
				  $pocet_p = intval($pocet);
				  
				  $data_kk = Db::queryRow('SELECT id FROM kosik WHERE id_produkt=? AND id_var=? '.$this->sql_kosik.' ', array($id_pr,$id_var));
				  if($data_kk['id'])
				  {
				    $data_update = array('pocet' => $pocet_p);
					$where_update = array('id' => $data_kk['id']);
					$query_update = Db::update('kosik', $data_update, $where_update);
				  }
					
			}	
		
	}  
	
	
	
	
	public function kosikVahaCelkem()
	{
	  // vrátí váhu produktů v košíku v gramech
	  // neřešíme pokud produkt / varianta nemá vyplněnou váhu
	  
	    $vaha_celkem = 0;
		// zjistíme jestli má nějaké zboží v košíku 
		$data_k = Db::queryRow('SELECT sum(pocet) AS POCET FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
		if($data_k['POCET'] > 0)
		{
			
			// výpočet váhy
			$data_kc = Db::queryAll('SELECT id_var, pocet FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
			if($data_kc)
			{
			   
			   foreach($data_kc as $row_kc)
			   {
				   $data_var_vaha = Db::queryRow('SELECT V.vaha 
				   FROM produkty_varianty V 
				   WHERE V.id=? ', array($row_kc['id_var']));
				   if($data_var_vaha)
				   {
				     
					        $vaha_celkem = $vaha_celkem + ($data_var_vaha['vaha'] * $row_kc['pocet']);
				   
				   }
			   
			   }
		   }

			
			
		}
		
		return $vaha_celkem;
	  
	  
	}
	
	
	
	public function kosikCenaCelkem()
	{ 
		$cena_celkem = 0;
		// zjistíme jestli má nějaké zboží v košíku 
		$data_k = Db::queryRow('SELECT sum(pocet) AS POCET FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
		if($data_k['POCET'] > 0)
		{
			
			// výpočet ceny
			$data_kc = Db::queryAll('SELECT id_var, pocet FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
			if($data_kc)
			{
			   
			   foreach($data_kc as $row_kc)
			   {
				   $data_var_cena = Db::queryRow('SELECT V.'.$this->typ_ceny.' AS CENA, V.sleva, V.sleva_datum_od, V.sleva_datum_do, P.id_dph, D.dph 
				   FROM produkty_varianty V 
				   LEFT JOIN produkty P ON P.id=V.id_produkt
				   LEFT JOIN dph D ON D.id=P.id_dph
				   WHERE V.id=? ', array($row_kc['id_var']));
				   if($data_var_cena)
				   {
				     
					        $ceny = $this->vypocetCeny($data_var_cena['CENA'],$data_var_cena['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
					        $cena_celkem = $cena_celkem + ($ceny['cena_s_dph'] * $row_kc['pocet']);
				   
				   }
			   
			   }
		   }

			
			
		}
		
		return $cena_celkem;
	}
	
	
	
	
	public function nastavSessDoprava($idd)
	{
	 
	  if($idd)
	  {
	     $data_d = Db::queryRow('SELECT D.*, DAN.dph 
	     FROM doprava D 
	     LEFT JOIN dph DAN ON DAN.id=D.id_dph
	     WHERE D.id=? AND D.aktivni=? ', array(intval($idd),1));
	     if($data_d)
	     {
			  $cena_celkem = $this->kosikCenaCelkem();
			    	
		   	  if(__DOPRAVA_ZDARMA_TYP__==1)
			  {
			    // všechny produkt v košíku musí mít příznak doprava zdarma
			    if($this->dopravaZdarmaVse())
			    {
				   $doprava_cena = 0;
				}
				else
				{
				   $doprava_cena = $data_d['cena'];
				}
			  }
			  else
			  {
			    // stačí jeden produkt s příznakem doprava zdarma
			    if($this->dopravaZdarma())
			    {
				   $doprava_cena = 0;
				}
				else
				{
				   $doprava_cena = $data_d['cena'];
				}
			  }
			  
			  if($cena_celkem > __POSTOVNE_ZDARMA__)
			  {
			     $doprava_cena = 0;
			  }
			
			if(__CENY_ADM__==1)
			{
			 // ceny jsou bez DPH
		     $doprava_cena = round($doprava_cena * ($data_d['dph'] / 100 + 1));
		    }
			
			$doprava_data = array(
						     'id' => intval($idd),
						     'cena' => $doprava_cena,
						     'dph' => $data_d['dph'],
						     'nazev' => $data_d['nazev']
						     );
	   
			$_SESSION['doprava'] = $doprava_data;
			

		 }
	  }
	
	}
	
	
	
	public function nastavSessPlatba($idp)
	{
		
		  if($idp)
		  {
		     $data_p = Db::queryRow('SELECT P.*, DAN.dph 
		     FROM platba P 
		     LEFT JOIN dph DAN ON DAN.id=P.id_dph
		     WHERE P.id=? AND P.aktivni=? ', array(intval($idp),1));
		     if($data_p)
		     {
				  $cena_celkem = $this->kosikCenaCelkem();
				    	
			   	  if(__DOPRAVA_ZDARMA_TYP_2__==2)
					{
						  // zdarma i platba při splnění podmínek
						  
						  if(__DOPRAVA_ZDARMA_TYP__==1)
		                  {
						    // všechny produkt v košíku musí mít příznak doprava zdarma
						    if($this->dopravaZdarmaVse())
						    {
							   $platba_cena = 0;
							}
							else
							{
							   $platba_cena = $data_p['cena'];
							}
						  }
						  else
						  {
						    // stačí jeden produkt s příznakem doprava zdarma
						    if($this->dopravaZdarma())
						    {
							   $platba_cena = 0;
							}
							else
							{
							   $platba_cena = $data_p['cena'];
							}
						  }
						  
						  if($cena_celkem > __POSTOVNE_ZDARMA__)
						  {
						     $platba_cena = 0;
						  }
					}
					else
					{
						$platba_cena = $data_p['cena'];
					}
					
					if(__CENY_ADM__==1)
					{
					 // ceny jsou bez DPH
				     $platba_cena = round($platba_cena * ($data_d['dph'] / 100 + 1));
				    }
				
				$platba_data = array(
							     'id' => intval($idp),
							     'cena' => $platba_cena,
							     'dph' => $data_p['dph'],
							     'nazev' => $data_p['nazev'],
							     'prevodem' => $data_p['prevodem'],
							     'karta' => $data_p['karta'],
							     'dobirka' => $data_p['dobirka']
							     );
		   
				$_SESSION['platba'] = $platba_data;

		   }	
	  
	    }
	
	}
	
	
	
	public function nastavSessZakaznik()
	{
		
				$zak_data = array(
							     'jmeno' => trim(strip_tags($_POST['jmeno'])),
							     'prijmeni' => trim(strip_tags($_POST['prijmeni'])),
							     'email' => trim(strip_tags($_POST['email'])),
							     'telefon' => trim(strip_tags($_POST['telefon'])),
							     'dodaci_nazev' => trim(strip_tags($_POST['dodaci_nazev'])),
							     'dodaci_ulice' => trim(strip_tags($_POST['dodaci_ulice'])),
							     'dodaci_cislo' => trim(strip_tags($_POST['dodaci_cislo'])),
							     'dodaci_obec' => trim(strip_tags($_POST['dodaci_obec'])),
							     'dodaci_psc' => trim(strip_tags($_POST['dodaci_psc'])),
							     'dodaci_id_stat' => intval($_POST['dodaci_id_stat']),
							     'fakturacni_jmeno' => trim(strip_tags($_POST['fakturacni_jmeno'])),
							     'fakturacni_firma' => trim(strip_tags($_POST['fakturacni_firma'])),
							     'fakturacni_ulice' => trim(strip_tags($_POST['fakturacni_ulice'])),
							     'fakturacni_cislo' => trim(strip_tags($_POST['fakturacni_cislo'])),
							     'fakturacni_obec' => trim(strip_tags($_POST['fakturacni_obec'])),
							     'fakturacni_psc' => trim(strip_tags($_POST['fakturacni_psc'])),
							     'fakturacni_id_stat' => intval($_POST['fakturacni_id_stat']),
							     'ic' => trim(strip_tags($_POST['ic'])),
							     'dic' => trim(strip_tags($_POST['dic'])),
							     'chci_registraci' => intval($_POST['cart-register']),
							     'nl' => intval($_POST['nl']),
							     'ip' => trim(strip_tags(getip())),
							     'nesouhlas_heureka' => intval($_POST['nesouhlas_heureka']),
							     'souhlas_ou' => 1

							     );
							     
				if($_POST['heslo'])
				{
				   $zak_data['heslo'] = strip_tags($_POST['heslo']);
				}			     
		   
				$_SESSION['reg_kosik'] = $zak_data;

		 
	
	}
	
	
	
	
	
	public function kosikTop()
	{ 
		$kos = '';
		$cena_celkem = 0;
		// zjistíme jestli má nějaké zboží v košíku 
		$data_k = Db::queryRow('SELECT sum(pocet) AS POCET FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
		if($data_k['POCET'] > 0)
		{
			
			// výpočet ceny
			$cena_celkem = $this->kosikCenaCelkem();
		   
		    $this->cena_celkem = $cena_celkem;
			
			$kos .= '<a class="cart --active" href="/kosik">
              <div class="cart__icon">           <img src="/ikony/cart.svg" alt="Košík">
                <div class="cart__number">'.$data_k['POCET'].'</div>
              </div>
              <div class="cart-wrap">
                <div class="cart-title">Nákupní košík</div>
                <div class="cart-price">'.round($cena_celkem).' '.__MENA__.'</div>
              </div></a>';
		}
		else
		{
			$kos .= '<a class="cart" href="/kosik">
                <div class="cart__icon">
                    <img src="/ikony/cart.svg" alt="Košík">
                </div>
                <div class="cart-wrap">
                    <div class="cart-title">Nákupní košík</div>
                </div>
            </a>';
		}

            
            return $kos;
		
	}
	
	
	public function infoKosikFB()
	{
		 // funkce vrací řetězec ID, počet, cenu za kus  pro FB pixel
		 $ret_fb = '';
		 
		 $data_kc = Db::queryAll('SELECT id_var, id_produkt, pocet FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
			if($data_kc)
			{
			   
			   foreach($data_kc as $row_kc)
			   {
					$data_var_cena = Db::queryRow('SELECT V.'.$this->typ_ceny.' AS CENA, V.nazev_var, V.sleva, V.sleva_datum_od, V.sleva_datum_do,
				   P.id_dph, P.nazev, D.dph
				   FROM produkty_varianty V 
				   LEFT JOIN produkty P ON P.id=V.id_produkt
				   LEFT JOIN dph D ON D.id=P.id_dph
				   WHERE V.id=? ', array($row_kc['id_var']));
				   if($data_var_cena)
				   {
						$ceny = $this->vypocetCeny($data_var_cena['CENA'],$data_var_cena['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
						
						$ret_fb .= "{'id':'".$row_kc['id_produkt']."_".$row_kc['id_var']."','quantity':".$row_kc['pocet'].",'item_price':".$ceny['cena_s_dph']."},";
				   }
			   }
		   }
		   
		   return $ret_fb;

	}
	
	
	
	
	public function vypis()
	{
	        $ret = '';
			$ix = 1;
	        $data_kc = Db::queryAll('SELECT id_var, id_produkt, pocet FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
			if($data_kc)
			{
			   
			   foreach($data_kc as $row_kc)
			   {
				   $data_var_cena = Db::queryRow('SELECT V.'.$this->typ_ceny.' AS CENA, V.foto_var, V.nazev_var, V.sleva, V.sleva_datum_od, V.sleva_datum_do, V.ks_skladem,
				   P.id_dph, P.nazev, P.str, P.jednotka, D.dph, DOS.dostupnost, DOS.zobrazeni 
				   FROM produkty_varianty V 
				   LEFT JOIN produkty P ON P.id=V.id_produkt
				   LEFT JOIN dph D ON D.id=P.id_dph
				   LEFT JOIN produkty_dostupnost DOS ON DOS.id=V.id_dostupnost
				   WHERE V.id=? ', array($row_kc['id_var']));
				   if($data_var_cena)
				   {
				     
    				      if($data_var_cena['foto_var'] && $data_var_cena['foto_var']!='bily.png')
						  {
						     $foto = $data_var_cena['foto_var'];
						  }
						  else
						  {
						      // chybí foto varianty - použijeme hlavní foto
						      $data_pf = Db::queryRow('SELECT foto FROM produkty_foto WHERE id_produkt=? ORDER BY typ DESC', array($row_kc['id_produkt']));
							  if($data_pf)
							  {
							    
							    $foto = $data_pf['foto'];
							  
							  }
							  
						  }
						  
						  
						  
					  
							$ceny = $this->vypocetCeny($data_var_cena['CENA'],$data_var_cena['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
							
							// dostupnost - nejdříve musíme zjistit jaké řešení dostupnosti má nastaveno
							if(__DOSTUPNOST_TYP__ == 1)
							{
							   // textová dostupnost dle výběru ve variantách
							   $dostupnost = $data_var_cena['dostupnost'];
							   $zobrazeni = $data_var_cena['zobrazeni'];
							}
							elseif(__DOSTUPNOST_TYP__ == 2)
							{
							     // počty kusů 
								 if($data_var_cena['ks_skladem']<1)
								 {
									 // pokud je dostupnost 0 ks
									 $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE id=? ', array(__PREDVYBRANA_DOSTUPNOST_NULA__));
									 if($data_var_dostupnost2)
									 {
										$dostupnost = $data_var_dostupnost2['dostupnost'];
										$zobrazeni = $data_var_dostupnost2['zobrazeni'];
									 }
								 
								 }
								 else
								 {
									$dostupnost = $data_var_cena['ks_skladem'].' ks';
									$zobrazeni = $data_var_cena['zobrazeni'];
								 } 
							     
							  
							}
							elseif(__DOSTUPNOST_TYP__ == 3)
							{
							     // textová dle počtu kusů od - do
							     if($data_var_cena['ks_skladem']<1)
								 {
									 // pokud je dostupnost 0 ks
									 $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE id=? ', array(__PREDVYBRANA_DOSTUPNOST_NULA__));
									 if($data_var_dostupnost2)
									 {
										$dostupnost = $data_var_dostupnost2['dostupnost'];
										$zobrazeni = $data_var_dostupnost2['zobrazeni'];
									 }
								 
								 }
								 else
								 {
								 $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE ks_od<=? AND ks_do>=?  ', array($data_var_cena['ks_skladem'],$data_var_cena['ks_skladem']));
							     $dostupnost = $data_var_dostupnost2['dostupnost'];
							     $zobrazeni = $data_var_dostupnost2['zobrazeni'];
								}
							}
 
					        
					        if($this->krok==1)
					        {
					             $ret .= '<div class="cart-item"> 
				                      <div class="cart-item-thumb-wrap">
				                        <div class="cart-item-thumb"><img src="/fotky/produkty/male/'.$foto.'" alt="'.$data_var_cena['nazev'].'" title="'.$data_var_cena['nazev'].'"></div>
				                      </div>
				                      <div class="cart-item-content"> 
				                        <div class="cart-item-main">    <a class="cart-item-name" href="/produkty/'.$data_var_cena['str'].'-'.$row_kc['id_produkt'].'">'.$data_var_cena['nazev'].'
											<div class="cart-item-name-var">'.$data_var_cena['nazev_var'].'</div>';
											
											// dostupnost
											if($zobrazeni==1)
											{
												$ret .= '<div class="cart-item-stock --available" id="stock" data-status="active"><img src="/img/icons/check.svg" alt="'.$dostupnost.'">'.$dostupnost.'</div>';
											}
											else
											{
												$ret .= '<div class="cart-item-stock --disabled" id="stock" data-status="disabled"><img src="/img/icons/cross.svg" alt="'.$dostupnost.'">'.$dostupnost.'</div>';
											}
				                            //<div class="cart-item-stock --available"><img src="/img/icons/check.svg" alt="'.$dostupnost.'">'.$dostupnost.'</div>
				                            
				                            $ret .= '</a>
				                          <div class="cart-item-price-pc"><span>'.$ceny['cena_s_dph'].'</span> '.__MENA__.'/'.$data_var_cena['jednotka'].'</div>
				                          <div class="cart-item-price"><span>'.($ceny['cena_s_dph'] * $row_kc['pocet']).'</span> '.__MENA__.'</div>
				                        </div>
				                        <div class="cart-item-bottom">
				                          <div class="cart-item-stepper stepper"> <span class="minus">–</span>
				                            <input class="num_items" id="qty-stepper-input-'.$ix.'" type="number" value="'.$row_kc['pocet'].'" min="1" max="100" step="1" aria-label="Počet položek" 
				                            onchange="ZmenPocetKos('.$ix.','.$row_kc['id_produkt'].','.$row_kc['id_var'].');" required><span class="plus">+</span>
				                          </div><a class="cart-item-remove" data-no-instant href="/kosik?delete='.$row_kc['id_produkt'].'|'.$row_kc['id_var'].'">  
				                            <div class="cart-item-remove-icon"><img src="/img/icons/cross-alt.svg" alt="Odstranit z košíku"></div>
				                            <div class="cart-item-remove-text">Odstranit z košíku</div></a>
				                        </div>
				                      </div>
				                    </div>';
							}
							elseif($this->krok==4)
					        {
									$ret .= '<div class="cart-summary__item"> <img class="cart-summary__img" src="/fotky/produkty/male/'.$foto.'" alt="'.$data_var_cena['nazev'].'" title="'.$data_var_cena['nazev'].'">
						                <div class="cart-summary__item-content">
						                  <div class="cart-summary__item-title">'.$data_var_cena['nazev'].'
						                  <div class="cart-item-name-var">'.$data_var_cena['nazev_var'].'</div></div>
						                  <div class="cart-summary__item-qty">'.$row_kc['pocet'].' '.$data_var_cena['jednotka'].'</div>
						                  <div class="cart-summary__item-price">'.($ceny['cena_s_dph'] * $row_kc['pocet']).' '.__MENA__.'</div>
						                </div>
						              </div>';
							}
							elseif($this->krok==5)
					        {
									// eml rekap.
									
									  $ret .= '<tr>
						                <td style="border-bottom: 1px solid #e5e5e5;padding:20px 0"><img src="'.__URL__.'/fotky/produkty/male/'.$foto.'" 
						                alt="'.$data_var_cena['nazev'].'" title="'.$data_var_cena['nazev'].'" height="80px"/></td>
						                <td style="border-bottom: 1px solid #e5e5e5;padding:20px 0 20px 20px;">
						                  <table style="width:100%">
						                    <tr> 
						                      <td colspan="2">'.$data_var_cena['nazev'].'</td>
						                    </tr>
						                    <tr> 
						                      <td colspan="2" style="color:#6e6e6e; font-size: 14px;padding-bottom: 20px">'.$data_var_cena['nazev_var'].'</td>
						                    </tr>
						                    <tr> 
						                      <td style="font-size: 14px;">Počet: '.$row_kc['pocet'].' '.$data_var_cena['jednotka'].'</td>
						                      <td style="text-align: right;font-size: 14px;">'.$ceny['cena_s_dph'].' '.__MENA__.'/ks</td>
						                    </tr>
						                    <tr style="white-space: nowrap;">
						                      <td style="font-size: 14px;">Celkem s DPH:</td>
						                      <td style="text-align: right;font-weight:500;padding-top: 10px;">'.($ceny['cena_s_dph'] * $row_kc['pocet']).' '.__MENA__.'</td>
						                    </tr>
						                  </table>
						                </td>
						              </tr>';

							}
				   
				   }
				   
				   $ix++;
			   
			   }
			   
			   
			   // slevový kód
			   
			   $ret .= $this->slevovyKod();
			   
			   
			   
		   }
		   
		   return $ret;
	}
	
	
	

	
	
	
	public function slevovyKod()
	{
	   $ret = '';
	   if($_SESSION['slevovy_kod'])
		 {
		    // vypočítáme částku dle typu slevového kódu
		    if($_SESSION['slevovy_kod']['typ']==1)
		    {

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
			    $cena_celkem = $this->kosikCenaCelkem();
			    $kod_castka = round(($cena_celkem / 100 * intval($kod_procento)));
			}
 
			
			if($this->krok==1)
		    {
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
        
                    
			}
			elseif($this->krok==4)
			{
				$ret .= '<div class="cart-summary__item"> <img class="cart-summary__img" src="/img/sleva.png" alt="sleva" title="sleva" style="width: 50px;">
						                <div class="cart-summary__item-content">
						                  <div class="cart-summary__item-title">Slevový kód '.$kod.'</div>
						                  <div class="cart-summary__item-qty">1</div>
						                  <div class="cart-summary__item-price">-'.round($kod_castka).' '.__MENA__.'</div>
						                </div>
						              </div>';
			}
			elseif($this->krok==5)
			{
				// eml rekap.
				$ret .= '<tr style="background: #ffffff">
									   <td style="border-bottom: 1px solid #e5e5e5;padding:20px 0">
									   <img src="'.__URL__.'/img/sleva.png" alt="sleva" title="sleva" style="width: 50px;"></td>
									    <td style="border-bottom: 1px solid #e5e5e5;padding:20px 0 20px 20px;">
						                  <table style="width:100%">
						                    <tr> 
						                      <td colspan="2">Slevový kód '.$kod.'</td>
						                    </tr>
						                    <tr> 
						                      <td colspan="2" style="color:#6e6e6e; font-size: 14px;padding-bottom: 20px"> </td>
						                    </tr>
						                    <tr> 
						                      <td style="font-size: 14px;">Počet: 1</td>
						                      <td style="text-align: right;font-size: 14px;">-'.round($kod_castka).' '.__MENA__.'/ks</td>
						                    </tr>
						                    <tr style="white-space: nowrap;">
						                      <td style="font-size: 14px;">Celkem s DPH:</td>
						                      <td style="text-align: right;font-weight:500;padding-top: 10px;">-'.round($kod_castka).' '.__MENA__.'</td>
						                    </tr>
						                  </table>
						                </td>
									   </tr>';
									   
   

			}
					
					

		
		
		 }
		 
		 return $ret;
	   
	}
	
	
	
	public function kroky()
	{
	  
	   $ret = '';
	   
	   // zjistíme jestli má něco v košíku

	   $data_kk = Db::queryAffected('SELECT id FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
	   if($data_kk > 0)
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
				    $cena_celkem = round($this->cena_celkem);
				    
				    $kod_castka = round(($cena_celkem/100 * intval($kod_procento)));
				}
			}
					
					
		   // rozdělení na jednotlivé kroky
	   
		   if($this->krok==1)
		   {  
		      // záhlaví
		      $ret .= '<section class="cart-heading">
		        <div class="container">
		          <div class="cart-heading__item --active">
		            <div class="cart-heading__icon"><img src="/img/icons/cart-big.svg" alt="1. Nákupní košík"></div>
		            <div class="cart-heading__title">1. Nákupní košík</div>
		          </div>
		          <div class="cart-heading__item">
		            <div class="cart-heading__icon"><img src="/img/icons/car.svg" alt="2. Doprava a platba"></div>
		            <div class="cart-heading__title">2. Doprava a platba</div>
		          </div>
		          <div class="cart-heading__item">
		            <div class="cart-heading__icon"><img src="/img/icons/personal-data.svg" alt="3. Osobní údaje"></div>
		            <div class="cart-heading__title">3. Osobní údaje</div>
		          </div>
		          <div class="cart-heading__item">
		            <div class="cart-heading__icon"><img src="/img/icons/summary.svg" alt="4. Shrnutí objednávky"></div>
		            <div class="cart-heading__title">4. Shrnutí objednávky</div>
		          </div>
		        </div>
		      </section>';
		      
		      // výpis
		     $ret .= '<section class="cart-content">
		        <div class="container">
		          <div class="row"> 
		            <div class="col-12 col-md-8">
		              <div class="cart-table">
		                <form class="cart-content__form" method="post" action="/kosik?krok=2" id="cart-form">
		                  <div class="cart-table__title">Zboží v košíku</div>
		                  <div class="cart-table-items" >';
		                  
		                  $ret .= $this->vypis();

		                    
		                  $ret .= '</div>
		                  <div class="cart-table__footer">
							  <a class="cart-table__footer-link" href="/"> 
		                      <div class="cart-table__footer-icon"><img src="/img/icons/cheveron.svg" alt="Zpět do obchodu"></div>
		                      <div class="cart-table__footer-text">Zpět do obchodu</div></a>
		                      
		                      <a class="cart-table__footer-link" href="/kosik?delete=all" data-no-instant> 
		                      <div class="cart-table__footer-icon"><img src="/img/icons/cross-alt.svg" alt="Vyprázdnit košík"></div>
		                      <div class="cart-table__footer-text">Vyprázdnit košík</div></a>
		                      </div>
		                </form>
		              </div>
		            </div>';
		            
		            // pravý sloupec
		            
		            $ret .= '<div class="col-12 col-md-4"> 
		              <aside class="cart-aside">
		                <div class="cart-aside__title">Shrnutí objednávky</div>
		                <div class="cart-aside__table"> 
		                  <div class="cart-aside__row"> 
		                    <div class="cart-aside__table-th">Cena zboží</div>
		                    <div class="cart-aside__table-td"><span id="goodsprice">'.round($this->cena_celkem - $kod_castka).'</span> '.__MENA__.'</div>
		                  </div>';
		                  /*
		                  <div class="cart-aside__row"> 
		                    <div class="cart-aside__table-th">Doprava a platba</div>
		                    <div class="cart-aside__table-td"><span id="delprice">10</span> '.__MENA__.'</div>
		                  </div>
		                  */
		                  $ret .= '<div class="cart-aside__row --main"> 
		                    <div class="cart-aside__table-th">Celková cena</div>
		                    <div class="cart-aside__table-td"> 
		                      <div><span id="sumprice">'.round($this->cena_celkem - $kod_castka).'</span> '.__MENA__.' </div><small>s DPH</small>
		                    </div>
		                  </div>
		                </div>
		                <div class="cart-aside__free-shipping" id="cartFreeShipping" data-price="'.intval(__POSTOVNE_ZDARMA__).'">Nakupte ještě za '.(__POSTOVNE_ZDARMA__ - round($this->cena_celkem - $kod_castka)).' '.__MENA__.' a máte dopravu zdarma</div>
		                <div class="cart-aside__login"> 
		                  <div class="cart-aside__login-icon"> <img src="/img/icons/user.svg" alt="Už máte svůj účet?"></div>
		                  <div class="cart-aside__login-content"> 
		                    <div class="cart-aside__login-title">Už máte svůj účet?</div>
		                    <div class="cart-aside__login-text"><a href="/registrace">Zaregistrujte se</a>, nebo se přihlaste pro jednodušší nákup.</div>
		                  </div>
		                </div>';
		                
		                
						  // slevový kód
						  // musíme zajistit aby nešel opakovaně vyplnit
						  if($_SESSION['slevovy_kod'])
						  {
						     $sk_disabled = 'disabled';
						  }	
						  else
						  {
						     $sk_disabled = '';
						  }
		                  
		                  $ret .= '<div class="form-group cart-aside__code '.$sk_disabled.'"> 
		                    <label for="code">Mám slevový kód</label>
		                    <input type="text" id="code" name="code" '.$sk_disabled.' required>
		                    <button class="btn" id="tl_sk" '.$sk_disabled.' >Použít</button>
		                  </div>

		                <button class="btn --cart" name="krok" value="2" form="cart-form">Doprava a platba <img src="/img/icons/cheveron-white.svg" alt="Pokračovat"></button>
		              </aside>
		            </div>
		          </div>
		        </div>
		      </section>';
		   
		   }
		   elseif($this->krok==2)
		   {
		       $sql_doprava = '';
		       // záhlaví
		       $ret .= '<section class="cart-heading">
				        <div class="container">
				          <div class="cart-heading__item --complete">
				            <div class="cart-heading__icon"><img src="/img/icons/cart-big.svg" alt="1. Nákupní košík"></div>
				            <div class="cart-heading__title">1. Nákupní košík</div>
				          </div>
				          <div class="cart-heading__item --active">
				            <div class="cart-heading__icon"><img src="/img/icons/car.svg" alt="2. Doprava a platba"></div>
				            <div class="cart-heading__title">2. Doprava a platba</div>
				          </div>
				          <div class="cart-heading__item">
				            <div class="cart-heading__icon"><img src="/img/icons/personal-data.svg" alt="3. Osobní údaje"></div>
				            <div class="cart-heading__title">3. Osobní údaje</div>
				          </div>
				          <div class="cart-heading__item">
				            <div class="cart-heading__icon"><img src="/img/icons/summary.svg" alt="4. Shrnutí objednávky"></div>
				            <div class="cart-heading__title">4. Shrnutí objednávky</div>
				          </div>
				        </div>
				      </section>';
				      
				    // musíme dodatečně ošetřit situaci, kdy se z 3. kroku vrátí do 1. a změní počet kusu tak až dosáhne na dopravu zdarma
				    // údaje o ceně dopravy a platby máme v sešně a musíme je zaktualizovat
				    // mohl také dodatečně přidat do košíku produkt, který má příznak doprava zdarma v závislosti na nastavení je pak doprava zdarma
				    $pdp = $this->prepocetDopravaPlatba();
					
					
					
					if($_POST['_posta'])
					{
						// návrat z výběru balík na poštu
						$_SESSION['doprava']['psc_cp_na_postu'] = intval($_POST['_posta_psc']);
						$_SESSION['doprava']['id_cp_na_postu'] = intval($_POST['_posta']);
						$_SESSION['doprava']['nazev_cp_na_postu'] = strip_tags($_POST['_posta_nazev']);
						$_SESSION['doprava']['adresa_cp_na_postu'] = strip_tags($_POST['_posta_adresa']);
						
						// smažeme balíkovnu
						unset($_SESSION['doprava']['psc_cp_balikovna']);
						unset($_SESSION['doprava']['id_cp_balikovna']);
						unset($_SESSION['doprava']['nazev_cp_balikovna']);
						unset($_SESSION['doprava']['adresa_cp_balikovna']);
					}
					
					 
					if($_POST['_posta2'])
					{
						// návrat z výběru balík do balíkovny
						$_SESSION['doprava']['psc_cp_balikovna'] = intval($_POST['_posta2_psc']);
						$_SESSION['doprava']['id_cp_balikovna'] = intval($_POST['_posta2']);
						$_SESSION['doprava']['nazev_cp_balikovna'] = strip_tags($_POST['_posta2_nazev']);
						$_SESSION['doprava']['adresa_cp_balikovna'] = strip_tags($_POST['_posta2_adresa']);
						
						// smažeme balík na poštu
						unset($_SESSION['doprava']['psc_cp_na_postu']);
						unset($_SESSION['doprava']['id_cp_na_postu']);
						unset($_SESSION['doprava']['nazev_cp_na_postu']);
						unset($_SESSION['doprava']['adresa_cp_na_postu']);
					}

				// výběr doprava / platba      

				 $ret .= '<section class="cart-content">
			        <div class="container">
			          <div class="row"> 
			            <div class="col-12 col-md-8">
			              <div class="cart-table">
			                <form class="cart-content__form" method="post" action="/kosik?krok=3" id="cart-form">
			                  <div class="cart-table__title">Způsob dopravy</div>
			                  <div class="cart-table-shipping">';
			                  
			                  // doprava
			                  // z obecného nastavení zjistíme jak se chovat ohledně produktů s příznakem doprava zdarma

			                  // z obecného nastavení zjistíme způsob fungování modulu doprava 
			                  // DOPRAVA_TYP  1 = dle ceny zboží od-do, 2 = dle váhy zboží od - do
			                  
			                  // speciální doprava
			                  // pokud je v košíku produkt s příznakem speciální doprava tak se generuje pouze doprava s tímto příznakem 
			                   
			                  if($this->dopravaSpecial2())
			                  {
							     $sql_doprava .= ' AND specialni_doprava2=1 ';
							  }
							  elseif($this->dopravaSpecial())
			                  {
							     $sql_doprava .= ' AND specialni_doprava=1 ';
							  }
							  else
							  {
								   $sql_doprava .= ' AND specialni_doprava=0 AND specialni_doprava2=0 ';
							  }
			                  
			                  $vaha_celkem = $this->kosikVahaCelkem();
			                  $cena_celkem = round($this->kosikCenaCelkem());
			                  
			                  if(__DOPRAVA_TYP__==1)
			                  {
							    $sql_doprava .= ' AND (castka_od<='.$cena_celkem.' AND castka_do>='.$cena_celkem.') ';
							  }
							  elseif(__DOPRAVA_TYP__==2)
							  {
							    $sql_doprava .= ' AND (vaha_od<='.$vaha_celkem.' AND vaha_do>='.$vaha_celkem.') ';
							  }
	 
			                  $data_doprava = Db::queryAll('SELECT D.*, DAN.dph FROM doprava D 
			                  LEFT JOIN dph DAN ON DAN.id=D.id_dph
			                  WHERE D.aktivni=? '.$sql_doprava.' ORDER BY razeni ASC', array(1));
							  if($data_doprava)
							  {
								    $xd = 1;
									foreach($data_doprava as $row_doprava)
									{		
									   
									   	  if(__DOPRAVA_ZDARMA_TYP__==1)
						                  {
										    // všechny produkt v košíku musí mít příznak doprava zdarma
										    if($this->dopravaZdarmaVse())
										    {
											   $doprava_cena = 0;
											}
											else
											{
											   $doprava_cena = $row_doprava['cena'];
											}
										  }
										  else
										  {
										    // stačí jeden produkt s příznakem doprava zdarma
										    if($this->dopravaZdarma())
										    {
											   $doprava_cena = 0;
											}
											else
											{
											   $doprava_cena = $row_doprava['cena'];
											}
										  }
										  
										  if($cena_celkem > __POSTOVNE_ZDARMA__)
										  {
										     $doprava_cena = 0;
										  }
										
										
										if(__CENY_ADM__==1)
										{
										  // ceny jsou bez DPH
										  $doprava_cena = round($doprava_cena * ($row_doprava['dph'] / 100 + 1));
										}
										
										$ret .= '<label class="cart-table-shipping-item" for="delivery-'.$xd.'" onclick="VygenerujPlatbu('.$row_doprava['id'].');';
							                      
							                      if($row_doprava['cp_balik_na_postu']==1)
							                      {
												    // modal okno pro balík na poštu
												    $ret .= 'SkryjZasilkovnu('.$row_doprava['id'].');SkryjPPLparcel('.$row_doprava['id'].');SkryjDPDpickup('.$row_doprava['id'].');BalikNaPostu('.$row_doprava['id'].');';
												  }
												  elseif($row_doprava['cp_balik_do_balikovny']==1)
							                      {
												    // modal okno pro balík do balíkovny
												    $ret .= 'SkryjZasilkovnu('.$row_doprava['id'].');SkryjPPLparcel('.$row_doprava['id'].');SkryjDPDpickup('.$row_doprava['id'].');BalikDoBalikovny('.$row_doprava['id'].');';
												  }
												  elseif($row_doprava['zasilkovna']==1)
							                      {
												    // zásilkovna - má vlastní překryvné okno
												    $ret .= 'SkryjBaliky('.$row_doprava['id'].');SkryjPPLparcel('.$row_doprava['id'].');SkryjDPDpickup('.$row_doprava['id'].');Zasilkovna('.$row_doprava['id'].');';
												  }
												  elseif($row_doprava['ppl_parcel']==1)
							                      {
												    // ppl_parcel - má vlastní překryvné okno
												    $ret .= 'SkryjBaliky('.$row_doprava['id'].');SkryjZasilkovnu('.$row_doprava['id'].');SkryjDPDpickup('.$row_doprava['id'].');PPLparcel('.$row_doprava['id'].');';
												  }
												  elseif($row_doprava['dpd_pickup']==1)
							                      {
												    // dpd_pickup - má vlastní překryvné okno
												    $ret .= 'SkryjBaliky('.$row_doprava['id'].');SkryjZasilkovnu('.$row_doprava['id'].');SkryjPPLparcel('.$row_doprava['id'].');DPDpickup('.$row_doprava['id'].');';
												  }
												  else
												  {
												    $ret .= 'SkryjZasilkovnu('.$row_doprava['id'].');SkryjPPLparcel('.$row_doprava['id'].');SkryjDPDpickup('.$row_doprava['id'].');SkryjBaliky('.$row_doprava['id'].');';
												  }
							                      
							                      $ret .= '" >
							                      
							                      <input type="radio" id="delivery-'.$xd.'" name="doprava" value="'.$row_doprava['id'].'" ';
							                      
							                      if($_SESSION['doprava']['id']==$row_doprava['id']){$ret .= ' checked ';} 
							                      $ret .= ' required>
							                      <div class="cart-table-shipping-img"><img src="/fotky/dopravci/'.$row_doprava['foto'].'" alt="'.$row_doprava['nazev'].'"></div>
							                      <div class="cart-table-shipping-text">'.$row_doprava['nazev'].'<small>'.$row_doprava['popis'].'</small>';
							                      
							                      if($_SESSION['doprava']['nazev_cp_na_postu'] && $row_doprava['cp_balik_na_postu']==1)
							                      {  
													 // balík na poštu 
													 $ret .= '<span id="s_pobocka">Vybraná pobočka: '.$_SESSION['doprava']['nazev_cp_na_postu'].', '.$_SESSION['doprava']['adresa_cp_na_postu'].', '.$_SESSION['doprava']['psc_cp_na_postu'].'</span>';
												  }
												  elseif($_SESSION['doprava']['nazev_cp_balikovna'] && $row_doprava['cp_balik_do_balikovny']==1)
												  {
													 // balíko do balíkovny
													 $ret .= '<span id="s_pobocka2">Vybraná pobočka: '.$_SESSION['doprava']['nazev_cp_balikovna'].', '.$_SESSION['doprava']['adresa_cp_balikovna'].', '.$_SESSION['doprava']['psc_cp_balikovna'].'</span>';
												  }
												  elseif($row_doprava['zasilkovna']==1)
												  {
													 // zásilkovna
													 if($row_doprava['zasilkovna']==1 && $_SESSION['doprava']['id_zasilkovna']){$zas_dis = 'block';}
													 else{$zas_dis = 'none';}
													 $ret .= '<span id="s_pobocka3" style="display: '.$zas_dis.'">Vybraná pobočka: '.$_SESSION['doprava']['nazev_zasilkovna'].', '.$_SESSION['doprava']['ulice_zasilkovna'].', '.$_SESSION['doprava']['obec_zasilkovna'].', '.$_SESSION['doprava']['psc_zasilkovna'].'</span>';
												  }
												  elseif($row_doprava['ppl_parcel']==1)
												  {
													 // ppl_parcel
													 if($row_doprava['ppl_parcel']==1 && $_SESSION['doprava']['id_ppl_parcel']){$ppl_parcel_dis = 'block';}
													 else{$ppl_parcel_dis = 'none';}
													 $ret .= '<span id="s_pobocka4" style="display: '.$ppl_parcel_dis.'">Vybraná pobočka: '.$_SESSION['doprava']['nazev_ppl_parcel'].', '.$_SESSION['doprava']['ulice_ppl_parcel'].', '.$_SESSION['doprava']['obec_ppl_parcel'].', '.$_SESSION['doprava']['psc_ppl_parcel'].'</span>';
												  }
												  elseif($row_doprava['dpd_pickup']==1)
												  {
													 // dpd_pickup
													 if($row_doprava['dpd_pickup']==1 && $_SESSION['doprava']['id_dpd_pickup']){$dpd_pickup_dis = 'block';}
													 else{$dpd_pickup_dis = 'none';}
													 $ret .= '<span id="s_pobocka5" style="display: '.$dpd_pickup_dis.'">Vybraná pobočka: '.$_SESSION['doprava']['nazev_dpd_pickup'].', '.$_SESSION['doprava']['ulice_dpd_pickup'].', '.$_SESSION['doprava']['obec_dpd_pickup'].', '.$_SESSION['doprava']['psc_dpd_pickup'].'</span>';
												  }
							                     
							                      $ret .= '</div>
							                      <div class="cart-table-shipping-price"><span>'.$doprava_cena.'</span> '.__MENA__.'</div>
							                    </label>';
							                    
							            $xd++;        										
									}
									
							  }
							  

			                  $ret .= '</div>
			                  <div class="cart-table__title">Způsob platby</div>
			                  <div class="cart-table-shipping" id="platby">';
			                  
			                  // platba
			                  // pokud je ID dopravy v sešně tak vygenerujeme platby
			                  // jinak generujeme až ajaxem na výběr typu dopravy
			                  if($_SESSION['doprava']['id'])
			                  {
								  $data_dp = Db::queryRow('SELECT platba_arr FROM doprava WHERE id=? AND aktivni=? ', array($_SESSION['doprava']['id'],1));
								  if($data_dp)
						          {
								     $platba_arr = unserialize($data_dp['platba_arr']);
								     $data_platba = Db::queryAll('SELECT P.*, DAN.dph FROM platba P 
								     LEFT JOIN dph DAN ON DAN.id=P.id_dph
								     WHERE P.aktivni=? AND P.id IN('.implode(',',$platba_arr).')  ', array(1));
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
												    if($this->dopravaZdarmaVse())
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
												    if($this->dopravaZdarma())
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
											
											
											if(__CENY_ADM__==1)
											{
											  // ceny jsou bez DPH
											  $platba_cena = round($platba_cena * ($platba_cena['dph'] / 100 + 1));
											}
											
											$ret .= '<label class="cart-table-shipping-item" for="payment-'.$xp.'">
						                      <input type="radio" id="payment-'.$xp.'" name="platba" value="'.$row_platba['id'].'" ';
						                    if($_SESSION['platba']['id']==$row_platba['id']){$ret .= ' checked ';} 
						                    $ret .= ' required>
						                      <div class="cart-table-shipping-text">'.$row_platba['nazev'].'</div>
						                      <div class="cart-table-shipping-price"><span>'.$platba_cena.'</span> '.__MENA__.'</div>
						                    </label>';

						                    	
										    $xp++;
										 }
									 }
								  
								  }
							
									
							  }

			                  $ret .= '</div>
			                  <div class="cart-table__footer"><a class="cart-table__footer-link" href="/kosik?krok=1" > 
			                      <div class="cart-table__footer-icon"><img src="/img/icons/cheveron.svg" alt="Krok zpět"></div>
			                       <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
			                      <div class="cart-table__footer-text">Krok zpět</div></a></div>
			                </form>
			              </div>
			            </div>';
			            
			            if($_SESSION['doprava']['cena'])
			            {
						   $doprava_cena_vyber = intval($_SESSION['doprava']['cena']);
						}
						else
						{
						   $doprava_cena_vyber = 0;
						}
						
						if($_SESSION['platba']['cena'])
			            {
						   $platba_cena_vyber = intval($_SESSION['platba']['cena']);
						}
						else
						{
						   $platba_cena_vyber = 0;
						}
						
			            // pravý sloupec
			            $ret .= '<div class="col-12 col-md-4"> 
			              <aside class="cart-aside">
			                <div class="cart-aside__title">Shrnutí objednávky</div>
			                <div class="cart-aside__table"> 
			                  <div class="cart-aside__row"> 
			                    <div class="cart-aside__table-th">Cena zboží</div>
			                    <div class="cart-aside__table-td"><span id="goodsprice">'.($cena_celkem - $kod_castka).'</span> '.__MENA__.'</div>
			                  </div>
			                  <div class="cart-aside__row"> 
			                    <div class="cart-aside__table-th">Doprava a platba</div>
			                    <div class="cart-aside__table-td"><span id="delprice">'.($doprava_cena_vyber + $platba_cena_vyber ).'</span> '.__MENA__.'</div>
			                  </div>
			                  <div class="cart-aside__row --main"> 
			                    <div class="cart-aside__table-th">Celková cena</div>
			                    <div class="cart-aside__table-td"> 
			                      <div><span id="sumprice">'.round($cena_celkem + $doprava_cena_vyber + $platba_cena_vyber - $kod_castka).'</span> '.__MENA__.' </div><small>s DPH</small>
			                    </div>
			                  </div>
			                </div>
			       
			              
				             <div class="cart-aside__login"> 
			                  <div class="cart-aside__login-icon"> <img src="/img/icons/user.svg" alt="Už máte svůj účet?"></div>
			                  <div class="cart-aside__login-content"> 
			                    <div class="cart-aside__login-title">Už máte svůj účet?</div>
			                    <div class="cart-aside__login-text"><a href="/registrace">Zaregistrujte se</a>, nebo se přihlaste pro jednodušší nákup.</div>
			                  </div>
			                </div>
			                <input type="hidden" name="_p" id="_p" value="0">
			                <button class="btn --cart" name="krok" value="3" id="tl_krok_2" onclick="KontrolaPosta();">Osobní údaje <img src="/img/icons/cheveron-white.svg" alt="Pokračovat"></button>
			              </aside>
			            </div>
			          </div>
			        </div>
			      </section>';
				      
				      
		   }
		   elseif($this->krok==3)
		   {
				  //var_dump($_SESSION['doprava']);
				  // záhlaví
		          $ret .= '<section class="cart-heading">
			        <div class="container">
			          <div class="cart-heading__item --complete">
			            <div class="cart-heading__icon"><img src="/img/icons/cart-big.svg" alt="1. Nákupní košík"></div>
			            <div class="cart-heading__title">1. Nákupní košík</div>
			          </div>
			          <div class="cart-heading__item --complete">
			            <div class="cart-heading__icon"><img src="/img/icons/car.svg" alt="2. Doprava a platba"></div>
			            <div class="cart-heading__title">2. Doprava a platba</div>
			          </div>
			          <div class="cart-heading__item --active">
			            <div class="cart-heading__icon"><img src="/img/icons/personal-data.svg" alt="3. Osobní údaje"></div>
			            <div class="cart-heading__title">3. Osobní údaje</div>
			          </div>
			          <div class="cart-heading__item">
			            <div class="cart-heading__icon"><img src="/img/icons/summary.svg" alt="4. Shrnutí objednávky"></div>
			            <div class="cart-heading__title">4. Shrnutí objednávky</div>
			          </div>
			        </div>
			      </section>';
			      
			      // kontrola doprava a platba
			      if(!$_SESSION['doprava']['id'] || !$_SESSION['platba']['id'])
				  {
				    $ret .= '<section class="cart-content">
				        <div class="container">
				          <div class="row"><b>Nevybrali jste dopravu a platbu.</b> Vraťte se prosím zpět a vyberte.
				          </div>
				          </div>
				          </section>';
				  }
				  else
				  {
					// osobní údaje + rekapitulace
					if($_SESSION['uzivatel'])
					{
						// je přihlášen
				    
						if($_SESSION['uzivatel']['typ']==1)
						{
							// regulerní registrace
						}
						
						
						$data_zak = Db::queryRow('SELECT * FROM zakaznici WHERE id=? AND aktivni=? ', array(intval($_SESSION['uzivatel']['id']),1));
						if($data_zak)
						{
						   $jmeno = $data_zak['jmeno'];
						   $prijmeni = $data_zak['prijmeni'];
						   $email = $data_zak['email'];
						   $telefon = $data_zak['telefon'];
						   
						   $dodaci_nazev = $data_zak['dodaci_nazev'];
						   $dodaci_ulice = $data_zak['dodaci_ulice'];
						   $dodaci_cislo = $data_zak['dodaci_cislo'];
						   $dodaci_obec = $data_zak['dodaci_obec'];
						   $dodaci_psc = $data_zak['dodaci_psc'];
						   $dodaci_id_stat = $data_zak['dodaci_id_stat'];
						   
						   $fakturacni_jmeno = $data_zak['fakturacni_jmeno'];
						   $fakturacni_firma = $data_zak['fakturacni_firma'];
						   $fakturacni_ulice = $data_zak['fakturacni_ulice'];
						   $fakturacni_cislo = $data_zak['fakturacni_cislo'];
						   $fakturacni_obec = $data_zak['fakturacni_obec'];
						   $fakturacni_psc = $data_zak['fakturacni_psc'];
						   $fakturacni_id_stat = $data_zak['fakturacni_id_stat'];
						   $ic = $data_zak['ic'];
						   $dic = $data_zak['dic'];
						}
		  
				     }
				     elseif($_SESSION['reg_kosik']['jmeno'])
				     {
					       $jmeno = $_SESSION['reg_kosik']['jmeno'];
						   $prijmeni = $_SESSION['reg_kosik']['prijmeni'];
						   $email = $_SESSION['reg_kosik']['email'];
						   $telefon = $_SESSION['reg_kosik']['telefon'];
						   
						   $dodaci_nazev = $_SESSION['reg_kosik']['dodaci_nazev'];
						   $dodaci_ulice = $_SESSION['reg_kosik']['dodaci_ulice'];
						   $dodaci_cislo = $_SESSION['reg_kosik']['dodaci_cislo'];
						   $dodaci_obec = $_SESSION['reg_kosik']['dodaci_obec'];
						   $dodaci_psc = $_SESSION['reg_kosik']['dodaci_psc'];
						   $dodaci_id_stat = $_SESSION['reg_kosik']['dodaci_id_stat'];
						   
						   $fakturacni_jmeno = $_SESSION['reg_kosik']['fakturacni_jmeno'];
						   $fakturacni_firma = $_SESSION['reg_kosik']['fakturacni_firma'];
						   $fakturacni_ulice = $_SESSION['reg_kosik']['fakturacni_ulice'];
						   $fakturacni_cislo = $_SESSION['reg_kosik']['fakturacni_cislo'];
						   $fakturacni_obec = $_SESSION['reg_kosik']['fakturacni_obec'];
						   $fakturacni_psc = $_SESSION['reg_kosik']['fakturacni_psc'];
						   $fakturacni_id_stat = $_SESSION['reg_kosik']['fakturacni_id_stat'];
						   $ic = $_SESSION['reg_kosik']['ic'];
						   $dic = $_SESSION['reg_kosik']['dic'];
					 }
			    
			            $ret .= '<section class="cart-content">
				        <div class="container">
				          <div class="row"> 
				            <div class="col-12 col-lg-8">
				              <div class="cart-table">
				                <form class="cart-content__form" method="post" action="/kosik?krok=4" id="cart-form-user">
				                  <div class="cart-table__title">Vyplňte osobní údaje</div>
				                  <div class="cart-table-personal">
				                    <div class="form-group">    
				                      <label for="cart-name">Jméno</label>
				                      <input type="text" id="cart-name" name="jmeno" value="'.$jmeno.'" required>
				                    </div>
				                    <div class="form-group">    
				                      <label for="cart-surname">Příjmení</label>
				                      <input type="text" id="cart-surname" name="prijmeni" value="'.$prijmeni.'" required>
				                    </div>
				                    <div class="form-group">    
				                      <label for="email">Email</label><input type="email" id="email" name="email" autocomplete="new-password"  value="'.$email.'" 
				                      required minlength="6" maxlength="255" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,5}$">
				                    </div>
				                    <div class="form-group">    
				                      <label for="cart-tel">Telefon</label><input type="tel" id="cart-tel" name="telefon" value="'.$telefon.'" required  pattern="^(?:0|\(?\+42\)?\s?|0042\s?)[0-9][. \s]?[0-9]{3}[. \s]?[0-9]{3}[. \s]?[0-9]{3}">
				                    </div>
				                    <div class="form-subtitle">Dodací údaje</div>';
				                    
				                    // úprava pro balík na poštu, do balíkovny + zásilkovna
				                      if($_SESSION['doprava']['id_cp_na_postu'])
					                  {
										// rozparsujeme adresu na jenotlivé položky
										list($posta_ulice,$posta_cast,$posta_psc,$posta_mesto_) = explode(', ',$_SESSION['doprava']['adresa_cp_na_postu']);
										if(!$posta_mesto_)
										{
											$posta_mesto = $posta_psc;
										}
										else
										{
										    $posta_mesto = $posta_mesto_;
										}
										
										$posta_cislo_arr = explode(' ',$posta_ulice);
										$posta_cislo_ = array_reverse($posta_cislo_arr);
										$posta_cislo = $posta_cislo_[0];

				  
										$ret .= '<div class="form-group">    
					                        <label for="dodaci_nazev">Balík na poštu  </label>
					                        <input type="text" id="dodaci_nazev" name="dodaci_nazev" value="'.$_SESSION['doprava']['nazev_cp_na_postu'].'" readonly >
					                      </div>
					                      <div class="form-group">    
						                      <label for="cart-street">Ulice</label>
						                      <input type="text" id="cart-street" name="dodaci_ulice" value="'.$posta_ulice.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-street-n">Číslo popisné</label>
						                      <input type="text" id="cart-street-n" name="dodaci_cislo" value="'.$posta_cislo.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-city">Město/obec</label>
						                      <input type="text" id="cart-city" name="dodaci_obec" value="'.$posta_mesto.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-psc">PSČ</label>
						                      <input type="text" id="cart-psc" name="dodaci_psc" value="'.$_SESSION['doprava']['psc_cp_na_postu'].'" required readonly >
						                    </div>';
									  }
									  elseif($_SESSION['doprava']['id_cp_balikovna'])
									  {
										// rozparsujeme adresu na jenotlivé položky
										list($balikovna_ulice,$balikovna_cast,$balikovna_psc,$balikovna_mesto_) = explode(', ',$_SESSION['doprava']['adresa_cp_balikovna']);
										if(!$balikovna_mesto_)
										{
											$balikovna_mesto = $balikovna_psc;
										}
										else
										{
										    $balikovna_mesto = $balikovna_mesto_;
										}
										
										$balikovna_cislo_arr = explode(' ',$balikovna_ulice);
										$balikovna_cislo_ = array_reverse($balikovna_cislo_arr);
										$balikovna_cislo = $balikovna_cislo_[0];
										
										$ret .= '<div class="form-group">    
					                        <label for="dodaci_nazev">Balíkovna </label>
					                        <input type="text" id="dodaci_nazev" name="dodaci_nazev" value="'.$_SESSION['doprava']['nazev_cp_balikovna'].'" readonly >
					                      </div>
					                      
					                      <div class="form-group">    
						                      <label for="cart-street">Ulice</label>
						                      <input type="text" id="cart-street" name="dodaci_ulice" value="'.$balikovna_ulice.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-street-n">Číslo popisné</label>
						                      <input type="text" id="cart-street-n" name="dodaci_cislo" value="'.$balikovna_cislo.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-city">Město/obec</label>
						                      <input type="text" id="cart-city" name="dodaci_obec" value="'.$balikovna_mesto.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-psc">PSČ</label>
						                      <input type="text" id="cart-psc" name="dodaci_psc" value="'.$_SESSION['doprava']['psc_cp_balikovna'].'" required readonly >
						                    </div>';
									  }
									  elseif($_SESSION['doprava']['id_zasilkovna'])
									  {

										$zasilkovna_cislo_arr = explode(' ',$_SESSION['doprava']['ulice_zasilkovna']);
										$zasilkovna_cislo_ = array_reverse($zasilkovna_cislo_arr);
										$zasilkovna_cislo = $zasilkovna_cislo_[0];
										
										$ret .= '<div class="form-group">    
					                        <label for="dodaci_nazev">Zásilkovna </label>
					                        <input type="text" id="dodaci_nazev" name="dodaci_nazev" value="'.$_SESSION['doprava']['nazev_zasilkovna'].'" readonly >
					                      </div>
					                      
					                      <div class="form-group">    
						                      <label for="cart-street">Ulice</label>
						                      <input type="text" id="cart-street" name="dodaci_ulice" value="'.$_SESSION['doprava']['ulice_zasilkovna'].'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-street-n">Číslo popisné</label>
						                      <input type="text" id="cart-street-n" name="dodaci_cislo" value="'.$zasilkovna_cislo.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-city">Město/obec</label>
						                      <input type="text" id="cart-city" name="dodaci_obec" value="'.$_SESSION['doprava']['obec_zasilkovna'].'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-psc">PSČ</label>
						                      <input type="text" id="cart-psc" name="dodaci_psc" value="'.$_SESSION['doprava']['psc_zasilkovna'].'" required readonly >
						                    </div>';
									  }
									  elseif($_SESSION['doprava']['id_ppl_parcel'])
									  {

										$ppl_parcel_cislo_arr = explode(' ',$_SESSION['doprava']['ulice_ppl_parcel']);
										$ppl_parcel_cislo_ = array_reverse($ppl_parcel_cislo_arr);
										$ppl_parcel_cislo = $ppl_parcel_cislo_[0];
										
										$ret .= '<div class="form-group">    
					                        <label for="dodaci_nazev">PPL Parcel </label>
					                        <input type="text" id="dodaci_nazev" name="dodaci_nazev" value="'.$_SESSION['doprava']['nazev_ppl_parcel'].'" readonly >
					                      </div>
					                      
					                      <div class="form-group">    
						                      <label for="cart-street">Ulice</label>
						                      <input type="text" id="cart-street" name="dodaci_ulice" value="'.$_SESSION['doprava']['ulice_ppl_parcel'].'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-street-n">Číslo popisné</label>
						                      <input type="text" id="cart-street-n" name="dodaci_cislo" value="'.$ppl_parcel_cislo.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-city">Město/obec</label>
						                      <input type="text" id="cart-city" name="dodaci_obec" value="'.$_SESSION['doprava']['obec_ppl_parcel'].'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-psc">PSČ</label>
						                      <input type="text" id="cart-psc" name="dodaci_psc" value="'.$_SESSION['doprava']['psc_ppl_parcel'].'" required readonly >
						                    </div>';
									  }
									  elseif($_SESSION['doprava']['id_dpd_pickup'])
									  {

										$dpd_pickup_cislo_arr = explode(' ',$_SESSION['doprava']['ulice_dpd_pickup']);
										$dpd_pickup_cislo_ = array_reverse($dpd_pickup_cislo_arr);
										$dpd_pickup_cislo = $dpd_pickup_cislo_[0];
										
										$ret .= '<div class="form-group">    
					                        <label for="dodaci_nazev">DPD Pickup </label>
					                        <input type="text" id="dodaci_nazev" name="dodaci_nazev" value="'.$_SESSION['doprava']['nazev_dpd_pickup'].'" readonly >
					                      </div>
					                      
					                      <div class="form-group">    
						                      <label for="cart-street">Ulice</label>
						                      <input type="text" id="cart-street" name="dodaci_ulice" value="'.$_SESSION['doprava']['ulice_dpd_pickup'].'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-street-n">Číslo popisné</label>
						                      <input type="text" id="cart-street-n" name="dodaci_cislo" value="'.$dpd_pickup_cislo.'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-city">Město/obec</label>
						                      <input type="text" id="cart-city" name="dodaci_obec" value="'.$_SESSION['doprava']['obec_dpd_pickup'].'" required readonly >
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-psc">PSČ</label>
						                      <input type="text" id="cart-psc" name="dodaci_psc" value="'.$_SESSION['doprava']['psc_dpd_pickup'].'" required readonly >
						                    </div>';
									  }
									  else
									  {
									        $ret .= '<div class="form-group">    
					                        <label for="dodaci_nazev">Jméno a příjmení </label>
					                        <input type="text" id="dodaci_nazev" name="dodaci_nazev" value="'.$dodaci_nazev.'" >
					                        </div>';
						                    $ret .= '<div class="form-group">    
						                      <label for="cart-street">Ulice</label>
						                      <input type="text" id="cart-street" name="dodaci_ulice" value="'.$dodaci_ulice.'" required>
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-street-n">Číslo popisné</label>
						                      <input type="text" id="cart-street-n" name="dodaci_cislo" value="'.$dodaci_cislo.'" required>
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-city">Město/obec</label>
						                      <input type="text" id="cart-city" name="dodaci_obec" value="'.$dodaci_obec.'" required>
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-psc">PSČ</label>
						                      <input type="text" id="cart-psc" name="dodaci_psc" value="'.$dodaci_psc.'" required>
						                    </div>
						                    <div class="form-group">    
						                      <label for="cart-state">Stát</label>
						                      <select name="dodaci_id_stat" id="cart-state" required> '; 
						                       
					                           foreach($_SESSION['staty_arr'] as $staty_k=>$staty_v)
							                    {
													 $ret .= '<option value="'.$staty_k.'" ';
													 if($dodaci_id_stat == $staty_k){$ret .= ' selected '; }
													 $ret .= '>'.$staty_v.'</option>';
												}
												
						                      $ret .= '</select>
						                    </div>';
									  }
				                    
				                    if($_SESSION['doprava']['id_zasilkovna'] || $_SESSION['doprava']['id_ppl_parcel'] || $_SESSION['doprava']['id_dpd_pickup'] || $_SESSION['doprava']['id_cp_balikovna'] || $_SESSION['doprava']['id_cp_na_postu'])
				                    {
									     $ret .= '<div class="form-alert">Vyplňte prosím fakturační adresu abychom vám mohli správně vygenerovat fakturu. </div>';
									}
				                    

				                    $ret .= '<div class="form-spacer"> </div>';
				                    
				                    // fakturační 
				                    
				                    if($_SESSION['doprava']['id_zasilkovna'] || $_SESSION['doprava']['id_ppl_parcel'] || $_SESSION['doprava']['id_dpd_pickup'] || $_SESSION['doprava']['id_cp_balikovna'] || $_SESSION['doprava']['id_cp_na_postu'])
				                    {
									   // balíkovna, balík na poštu, zásilkovna
									   $ret .= '<div class="form-subtitle">Fakturační údaje </div>';
									   $required = ' required ';

									}
									else
									{
									   // zbytek
									   $required = '';
									   $ret .= '<div class="form-group --checkbox">
				                      <label for="cart-fa-address" id="faToggle">Vyplnit fakturační adresu <span>(pokud je rozdílná od dodací adresy nebo nakupuji na firmu)</span></label>
				                      <input type="checkbox" name="cart-fa-address" id="cart-fa-address" value="1" >
				                      <div class="form-collapse">';
									}
				                    

				                      $ret .= '<div class="form-group">    
				                        <label for="fakturacni_jmeno">Jméno a příjmení</label>
				                        <input type="text" id="fakturacni_jmeno" name="fakturacni_jmeno" value="'.$fakturacni_jmeno.'" '.$required.' >
				                      </div>
				                      
				                      <div class="form-group --checkbox">
					                      <label for="cart-fa-company" id="faComToggle">Jsme firma a potřebujeme uvést také obchodní informace</label>
					                      <input type="checkbox" name="cart-fa-address" id="cart-fa-company">
					                      <div class="form-collapse">
					                        <div class="form-group">    
					                          <label for="fakturacni_jmeno">Název firmy</label>
					                          <input type="text" id="fakturacni_firma" name="fakturacni_firma" value="'.$fakturacni_firma.'" >
					                        </div>
					                        <div class="form-group">    
					                          <label for="cart-fa-ico">IČO</label>
					                          <input type="text" id="ic" name="ic" value="'.$ic.'">
					                        </div>
					                        <div class="form-group">    
					                          <label for="dic">DIČ</label>
					                          <input type="text" id="dic" name="dic" value="'.$dic.'">
					                        </div>
					                      </div>
					                    </div>
				                      
 
				                      <div class="form-group">    
				                        <label for="fakturacni_ulice">Ulice</label>
				                        <input type="text" id="fakturacni_ulice" name="fakturacni_ulice"  value="'.$fakturacni_ulice.'" '.$required.'>
				                      </div>
				                      <div class="form-group">    
				                        <label for="fakturacni_cislo">Číslo popisné</label>
				                        <input type="text" id="fakturacni_cislo" name="fakturacni_cislo"  value="'.$fakturacni_cislo.'" '.$required.'>
				                      </div>
				                      <div class="form-group">    
				                        <label for="fakturacni_obec">Město/obec</label>
				                        <input type="text" id="fakturacni_obec" name="fakturacni_obec"  value="'.$fakturacni_obec.'" '.$required.'>
				                      </div>
				                      <div class="form-group">    
				                        <label for="fakturacni_psc">PSČ</label>
				                        <input type="text" id="fakturacni_psc" name="fakturacni_psc"  value="'.$fakturacni_psc.'" '.$required.'>
				                      </div>
				                      <div class="form-group">    
				                          <label for="cart-fa-state">Stát</label>
				                          <select name="fakturacni_id_stat" id="cart-fa-state"> ';
				                          
				                           foreach($_SESSION['staty_arr'] as $staty_k=>$staty_v)
						                    {
												 $ret .= '<option value="'.$staty_k.'" ';
												 if($fakturacni_id_stat == $staty_k){$ret .= ' selected '; }
												 $ret .= '>'.$staty_v.'</option>';
											}
											
					                      $ret .= '</select>
				                        </div>';
				                        
				                      if($_SESSION['doprava']['id_zasilkovna'] || $_SESSION['doprava']['id_ppl_parcel'] || $_SESSION['doprava']['id_dpd_pickup'] || $_SESSION['doprava']['id_cp_balikovna'] || $_SESSION['doprava']['id_cp_na_postu'])
				                      {
										  
									  }  
									  else
									  {
										 $ret .= '</div>
											</div>';	
									  } 
				                      
				                     $ret .= '<div class="form-spacer"> </div>';
				                    
				                    
				                    // registrace
				                    if(!$_SESSION['uzivatel'])
									{
				                    
				                    $ret .= '<div class="form-group --checkbox">
				                      <label for="cart-register" id="regToggle">S těmito údaji se chci zaregistrovat</label>
				                      <input type="checkbox" name="cart-register" id="cart-register" value="1">
				                      <div class="form-collapse">
				                        <div class="form-group">    
				                          <label for="cart-register-password">Heslo</label><input type="password" id="cart-register-password" name="heslo" disabled  
				                          pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d\w\W]{8,}$">
				                          <span class="show-password" id="showCartRegisterPassword" aria-label="Zobrazit heslo"></span> 
				                          <span class="label-subtext">(Minimum 8 znaků, musí obsahovat velké a malé písmena a alespoň 1 číslo)</span>
				                        </div>

				                      </div>
				                    </div>
				                    
				                    <div class="form-spacer"> </div>';
								    
								    }
				                    
				                    
				                    
				                    $ret .= '<div class="form-group">
				                      <label>Vložením údajů beru na vědomí <a href="/ochrana-osobnich-udaju">zpracování osobních údajů</a></label>
				                    </div>
				                    <div class="form-group --checkbox">
				                      <label for="register-agree1">Chci odebírat newsletter a souhlasím se <a href="/ochrana-osobnich-udaju">zpracováním osobních údajů</a> za účelem zasílání informací o novinkách a slevách.</label>
				                      <input type="checkbox" name="nl" value="1" id="register-agree1">
				                       <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
				                    </div>
				                  </div>
				                  <div class="cart-table__footer"><a class="cart-table__footer-link" href="/kosik?krok=2"> 
				                      <div class="cart-table__footer-icon"><img src="/img/icons/cheveron.svg" alt="Krok zpět"></div>
				                      <div class="cart-table__footer-text">Krok zpět</div></a></div>
				                </form>
				              </div>
				            </div>';
				            
				            
				                if($_SESSION['doprava']['cena'])
					            {
								   $doprava_cena_vyber = intval($_SESSION['doprava']['cena']);
								}
								else
								{
								   $doprava_cena_vyber = 0;
								}
								
								if($_SESSION['platba']['cena'])
					            {
								   $platba_cena_vyber = intval($_SESSION['platba']['cena']);
								}
								else
								{
								   $platba_cena_vyber = 0;
								}
						
						
				            // pravý sloupec
			                
			                $ret .= '<div class="col-12 col-lg-4"> 
				              <aside class="cart-aside">
				                <div class="cart-aside__title">Shrnutí objednávky</div>
				                <div class="cart-aside__table"> 
				                  <div class="cart-aside__row"> 
				                    <div class="cart-aside__table-th">Cena zboží</div>
				                    <div class="cart-aside__table-td">'.($this->cena_celkem - $kod_castka).' '.__MENA__.'</div>
				                  </div>
				                  <div class="cart-aside__row"> 
				                    <div class="cart-aside__table-th">Doprava a platba</div>
				                    <div class="cart-aside__table-td">'.($doprava_cena_vyber + $platba_cena_vyber ).' '.__MENA__.'</div>
				                  </div>
				                  <div class="cart-aside__row --main"> 
				                    <div class="cart-aside__table-th">Celková cena</div>
				                    <div class="cart-aside__table-td">'.round($this->cena_celkem + $doprava_cena_vyber + $platba_cena_vyber - $kod_castka).' '.__MENA__.'<small>s DPH</small></div>
				                  </div>
				                </div>
				         
				                
				                <button class="btn --cart" name="krok" value="4" form="cart-form-user" id="tl_krok_3">Souhrn objednávky <img src="/img/icons/cheveron-white.svg" alt="Pokračovat"></button>
				              </aside>
				            </div>
				          </div>
				        </div>
				      </section>'; 
						
				  }
			 
    
			      
		   }   
		   elseif($this->krok==4)
		   {
			      // záhlaví
			      $ret .= '<section class="cart-heading">
			        <div class="container">
			          <div class="cart-heading__item --complete">
			            <div class="cart-heading__icon"><img src="/img/icons/cart-big.svg" alt="1. Nákupní košík"></div>
			            <div class="cart-heading__title">1. Nákupní košík</div>
			          </div>
			          <div class="cart-heading__item --complete">
			            <div class="cart-heading__icon"><img src="/img/icons/car.svg" alt="2. Doprava a platba"></div>
			            <div class="cart-heading__title">2. Doprava a platba</div>
			          </div>
			          <div class="cart-heading__item --complete">
			            <div class="cart-heading__icon"><img src="/img/icons/personal-data.svg" alt="3. Osobní údaje"></div>
			            <div class="cart-heading__title">3. Osobní údaje</div>
			          </div>
			          <div class="cart-heading__item --active">
			            <div class="cart-heading__icon"><img src="/img/icons/summary.svg" alt="4. Shrnutí objednávky"></div>
			            <div class="cart-heading__title">4. Shrnutí objednávky</div>
			          </div>
			        </div>
			      </section>';
			      
 
			      // kontrola doprava a platba
			      if(!$_SESSION['doprava']['id'] || !$_SESSION['platba']['id'])
				  {
				    $ret .= '<section class="cart-content">
				        <div class="container">
				          <div class="row"><b>Nevybrali jste dopravu a platbu.</b> Vraťte se prosím zpět a vyberte.
				          </div>
				          </div>
				          </section>';
				  }
				  else
				  {
					  
					// údaje uložíme do sešny
					if($_POST['r_u'])
					{
					  $this->nastavSessZakaznik();  
				    }
					
                   
                    if($_SESSION['doprava']['cena'])
		            {
					   $doprava_cena_vyber = intval($_SESSION['doprava']['cena']);
					}
					else
					{
					   $doprava_cena_vyber = 0;
					}
					
					if($_SESSION['platba']['cena'])
		            {
					   $platba_cena_vyber = intval($_SESSION['platba']['cena']);
					}
					else
					{
					   $platba_cena_vyber = 0;
					}
								
								
					$ret .= '<div class="container"> 
				        <div class="page --narrow">
				          <section class="page-heading">
				            <h1>Souhrn objednávky</h1>
				          </section>
				          <div class="cart-summary">
				            <form class="cart-summary__form" method="post" action="/kosik?krok=5" id="cart-form">';
				            
				            
				            $ret .= $this->vypis();
				            
	
				      
				              $ret .= '<table class="cart-summary__price"> 
				                <tbody> 
				                  <tr> 
				                    <td>Cena zboží</td>
				                    <td>'.$this->kosikCenaCelkem().' '.__MENA__.'</td>
				                  </tr>
				                  <tr> 
				                    <td>'.$_SESSION['doprava']['nazev'].'</td>
				                    <td>'.$_SESSION['doprava']['cena'].' '.__MENA__.'</td>
				                  </tr>
				                  <tr> 
				                    <td>'.$_SESSION['platba']['nazev'].'</td>
				                    <td>'.$_SESSION['platba']['cena'].' '.__MENA__.'</td>
				                  </tr>
				                </tbody>
				              </table>
				              <table class="cart-summary__price-sum">
				                <tbody> 
				                  <tr> 
				                    <td>Celková cena objednávky</td>
				                    <td>'.round($this->cena_celkem + $doprava_cena_vyber + $platba_cena_vyber - $kod_castka).' '.__MENA__.'</td>
				                  </tr>
				                </tbody>
				              </table>
				              
				               <table class="cart-summary__table">
				                <thead> 
				                  <tr> 
				                    <th>Osobní údaje</th>
				                    <th></th>
				                  </tr>
				                </thead>
				                <tbody>
				                <tr> 
				                    <td>Jméno</td>
				                    <td>'.$_SESSION['reg_kosik']['jmeno'].'</td>
				                  </tr>
				                  <tr> 
				                    <td>Příjmení</td>
				                    <td>'.$_SESSION['reg_kosik']['prijmeni'].'</td>
				                  </tr>
				                  <tr> 
				                    <td>Telefon</td>
				                    <td>'.$_SESSION['reg_kosik']['telefon'].'</td>
				                  </tr>
				                  <tr> 
				                    <td>E-mail</td>
				                    <td>'.$_SESSION['reg_kosik']['email'].'</td>
				                  </tr>
				                  </tbody>
				              </table> 
				                
				              <table class="cart-summary__table">
				                <thead> 
				                  <tr> 
				                    <th>';
				                    
				                    if(!$_SESSION['reg_kosik']['fakturacni_obec'])
				                    {
									  $ret .= ' Dodací a fakturační adresa ';
									}
									else
									{
									  $ret .= ' Dodací adresa';
									}
				                   
				                    
				                    $ret .= '</th>
				                    <th></th>
				                  </tr>
				                </thead>
				                <tbody> ';
				                
				                 if($_SESSION['reg_kosik']['dodaci_nazev'] && (!$_SESSION['doprava']['id_cp_na_postu'] && !$_SESSION['doprava']['id_cp_balikovna']  
				                 && !$_SESSION['doprava']['id_zasilkovna'] && !$_SESSION['doprava']['id_ppl_parcel'] && !$_SESSION['doprava']['id_dpd_pickup']))
				                  {
									 
									$ret .= '<tr> 
				                    <td>Jméno a příjmení</td>
				                    <td>'.$_SESSION['reg_kosik']['dodaci_nazev'].'</td>
				                    </tr>';
				                  
								  }
								  else
								  {
								  
								   $ret .= '<tr> 
				                    <td>Jméno</td>
				                    <td>'.$_SESSION['reg_kosik']['jmeno'].'</td>
				                  </tr>
				                  <tr> 
				                    <td>Příjmení</td>
				                    <td>'.$_SESSION['reg_kosik']['prijmeni'].'</td>
				                  </tr>';
				                  
								  }
				                  
				                  
				                  // úprava pro balík na poštu a do balíkovny
				                  if($_SESSION['doprava']['id_cp_na_postu'])
				                  {
									$ret .= '
									<tr> 
					                    <td>Balík na poštu </td>
					                    <td>'.$_SESSION['doprava']['nazev_cp_na_postu'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>Adresa</td>
					                    <td>'.$_SESSION['doprava']['adresa_cp_na_postu'].'</td>
					                  </tr>';
								  }
								  elseif($_SESSION['doprava']['id_cp_balikovna'])
								  {
									$ret .= '
									<tr> 
					                    <td>Balíkovna</td>
					                    <td>'.$_SESSION['doprava']['nazev_cp_balikovna'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>Adresa</td>
					                    <td>'.$_SESSION['doprava']['adresa_cp_balikovna'].'</td>
					                  </tr>';
								  }
								  elseif($_SESSION['doprava']['id_zasilkovna'])
								  {
									$ret .= '
									<tr> 
					                    <td>Zásilkovna</td>
					                    <td>'.$_SESSION['doprava']['nazev_zasilkovna'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>Adresa</td>
					                    <td>'.$_SESSION['doprava']['ulice_zasilkovna'].', '.$_SESSION['doprava']['obec_zasilkovna'].', '.$_SESSION['doprava']['psc_zasilkovna'].'</td>
					                  </tr>';
								  }
								  elseif($_SESSION['doprava']['id_ppl_parcel'])
								  {
									$ret .= '
									<tr> 
					                    <td>PPL Parcel</td>
					                    <td>'.$_SESSION['doprava']['nazev_ppl_parcel'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>Adresa</td>
					                    <td>'.$_SESSION['doprava']['ulice_ppl_parcel'].', '.$_SESSION['doprava']['obec_ppl_parcel'].', '.$_SESSION['doprava']['psc_ppl_parcel'].'</td>
					                  </tr>';
								  }
								  elseif($_SESSION['doprava']['id_dpd_pickup'])
								  {
									$ret .= '
									<tr> 
					                    <td>DPD Pickup</td>
					                    <td>'.$_SESSION['doprava']['nazev_dpd_pickup'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>Adresa</td>
					                    <td>'.$_SESSION['doprava']['ulice_dpd_pickup'].', '.$_SESSION['doprava']['obec_dpd_pickup'].', '.$_SESSION['doprava']['psc_dpd_pickup'].'</td>
					                  </tr>';
								  }
								  else
								  {
									$ret .= '
					                  <tr> 
					                    <td>Ulice, č. p.</td>
					                    <td>'.$_SESSION['reg_kosik']['dodaci_ulice'].' '.$_SESSION['reg_kosik']['dodaci_cislo'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>Město</td>
					                    <td>'.$_SESSION['reg_kosik']['dodaci_obec'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>PSČ</td>
					                    <td>'.$_SESSION['reg_kosik']['dodaci_psc'].'</td>
					                  </tr>
					                  <tr> 
					                    <td>Stát</td>
					                    <td>'.$_SESSION['staty_arr'][$_SESSION['reg_kosik']['dodaci_id_stat']].'</td>
					                  </tr>';
								  }
				                 
				                  
	
				                $ret .= '</tbody>
				              </table>';
				              
				              // fakturační pokud je
				              if($_SESSION['reg_kosik']['fakturacni_jmeno'] || $_SESSION['reg_kosik']['fakturacni_firma'] || $_SESSION['reg_kosik']['fakturacni_obec'])
				              {
							  
							  $ret .= '<table class="cart-summary__table">
				                <thead> 
				                  <tr>
				                    <th>Fakturační adresa</th>
				                    <th></th>
				                  </tr>
				                </thead>
				                <tbody> 
				                  <tr> 
				                    <td>Jméno</td>
				                    <td>'.$_SESSION['reg_kosik']['fakturacni_jmeno'].'</td>
				                  </tr>';
				                  if($_SESSION['reg_kosik']['fakturacni_firma'])
				                  {
					                  $ret .= '<tr> 
					                    <td>Firma</td>
					                    <td>'.$_SESSION['reg_kosik']['fakturacni_firma'].'</td>
					                  </tr>';
							      }
				                  
				                  $ret .= '<tr> 
				                    <td>Ulice, č. p.</td>
				                    <td>'.$_SESSION['reg_kosik']['fakturacni_ulice'].' '.$_SESSION['reg_kosik']['fakturacni_cislo'].'</td>
				                  </tr>
				                  <tr> 
				                    <td>Město</td>
				                    <td>'.$_SESSION['reg_kosik']['fakturacni_obec'].'</td>
				                  </tr>
				                  <tr> 
				                    <td>PSČ</td>
				                    <td>'.$_SESSION['reg_kosik']['fakturacni_psc'].'</td>
				                  </tr>';
				                  
				                  if($_SESSION['reg_kosik']['fakturacni_id_stat'])
				                  {
								  
								   $ret .= '<tr> 
				                    <td>Stát</td>
				                    <td>'.$_SESSION['staty_arr'][$_SESSION['reg_kosik']['fakturacni_id_stat']].'</td>
				                  </tr>';
								  
								  }
								  
								  if($_SESSION['reg_kosik']['ic'])
				                  {
								  
								   $ret .= '<tr> 
				                    <td>IČ</td>
				                    <td>'.$_SESSION['reg_kosik']['ic'].'</td>
				                  </tr>';
								  
								  }
								  
								  if($_SESSION['reg_kosik']['dic'])
				                  {
								  
								   $ret .= '<tr> 
				                    <td>DIČ</td>
				                    <td>'.$_SESSION['reg_kosik']['dic'].'</td>
				                  </tr>';
								  
								  }
	 
	
				                $ret .= '</tbody>
				              </table>';
							  
							  }
				              
				              
				               $ret .= '<div class="form-group">    
				                <label for="order-text">Poznámka k objednávce</label>
				                <textarea type="text" id="order-text" name="poznamka" rows="4">'.$_SESSION['reg_kosik']['poznamka'].'</textarea>
				              </div>
				              <div class="form-group --checkbox">
				                <label for="order-agree1">Nesouhlasím se zasláním dotazníku spokojenosti v rámci programu Ověřeno zákazníky, který pomáhá zlepšovat vaše služby.  </label>
				                <input type="checkbox" name="nesouhlas_heureka" value="1" id="order-agree1">
				              </div>
				              <div class="form-group --checkbox">
				                <label for="order-agree2">Souhlasím s <a href="/obchodni-podminky">obchodními podmínkami</a></label>
				                <input type="checkbox" name="souhlas_op" value="1" required id="order-agree2">
				              </div>
				              <button class="btn --cart" name="finish-order">Odeslat objednávku</button>
				              <div class="cart-table__footer"><a class="cart-table__footer-link" href="/kosik?krok=3"> 
				                  <div class="cart-table__footer-icon"><img src="/img/icons/cheveron.svg" alt="Krok zpět"></div>
				                  <div class="cart-table__footer-text">Krok zpět</div></a></div>
				                   <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
				            </form>
				          </div>
				        </div>
				      </div>';
				  
				  
				  
				  }
		      
		      
		      
		   } 
		   elseif($this->krok==5)
		   {	
			   $options_pass = ['cost' => 12,];
			   
		       // kontrola doprava a platba
			      if(!$_SESSION['doprava']['id'] || !$_SESSION['platba']['id'])
				  {
				    $ret .= '<section class="cart-content">
				        <div class="container">
				          <div class="row"><b>Nevybrali jste dopravu a platbu.</b> Vraťte se prosím zpět a vyberte.
				          </div>
				          </div>
				          </section>';
				  }
				  else
				  {
					  if($_POST['poznamka'])
					  {
					    $_SESSION['reg_kosik']['poznamka'] = strip_tags($_POST['poznamka']);
					  }
					
					// pokud se jedná o přihlášeného zákazníka s registrací tak updatujeme údaje
					if($_SESSION['uzivatel']['typ']==1 && $_SESSION['reg_kosik']['jmeno'])
				    {	
						if($_SESSION['doprava']['id_stat_zasilkovna'])
						{
						    $dodaci_id_stat = $_SESSION['doprava']['id_stat_zasilkovna'];
						}
						else
						{
							$dodaci_id_stat = $_SESSION['reg_kosik']['dodaci_id_stat'];
						}
						
					   	$data_zak_update = array(
						'jmeno' => sanitize($_SESSION['reg_kosik']['jmeno']),
						'prijmeni' => sanitize($_SESSION['reg_kosik']['prijmeni']),
						'uz_jmeno' => sanitize($_SESSION['reg_kosik']['email']),
						'email' => sanitize($_SESSION['reg_kosik']['email']),
						'telefon' => sanitize(str_replace(' ','',$_SESSION['reg_kosik']['telefon'])),
						
						'dodaci_nazev' => sanitize($_SESSION['reg_kosik']['dodaci_nazev']),
						'dodaci_ulice' => sanitize($_SESSION['reg_kosik']['dodaci_ulice']),
						'dodaci_cislo' => sanitize($_SESSION['reg_kosik']['dodaci_cislo']),
						'dodaci_obec' => sanitize($_SESSION['reg_kosik']['dodaci_obec']),
						'dodaci_psc' => sanitize($_SESSION['reg_kosik']['dodaci_psc']),
						'dodaci_id_stat' => intval($dodaci_id_stat),
						
						'fakturacni_jmeno' => sanitize($_SESSION['reg_kosik']['fakturacni_jmeno']),
						'fakturacni_firma' => sanitize($_SESSION['reg_kosik']['fakturacni_firma']),
						'fakturacni_ulice' => sanitize($_SESSION['reg_kosik']['fakturacni_ulice']),
						'fakturacni_cislo' => sanitize($_SESSION['reg_kosik']['fakturacni_cislo']),
						'fakturacni_obec' => sanitize($_SESSION['reg_kosik']['fakturacni_obec']),
						'fakturacni_psc' => sanitize($_SESSION['reg_kosik']['fakturacni_psc']),
						'fakturacni_id_stat' => intval($_SESSION['reg_kosik']['fakturacni_id_stat']),
						'ic' => sanitize($_SESSION['reg_kosik']['ic']),
						'dic' =>sanitize( $_SESSION['reg_kosik']['dic']),
						'ip' => getip(),
	
						'nl' => intval($_SESSION['reg_kosik']['nl']));
						

						$where_zak_update = array('id' => intval($_SESSION['uzivatel']['id']));
						$query_zak_update = Db::update('zakaznici', $data_zak_update, $where_zak_update);
						
						$id_zak = intval($_SESSION['uzivatel']['id']);
						
					}
					
					
					// pokud se jedná o příznak chci_registraci tak vytvoříme nový regulérní účet a zašleme emailovou rekapitulaci
					if(!$_SESSION['uzivatel'] && $_SESSION['reg_kosik']['chci_registraci']==1)
				    {
						if($_SESSION['doprava']['id_stat_zasilkovna'])
						{
						    $dodaci_id_stat = $_SESSION['doprava']['id_stat_zasilkovna'];
						}
						else
						{
							$dodaci_id_stat = $_SESSION['reg_kosik']['dodaci_id_stat'];
						}
						
						$data_zak_insert = array(
						    'jmeno' => sanitize($_SESSION['reg_kosik']['jmeno']),
							'prijmeni' => sanitize($_SESSION['reg_kosik']['prijmeni']),
							'uz_jmeno' => sanitize($_SESSION['reg_kosik']['email']),
							'heslo' => password_hash($_SESSION['reg_kosik']['heslo'], PASSWORD_BCRYPT, $options_pass),
							'email' => sanitize($_SESSION['reg_kosik']['email']),
							'telefon' => sanitize($_SESSION['reg_kosik']['telefon']),
							
							'dodaci_nazev' => sanitize($_SESSION['reg_kosik']['dodaci_nazev']),
							'dodaci_ulice' => sanitize($_SESSION['reg_kosik']['dodaci_ulice']),
							'dodaci_cislo' => sanitize($_SESSION['reg_kosik']['dodaci_cislo']),
							'dodaci_obec' => sanitize($_SESSION['reg_kosik']['dodaci_obec']),
							'dodaci_psc' => strip_tags($_SESSION['reg_kosik']['dodaci_psc']),
							'dodaci_id_stat' => intval($dodaci_id_stat),
							
							'fakturacni_jmeno' => sanitize($_SESSION['reg_kosik']['fakturacni_jmeno']),
							'fakturacni_firma' => sanitize($_SESSION['reg_kosik']['fakturacni_firma']),
							'fakturacni_ulice' => sanitize($_SESSION['reg_kosik']['fakturacni_ulice']),
							'fakturacni_cislo' => sanitize($_SESSION['reg_kosik']['fakturacni_cislo']),
							'fakturacni_obec' => sanitize($_SESSION['reg_kosik']['fakturacni_obec']),
							'fakturacni_psc' => strip_tags($_SESSION['reg_kosik']['fakturacni_psc']),
							'fakturacni_id_stat' => intval($_SESSION['reg_kosik']['fakturacni_id_stat']),
							'ic' => strip_tags($_SESSION['reg_kosik']['ic']),
							'dic' => strip_tags($_SESSION['reg_kosik']['dic']),
							'souhlas_ou' => 1,
							'ip' => getip(),
							'datum' => time(),
							'nl' => intval($_SESSION['reg_kosik']['nl']),
							'aktivni' => 1,
							'bez_registrace' => 0
						     );
					  
					  $query_zak_insert = Db::insert('zakaznici', $data_zak_insert);
					  
					  $id_zak = intval($query_zak_insert);
					  
					  
					}
					elseif(!$_SESSION['uzivatel'] && !$_SESSION['reg_kosik']['chci_registraci'])
				    {
						// jinak se jedná o nereg. zákazníka, který vyplnil jednorázově údaje v košíku
						if($_SESSION['doprava']['id_stat_zasilkovna'])
						{
						    $dodaci_id_stat = $_SESSION['doprava']['id_stat_zasilkovna'];
						}
						else
						{
							$dodaci_id_stat = $_SESSION['reg_kosik']['dodaci_id_stat'];
						}
						
						$data_zak_insert = array(
						     'jmeno' => sanitize($_SESSION['reg_kosik']['jmeno']),
							'prijmeni' => sanitize($_SESSION['reg_kosik']['prijmeni']),
							'uz_jmeno' => sanitize($_SESSION['reg_kosik']['email']),
							'heslo' => password_hash(random_strings(10).'_'.sha1(time()), PASSWORD_BCRYPT, $options_pass),
							'email' => sanitize($_SESSION['reg_kosik']['email']),
							'telefon' => sanitize($_SESSION['reg_kosik']['telefon']),
							
							'dodaci_nazev' => sanitize($_SESSION['reg_kosik']['dodaci_nazev']),
							'dodaci_ulice' => sanitize($_SESSION['reg_kosik']['dodaci_ulice']),
							'dodaci_cislo' => sanitize($_SESSION['reg_kosik']['dodaci_cislo']),
							'dodaci_obec' => sanitize($_SESSION['reg_kosik']['dodaci_obec']),
							'dodaci_psc' => strip_tags($_SESSION['reg_kosik']['dodaci_psc']),
							'dodaci_id_stat' => intval($dodaci_id_stat),
							
							'fakturacni_jmeno' => sanitize($_SESSION['reg_kosik']['fakturacni_jmeno']),
							'fakturacni_firma' => sanitize($_SESSION['reg_kosik']['fakturacni_firma']),
							'fakturacni_ulice' => sanitize($_SESSION['reg_kosik']['fakturacni_ulice']),
							'fakturacni_cislo' => sanitize($_SESSION['reg_kosik']['fakturacni_cislo']),
							'fakturacni_obec' => sanitize($_SESSION['reg_kosik']['fakturacni_obec']),
							'fakturacni_psc' => strip_tags($_SESSION['reg_kosik']['fakturacni_psc']),
							'fakturacni_id_stat' => intval($_SESSION['reg_kosik']['fakturacni_id_stat']),
							'ic' => strip_tags($_SESSION['reg_kosik']['ic']),
							'dic' => strip_tags($_SESSION['reg_kosik']['dic']),
							'souhlas_ou' => 1,
							'ip' => getip(),
							'datum' => time(),
							'nl' => intval($_SESSION['reg_kosik']['nl']),
							'aktivni' => 1,
							'bez_registrace' => 1
						     );
					  
					  $query_zak_insert = Db::insert('zakaznici', $data_zak_insert);
					  
					  $id_zak = intval($query_zak_insert);
					
					}
					
					// platba kartou
					if($_SESSION['platba']['karta']==1)
					{
						if($this->objednavka($id_zak,$kod_castka,1,0))
						{
						 
					     $text_obj = '<form name="pk" id="pk" method="post" action="/csob">
					       <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
					       <input type="submit" name="sk" class="tl_karta_kosik" value="Zaplatit kartou">
					       <br>
					       Během chvíle budete přesměrováni na platební bránu.
					      </form>';
					    }
					    else
						{
						   // chyba
						   $text_obj = '<b style="color: red;">Objednávku se nepodařilo uložit.</b>';
						}
					}
					else
					{
						// odeslání objednávky a poděkování
						if($this->objednavka($id_zak,$kod_castka,0,0))
						{
						   // google el. obchod
						   $text_obj = $this->googleElObchod();	
						   
						   // google zákaznické recenze
						   $text_obj .= $this->googleZakaznickeRecenze();
						   
						   // sklik konverze
						   $text_obj .= $this->sklikKonverze();	
						   
						   // zboží konverze
						   $text_obj .= $this->zboziKonverze();
						   
						   // heureka konverze
						   $text_obj .= $this->heurekaKonverze();
						   
						   // heureka ověřeno zákazníky
						   if($_SESSION['reg_kosik']['nesouhlas_heureka']==0 && __HEUREKA_ID_OVERENO_ZAKAZNIKY__)
						   {
						     $url_heureka = 'https://www.heureka.cz/direct/dotaznik/objednavka.php?id='.__HEUREKA_ID_OVERENO_ZAKAZNIKY__.'&email='.$_SESSION['reg_kosik']['email'].'&orderid='.$this->id_obj;
							 file_get_contents($url_heureka);
						   }
							
							
						   $text_obj .= ' <div class="cart-empty__img"> <img src="/img/cart-complete.svg" alt="Objednávka byla úspěšná"></div>
				            <h1 class="cart-empty__title">Objednávka byla úspěšná</h1>
				            <p class="cart-empty__text">Rekapitulace objednávky a platební pokyny byly odeslány na Váš email.</p>';
				            
				            // QR platba
				            if($_SESSION['platba']['prevodem']==1)
				            {
								$data_obj = Db::queryRow('SELECT cena_celkem_s_dph FROM objednavky WHERE cislo_obj = ?', array($this->id_obj));
								$text_obj .= '<b>QR platba:</b><br>';

								
								   $qr_platba = new QRPlatba(trim(str_replace(' ','',__IBAN__)), str_replace(',','.',$data_obj['cena_celkem_s_dph']));
								   $qr_platba->setMessage('PLATBA ZA NAKUP NA '.mb_strtoupper($_SERVER['SERVER_NAME']));
								   $qr_platba->setVariableSym($this->id_obj);
								   $text_obj .= $qr_platba->getQRCodeImage(1);
   
								$text_obj .= '<br>';
								$text_obj .= 'Částka k úhradě: <b>'.$data_obj['cena_celkem_s_dph'].' '.__MENA__.'</b><br>';
								$text_obj .= 'Bankovní účet: <b>'.__CISLO_UCTU__.'</b><br>';
								$text_obj .= 'IBAN: <b>'.__IBAN__.'</b><br>';
								$text_obj .= 'Variabilní symbol: <b>'.$this->id_obj.'</b><br>';
							}
				            
				            $text_obj .= '<a class="btn --green" href="/">Zpět do obchodu</a>
				            <div class="cart-empty__alert"> <img src="/img/icons/info.svg" alt="Informace">
				              <div class="cart-empty__alert-text">Pokud máte problém s objednávkou, kontaktujte nás na '.__FORM_EMAIL__.' nebo na telefonním čísle '.__TELEFON__.'</div>
				            </div>';
				            
				       	   // vymažeme údaje
						   $this->smazatVseZKosiku();
						   unset($_SESSION['slevovy_kod']);
						   unset($_SESSION['reg_kosik']);	
						   unset($_SESSION['doprava']);
						   unset($_SESSION['platba']);
						   //unset($_SESSION['uzivatel']);
						   
						}
						else
						{
						   // chyba
						   $text_obj = '<b style="color: red;">Objednávku se nepodařilo odeslat.</b>';
						}
					}
					
					
					$ret .= '<div class="page">  
			          <div class="cart-empty">
			           '.$text_obj.'
			          </div>
			        </div>';
			        
			        
				  }
				  

		       
		   } 
		   
		   
		   
	   }
	   else
	   {
	      // prázdný košík
	      $ret = '<div class="container"> 
			        <div class="page">  
			          <div class="cart-empty">
			            <div class="cart-empty__img"> <img src="/img/cart-empty.svg" alt="Váš košík je prázdný"></div>
			            <h1 class="cart-empty__title">Váš košík je prázdný</h1>
			            <p class="cart-empty__text">Pro pokračování v nákupu prosím nejprve vložte vybraný produkt do košíku.</p><a class="btn" href="/">Začít nakupovat</a>
			          </div>
			        </div>
			      </div>';
	   
	   }
	   
	   
	   return $ret;
	   
	   
	   
	}
	
	
	
	
	public function objednavka($id_zak,$kod_castka,$karta,$karta_navrat)
	{
		
		// uložíme objednávku do databáze a sestavíme HTML email

		//$dbl = Db::lockRead('objednavky');
		//$dbl2 = Db::lockRead('objednavky_polozky');
		
		if($karta_navrat == 1)
		{
		   $id_obj = $_SESSION['reg_kosik']['id_obj'];
		}
		else
		{
			$data_o = Db::queryRow('SELECT max(cislo_obj) AS MAXI FROM objednavky', array());
	
				if(!$data_o['MAXI'])
				{
				 $posledni_id = "00001";
				 $id_obj = date("Y").$posledni_id;
				}
				else
				{
					// kazdy rok zacneme od 0001
					$rok_posledni_obj =  substr($data_o['MAXI'],0,4);
					if($rok_posledni_obj!=date("Y"))
					{
					 // zmena roku
					 // zaciname od 0001
					 $posledni_id = "00001";
					 $id_obj = date("Y").$posledni_id;
						
					}
					else
					{
					 $posledni_id = substr($data_o['MAXI'],4,5);
					 $posledni_id2 = ($posledni_id+1);
					 $delka = strlen($posledni_id2);
					if($delka<5)
					{
					  for ($i = $delka; $i <= 4; $i++) 
					  {
					   $posledni_id2 = "0".$posledni_id2;
					  }
					}
					
					$id_obj = date("Y").$posledni_id2;
						
					}	
				}
		}
		
		$this->id_obj = $id_obj;

		
		if($_SESSION['doprava']['cena'])
		{
		   $doprava_cena_vyber = intval($_SESSION['doprava']['cena']);
		   $doprava_cena_vyber_bez_dph = round((intval($_SESSION['doprava']['cena']) / (intval($_SESSION['doprava']['dph'])/100+1) ),2);
		}
		else
		{
		   $doprava_cena_vyber = 0;
		   $doprava_cena_vyber_bez_dph = 0;
		}
		
		if($_SESSION['platba']['cena'])
		{
		   $platba_cena_vyber = intval($_SESSION['platba']['cena']);
		   $platba_cena_vyber_bez_dph = round((intval($_SESSION['platba']['cena']) / (intval($_SESSION['platba']['dph'])/100+1) ),2);
		}
		else
		{
		   $platba_cena_vyber = 0;
		   $platba_cena_vyber_bez_dph = 0;
		}
					
						
		// rekapitulace zboží
		
		$obj_html = '<body style="margin:0;font-family: SF Pro Display,apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif;">
	    <div style="background: #f5f5f5;padding: 40px 0;">
	      <div style="margin: 0 auto; max-width: 800px;background:white;box-shadow: 0px 4px 20px rgba(0, 0, 0,0.01);">
	        <div style="display:block;text-align:center;padding: 50px 40px;border-bottom: 2px solid #f5f5f5"><img src="'.__URL__.'/img/logo.png" height="50px"/>
	          <h1 style="display: block;width: 100%;text-align:center;font-size: 22px;margin: 0;margin-top: 30px;">Rekapitulace objednávky č. '.$id_obj.'</h1>
	        </div>
	        
	        <div style="display: flex;flex-wrap: wrap;">
				
	          <div style="padding: 30px 40px;border-bottom: 2px solid #f5f5f5;flex-grow: 1;">
	            <table style="line-height: 1.8;">
	              <tbody> 
	                <tr>
	                  <td style="font-size: 18px;font-weight: 600;padding-bottom:10px">Osobní údaje</td>
	                </tr>
	                <tr>
	                  <td>'.strip_tags($_SESSION['reg_kosik']['jmeno']).' '.strip_tags($_SESSION['reg_kosik']['prijmeni']).'</td>
	                </tr>
	                <tr>
	                  <td>E-mail: '.strip_tags($_SESSION['reg_kosik']['email']).'</td>
	                </tr>
	                <tr>
	                  <td>Telefon: '.strip_tags($_SESSION['reg_kosik']['telefon']).'</td>
	                </tr>
	                 <tr>
	                  <td>Poznámka: '.strip_tags($_SESSION['reg_kosik']['poznamka']).'</td>
	                </tr>
	              </tbody>
	            </table>
	          </div>';
	          
	          // dodací údaje
              $obj_html .= '<div style="padding: 30px 40px;border-bottom: 2px solid #f5f5f5;flex-grow: 1;">
	           <table style="line-height: 1.8;">
	            <tbody> 
	              <tr>
	                <td style="font-size: 18px;font-weight: 600;padding-bottom:10px">';
	                
	                if(!$_SESSION['reg_kosik']['fakturacni_obec'])
                    {
					  $obj_html .= ' Dodací a fakturační adresa ';
					}
					else
					{
					  $obj_html .= ' Dodací adresa';
					}
	                
	                $obj_html .= '</td>
	              </tr>
	              <tr>
	                <td>';
	                
	                if($_SESSION['reg_kosik']['dodaci_nazev'])
					{
					  $obj_html .= strip_tags($_SESSION['reg_kosik']['dodaci_nazev']);
					}
					else
					{
					  $obj_html .= strip_tags($_SESSION['reg_kosik']['jmeno']).' '.strip_tags($_SESSION['reg_kosik']['prijmeni']);
					}
	                
	                $obj_html .= '</td>
	              </tr>
	              <tr>
	                <td>'.strip_tags($_SESSION['reg_kosik']['dodaci_ulice']).' '.strip_tags($_SESSION['reg_kosik']['dodaci_cislo']).'</td>
	              </tr>
	              <tr>
	                <td>'.strip_tags($_SESSION['reg_kosik']['dodaci_psc']).' '.strip_tags($_SESSION['reg_kosik']['dodaci_obec']).'</td>
	              </tr>
	              <tr>
	                <td>'.$_SESSION['staty_arr'][intval($_SESSION['reg_kosik']['dodaci_id_stat'])].'</td>
	              </tr>
	            </tbody>
	          </table>
	        </div>';
	        
	        // fakturační
	        if($_SESSION['reg_kosik']['fakturacni_obec'])
            {
	            $obj_html .= '<div style="padding: 30px 40px;border-bottom: 2px solid #f5f5f5;flex-grow: 1;">
	            <table style="line-height: 1.8;">
	              <tbody> 
	                <tr>
	                  <td style="font-size: 18px;font-weight: 600;padding-bottom:10px">Fakturační údaje</td>
	                </tr>
	                <tr>
	                  <td>';
	                      if($_SESSION['reg_kosik']['fakturacni_firma'])
						  {
							  $obj_html .= strip_tags($_SESSION['reg_kosik']['fakturacni_firma']).'<br>';
						  }
						  if($_SESSION['reg_kosik']['fakturacni_jmeno'])
						  {
							  $obj_html .= strip_tags($_SESSION['reg_kosik']['fakturacni_jmeno']);
						  }
	                  $obj_html .= '</td>
	                </tr>
	                <tr>
	                  <td>'.strip_tags($_SESSION['reg_kosik']['fakturacni_ulice']).' '.strip_tags($_SESSION['reg_kosik']['fakturacni_cislo']).'</td>
	                </tr>
	                <tr>
	                  <td>'.strip_tags($_SESSION['reg_kosik']['fakturacni_psc']).' '.strip_tags($_SESSION['reg_kosik']['fakturacni_obec']).'</td>
	                </tr>
	                <tr>
	                  <td>'.$_SESSION['staty_arr'][intval($_SESSION['reg_kosik']['fakturacni_id_stat'])].'</td>
	                </tr>';
	                if($_SESSION['reg_kosik']['ic'])
	                {
	                  $obj_html .= '<tr>
	                  <td>IČ: '.strip_tags($_SESSION['reg_kosik']['ic']).'</td>
	                  </tr>';
				    }
				    
	                if($_SESSION['reg_kosik']['dic'])
	                {
					  $obj_html .= '<tr>
	                  <td>DIČ: '.strip_tags($_SESSION['reg_kosik']['dic']).'</td>
	                  </tr>';
				    }
	                
	              $obj_html .= '</tbody>
	            </table>
	          </div>';
		   
		    }
          
          $obj_html .= '</div>';
          
          $obj_html .= '<div style="display:block;padding: 30px 40px;border-bottom: 2px solid #f5f5f5">
          <table style="line-height: 1.8;border-collapse: collapse;width: 100%;">
            <tbody> 
              <tr>
                <td style="font-size: 18px;font-weight: 600;padding-bottom:10px">Položky</td>
              </tr>';
        

		$obj_html .= $this->vypis();
		
		$celkova_cena_objednavky = round($this->kosikCenaCelkem() + $doprava_cena_vyber + $platba_cena_vyber - $kod_castka);
		
		$obj_html .= '</tbody>
	          </table>
	          <table style="line-height: 1.8;border-collapse: collapse;margin-top: 20px;width: 100%;">
	            <tbody> 
	              <tr colspan="2">
	                <td style="font-weight:500;font-size: 18px;">Způsob dopravy</td>
	              </tr>
	              <tr>
	                <td>'.$_SESSION['doprava']['nazev'].'</td>
	                <td style="text-align:right;font-weight:500;">'.$doprava_cena_vyber.' '.__MENA__.'</td>
	              </tr>
	              <tr colspan="2">
	                <td style="padding-top: 20px;font-weight:500;font-size: 18px;">Způsob platby</td>
	              </tr>
	              <tr>
	                <td style="padding-bottom:20px;">'.$_SESSION['platba']['nazev'].'</td>
	                <td style="text-align:right;font-weight:500;padding-bottom:20px;">'.$platba_cena_vyber.' '.__MENA__.'</td>
	              </tr>
	              <tr>
	                <td style="padding-top: 20px;font-weight:bold;font-size: 20px;border-top:1px solid #e5e5e5;">Celková cena objednávky:</td>
	                <td style="padding-top: 20px;text-align:right;font-weight:bold;font-size: 20px;border-top:1px solid #e5e5e5;">'.$celkova_cena_objednavky.' '.__MENA__.'</td>
	              </tr>
	            </tbody>
	          </table>';
	          
	          // QR platba
	            if($_SESSION['platba']['prevodem']==1)
	            {
					$obj_html .= '<b>QR Platba</b><br>';
					//$obj_html .= $this->qrPlatba($celkova_cena_objednavky,$id_obj,200);
					
					$qr_platba = new QRPlatba(trim(str_replace(' ','',__IBAN__)), str_replace(',','.',$celkova_cena_objednavky));
				    $qr_platba->setMessage('PLATBA ZA NAKUP NA '.mb_strtoupper($_SERVER['SERVER_NAME']));
				    $qr_platba->setVariableSym($id_obj);
				    $obj_html .= $qr_platba->getQRCodeImage(2);
					
					$obj_html .= '<br>Částka k úhradě: <b>'.$celkova_cena_objednavky.' '.__MENA__.'</b><br>';
					$obj_html .= 'Bankovní účet: <b>'.__CISLO_UCTU__.'</b><br>';
					$obj_html .= 'IBAN: <b>'.__IBAN__.'</b><br>';
					$obj_html .= 'Variabilní symbol: <b>'.$id_obj.'</b><br>';
				}
	          
	        $obj_html .= '</div>
	      </div>
	      <div style="margin: 0 auto; max-width: 800px;text-align:center;"><a href="https://ozeo.cz" style="color:black; text-decoration:none;margin: 20px 0 10px 0;display: block;">'.__TITLE__.'</a><a href="mailto:'.__FORM_EMAIL__.'" style="color:black; text-decoration:none;margin-right:10px;">'.__FORM_EMAIL__.'</a><a href="tel:'.__TELEFON__.'" style="color:black; text-decoration:none;margin-left:10px;">'.__TELEFON__.'</a></div>
	    </div>
	</body>';
		
		
		
		// uložíme objednávku ********************************************************************************************
		
		
	        $data_kc = Db::queryAll('SELECT id_var, id_produkt, pocet FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
			if($data_kc)
			{
				$cena_celkem_s_dph = round($this->cena_celkem + $doprava_cena_vyber + $platba_cena_vyber - $kod_castka);
				$cena_celkem_bez_dph = round(($this->cena_celkem + $doprava_cena_vyber + $platba_cena_vyber - $kod_castka) / (__DPH__/100+1));
				
				
				if($_SESSION['slevovy_kod'])
				 {
				    // vypočítáme částku dle typu slevového kódu
				    if($_SESSION['slevovy_kod']['typ']==1)
				    {
					   // částka
					   $kod_castka = $_SESSION['slevovy_kod']['castka'];
					   $kod = $_SESSION['slevovy_kod']['kod'];
					   $kod_typ = 1;

					}
					elseif($_SESSION['slevovy_kod']['typ']==2)
					{
					   // procento
					   $kod_procento = $_SESSION['slevovy_kod']['castka'];
					   $kod = $_SESSION['slevovy_kod']['kod'].' ( sleva '.$_SESSION['slevovy_kod']['castka'].'%)';
					   
					    // musíme zjistit cenu za košík *****************************************************
					    $cena_celkem = $this->kosikCenaCelkem();
					    $kod_castka = round(($cena_celkem / 100 * intval($kod_procento)));
					    $kod_typ = 2;
					}
				}
				else
				{
					$kod = '';
					$kod_castka = 0;
					$kod_typ = 0;
				}
				
				// stavy  - karta
				if($karta==1)
				{
					$id_stav = 7;
					$card_text = 'nedokončený pokus o platbu kartou';
				}
				else
				{
					$id_stav = 1;
					$card_text = '';
				}
			
			
			
			if($karta_navrat == 0)
			{
				
						    // objednávka - ukládáme pouze v košíku
						    // úprava z 15.11.2021 - přidáváme příznak nebezpečný zákazník
						    
						    $hodnoceni_zakaznika = $this->hodnoceniZakaznika($id_zak);
						    
							$data_insert_o = array(
										    'id_stav' => $id_stav,
										    'cislo_obj' => $id_obj,
										    'id_zakaznik' => $id_zak,
										    'cenova_skupina' => __TYP_CENY__,
										    'skupina_slev' => __SLEVOVA_SKUPINA__,
										    'cena_celkem' => $cena_celkem_bez_dph,
										    'cena_celkem_s_dph' => $cena_celkem_s_dph,
										    'poznamka_zakaznik' => sanitize($_POST['poznamka']),
										    'hodnoceni_zakaznika' => intval($hodnoceni_zakaznika),
										    'doprava_id' => $_SESSION['doprava']['id'],
										    'doprava_nazev' => $_SESSION['doprava']['nazev'],
										    'platba_id' => $_SESSION['platba']['id'],
										    'platba_nazev' => $_SESSION['platba']['nazev'],
										    'id_zasilkovna' => intval($_SESSION['doprava']['id_zasilkovna']),
										    'id_ppl_parcel' => sanitize($_SESSION['doprava']['id_ppl_parcel']),
										    'id_dpd_pickup' => sanitize($_SESSION['doprava']['id_dpd_pickup']),
										    'psc_cp_na_postu' => intval($_SESSION['doprava']['psc_cp_na_postu']),
										    'psc_cp_balikovna' => intval($_SESSION['doprava']['psc_cp_balikovna']),
										    'vaha' => intval($this->kosikVahaCelkem()),
										    'card' => $card_text,
										    'slevovy_kod' => $kod,
										    'typ_slevoveho_kodu' => $kod_typ,
										    'castka_slevoveho_kodu' => $kod_castka,
										    'ip' => sanitize(getip()),
										    'referer' => sanitize($_SESSION['referer']),
										    'os' => sanitize(get_operating_system()),
										    'user_agent' => sanitize($_SERVER['HTTP_USER_AGENT']),
										    'souhlas_op' => 1,
										    'nesouhlas_heureka' => intval($_SESSION['reg_kosik']['nesouhlas_heureka']),
										    'hash_recenze' => StringUtils::randHash(20),
										    'datum' => time()
										     );
									
									// úprava z 22.12.2021 - pro toptrans
									$dop_tt = Db::queryRow('SELECT balikobot_shipper FROM doprava WHERE id=? ', array($_SESSION['doprava']['id']));	
									if($dop_tt['balikobot_shipper']=='toptrans')
									{
										$data_insert_o['typ_baliku_toptrans'] = 'BAL';
									}     
								     
							$query_insert_o = Db::insert('objednavky', $data_insert_o);
						   
						    foreach($data_kc as $row_kc)
						    {
								   $data_var_cena = Db::queryRow('SELECT V.'.$this->typ_ceny.' AS CENA, V.nazev_var, V.kat_cislo_var, V.sleva, V.sleva_datum_od, V.sleva_datum_do, V.ks_skladem,
								   P.id_dph, P.nazev, P.str, P.jednotka, D.dph, DOS.dostupnost 
								   FROM produkty_varianty V 
								   LEFT JOIN produkty P ON P.id=V.id_produkt
								   LEFT JOIN dph D ON D.id=P.id_dph
								   LEFT JOIN produkty_dostupnost DOS ON DOS.id=V.id_dostupnost
								   WHERE V.id=? ', array($row_kc['id_var']));
								   if($data_var_cena)
								   {
								     
										$ceny = $this->vypocetCeny($data_var_cena['CENA'],$data_var_cena['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
										
										// jednotlivé položky 	
										    
										    $data_insert_op = array(
										    'id_obj' => $query_insert_o,
										    'id_produkt' => $row_kc['id_produkt'],
										    'id_varianta' => $row_kc['id_var'],
										    'typ' => 0,
										    'polozka' => $data_var_cena['nazev'].' '.$data_var_cena['nazev_var'],
										    'kat_cislo' => $data_var_cena['kat_cislo_var'],
										    'pocet' => $row_kc['pocet'],
										    'cena_za_ks' => str_replace(',','.',$ceny['cena_bez_dph']),
										    'dph' => $data_var_cena['dph']
										     );
								     
											$query_insert_op = Db::insert('objednavky_polozky', $data_insert_op);
											
											// musíme odečíst počty ze skladu
											$query_pocet_update = Db::updateS('UPDATE produkty_varianty SET ks_skladem=ks_skladem-'.intval($row_kc['pocet']).' WHERE id= "'.intval($row_kc['id_var']).'"  ');
				
								   }
			
						   
						     }
						     
						     // doprava 
						     $data_insert_opd = array(
						    'id_obj' => $query_insert_o,
						    'id_produkt' => $_SESSION['doprava']['id'],
						    'id_varianta' => 0,
						    'typ' => 1,
						    'polozka' => $_SESSION['doprava']['nazev'],
						    'kat_cislo' => '',
						    'pocet' => 1,
						    'cena_za_ks' => str_replace(',','.',$doprava_cena_vyber_bez_dph),
						    'dph' => intval($_SESSION['doprava']['dph'])
						     );
				     
							$query_insert_opd = Db::insert('objednavky_polozky', $data_insert_opd);
							
							// platba
							$data_insert_opp = array(
						    'id_obj' => $query_insert_o,
						    'id_produkt' => $_SESSION['platba']['id'],
						    'id_varianta' => 0,
						    'typ' => 2,
						    'polozka' => $_SESSION['platba']['nazev'],
						    'kat_cislo' => '',
						    'pocet' => 1,
						    'cena_za_ks' => str_replace(',','.',$platba_cena_vyber_bez_dph),
						    'dph' => intval($_SESSION['platba']['dph'])
						     );
				     
							$query_insert_opp = Db::insert('objednavky_polozky', $data_insert_opp);
							
							
							// slevový kód také musí být uložen bez DPH
						    if($_SESSION['slevovy_kod'])
							{
							  
							  $data_insert_opsk = array(
							    'id_obj' => $query_insert_o,
							    'id_produkt' => 0,
							    'id_varianta' => 0,
							    'typ' => 3,
							    'polozka' => 'Sleva - slevový kód',
							    'kat_cislo' => $kod,
							    'pocet' => 1,
							    'cena_za_ks' => -(str_replace(',','.',($kod_castka / (__DPH__/100+1)))),
							    'dph' => __DPH__
							     );
					     
								$query_insert_opsk = Db::insert('objednavky_polozky', $data_insert_opsk);
							
							}
			 }
			     
			    if($karta==1 && $karta_navrat==0)
				{
				     // nic neodesíláme
				     $_SESSION['reg_kosik']['id_obj'] = $id_obj;
				     $_SESSION['reg_kosik']['id_zak'] = $id_zak;
				     $eml_odeslani = $id_obj;
				}
				elseif($karta==1 && $karta_navrat==1)
				{
					// úspěšná platba kartou - odešleme emailovou rekapitulaci
					$eml = New Email('html',false);
					$eml->nastavFrom(__EMAIL_FROM__);
					$eml->nastavBcc(__FORM_EMAIL__);
					$eml->nastavTo(sanitize($_SESSION['reg_kosik']['email']));
					$eml->nastavSubject('Objednávka č. '.$id_obj);
					$eml->nastavBody($obj_html);
					$eml_odeslani = $eml->odesliEmail();
					
				}
				else
				{
					// odešleme emailem **************************************************************************************
				    $eml = New Email('html',false);
					$eml->nastavFrom(__EMAIL_FROM__);
					$eml->nastavBcc(__FORM_EMAIL__);
					$eml->nastavTo(sanitize($_SESSION['reg_kosik']['email']));
					$eml->nastavSubject('Objednávka č. '.$id_obj);
					$eml->nastavBody($obj_html);
					$eml_odeslani = $eml->odesliEmail();
				
				}
			     
 

		     }	
		
		

		//$dbul = Db::unlockWrite('objednavky');
		//$dbul2 = Db::unlockWrite('objednavky_polozky');
		return $eml_odeslani;
		
	
	}
	
	
	
	public function hodnoceniZakaznika($id_zak)
	{
	   $hz = 0;
	   
	   if($id_zak)
	   {
	        $data_z = Db::queryRow('SELECT email, telefon FROM zakaznici WHERE id=?', array($id_zak));
			if($data_z)
			{ 
			        $data_z2 = Db::queryAll('SELECT id FROM zakaznici WHERE email=? OR telefon=? ', array($data_z['email'],$data_z['telefon']));
					if($data_z2)
					{
						$zak_arr = array();
						foreach ($data_z2 as $row_z2) 
						{
						   $zak_arr[] = $row_z2['id'];
						}
						
					}
					
					
					if(count($zak_arr)>0)
					{
						
						$data_o = Db::queryAffected('SELECT O.id FROM objednavky O WHERE O.id_zakaznik IN('.implode(',',$zak_arr).') AND O.hodnoceni_zakaznika=2', array());
						if($data_o > 0)
						{
							$hz = 2;
						}
					
					}
			}
	   }
	   
	   return $hz;
	   
	}
	
	
	public function googleElObchod()
	{
	  $ret = '';
	  
	  if(__GOOGLE_EL_OBCHOD__ && $this->id_obj)
	  {

		
		$data_obj = Db::queryRow('SELECT * FROM objednavky WHERE cislo_obj = ?', array($this->id_obj));
		$data_obj_p = Db::queryRow('SELECT sum(cena_za_ks) AS CENA_DOPRAVA_PLATBA FROM objednavky_polozky WHERE typ IN(1,2) AND id_obj = ?', array($data_obj['id'])); // součet dopravy a platby
		
		$ret .= '<script type="text/plain" data-cookiecategory="analytics">

			gtag(\'event\', \'purchase\', {
			  "transaction_id": "'.$this->id_obj.'",
			  "affiliation": "'.__DOMENA__.'",
			  "value": '.str_replace(',','.',$data_obj['cena_celkem_s_dph']).',
			  "currency": "CZK",
			  "tax": '.intval(__DPH__).',
			  "shipping": '.str_replace(',','.',round($data_obj_p['CENA_DOPRAVA_PLATBA'] * (__DPH__ / 100 + 1) )).',
			  "items": [
			    
			';

			 // smycka s produkty z objednavky
			   
			    $data_obj_s = Db::queryAll('SELECT polozka, kat_cislo, pocet, cena_za_ks, dph, id_produkt FROM objednavky_polozky WHERE id_obj=? AND typ=? ', array($data_obj['id'],0));
				if($data_obj_s !== false ) 
				{
					   foreach ($data_obj_s as $row_obj_s) 
						{
							
							// kategorie
							$data_p = Db::queryRow('SELECT id_kat_arr FROM produkty WHERE id = ?', array($row_obj_s['id_produkt']));
							if($data_p['id_kat_arr'])
							{
							   $id_kat_arr = unserialize($data_p['id_kat_arr']);
							   $id_kat = $id_kat_arr[0];
							   $data_k = Db::queryRow('SELECT nazev FROM kategorie WHERE id = ?', array($id_kat));
							}
							
							    
							    $ret .= '{
							      "item_id": "'.$row_obj_s['kat_cislo'].'",
							      "item_name": "'.$row_obj_s['polozka'].'",
							      "list_name": "",
							      "item_brand": "",
							      "item_category": "'.$data_k['nazev'].'",
							      "item_variant": "",
							      "list_position": 0,
							      "quantity": '.$row_obj_s['pocet'].',
							      "price": \''.round($row_obj_s['cena_za_ks'] * ($row_obj_s['dph'] / 100 + 1) ).'\'
							    },';
			    
			    
						}
				}
				   

			
			$ret .= '  ]
			}); ';
			
			$ret .= '</script>';	

	  }
	  
	  
	  
	  return $ret;
	}
	
	
	
	public function sklikKonverze()
	{
		$ret = '';
		
		if(__SEZNAM_SKLIK_KONVERZE__ && $this->id_obj)
	    {
			$data_obj = Db::queryRow('SELECT * FROM objednavky WHERE cislo_obj = ?', array($this->id_obj));
			$data_obj_p = Db::queryRow('SELECT sum(cena_za_ks * pocet * (dph/100+1)) AS CENA_ZBOZI FROM objednavky_polozky WHERE typ=0 AND id_obj = ?', array($data_obj['id']));
			
			$ret .= '<!-- Měřicí kód Sklik.cz -->
						<script type="text/plain" data-cookiecategory="marketing" src="https://c.seznam.cz/js/rc.js"></script>
						<script type="text/plain" data-cookiecategory="marketing">
						  window.sznIVA.IS.updateIdentities({
						    eid: null
						  });
						
						  var conversionConf = {
						    id: "'.__SEZNAM_SKLIK_KONVERZE__.'",
						    value: "'.round($data_obj_p['CENA_ZBOZI']).'",
						    orderId: "'.$this->id_obj.'",
						    zboziType: null,
						    zboziId: "'.__SEZNAM_ZBOZI_KONVERZE__.'",
						    consent: 1
						  };
						  window.rc.conversionHit(conversionConf);
						</script>';
		}
		
		
		
		return $ret;
	}
	
	
	
	public function zboziKonverze()
	{
      
      $ret = '';
      
      if(__SEZNAM_ZBOZI_KONVERZE__ && $this->id_obj)
	    {
			$data_obj = Db::queryRow('SELECT * FROM objednavky WHERE cislo_obj = ?', array($this->id_obj));
			
			$ret .= '<script type="text/plain" data-cookiecategory="marketing" src="https://c.seznam.cz/js/rc.js"></script>
					<script type="text/plain" data-cookiecategory="marketing">
					  window.sznIVA.IS.updateIdentities({
					    eid: null
					  });
					
					  var conversionConf = {
					    value: "'.round($data_obj['cena_celkem_s_dph']).'",
					    orderId: "'.$this->id_obj.'",
					    zboziType: null,
					    zboziId: "'.__SEZNAM_ZBOZI_KONVERZE__.'",
					    consent: 1
					  };
					  window.rc.conversionHit(conversionConf);
					</script>';
					
		}
      
      
      
      return $ret;
    
    }
	
	
	
	public function heurekaKonverze()
	{
	    
	    $ret = '';
	    
	    if(__HEUREKA_KEY__ && $this->id_obj)
	    {
			$heureka_pr_arr = array();
			
			$data_obj = Db::queryRow('SELECT id FROM objednavky WHERE cislo_obj = ?', array($this->id_obj));
			$data_obj_p = Db::queryAll('SELECT * FROM objednavky_polozky WHERE typ=0 AND id_obj = ?', array($data_obj['id']));
			foreach($data_obj_p as $row_obj_p)
			{
				$heureka_pr_arr[] = '_hrq.push([\'addProduct\', \''.$row_obj_p['polozka'].'\', \''.round($row_obj_p['cena_za_ks'] * ($row_obj_p['dph']/100+1)).'\', \''.$row_obj_p['pocet'].'\', \''.$row_obj_p['id_produkt'].'_'.$row_obj_p['id_varianta'].'\']);';
			}
			
			
			
			$ret .= '<script type="text/plain" data-cookiecategory="marketing">
					var _hrq = _hrq || [];
					    _hrq.push([\'setKey\', \''.__HEUREKA_KEY__.'\']);
					    _hrq.push([\'setOrderId\', \''.$this->id_obj.'\']);';
					    
					    // produkty
					    foreach($heureka_pr_arr as $heu_k=>$heu_v)
					    {
						 $ret .= $heu_v."\n";
						}
					    
					    $ret .= '_hrq.push([\'trackOrder\']);
					
					(function() {
					    var ho = document.createElement(\'script\'); ho.type = \'text/javascript\'; ho.async = true;
					    ho.src = \'https://im9.cz/js/ext/1-roi-async.js\';
					    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ho, s);
					})();
					</script>';

		}
	    
	    
	    return $ret;
	
	}
	
	
	
	public function googleZakaznickeRecenze()
	{
	    
	    $ret = '';
	    
	    if(__GOOGLE_ZAK_RECENCE_MERCHANT_ID__ && __GOOGLE_ZAK_RECENZE_DORUCENI__ && $this->id_obj)
	    {
			$g_pr_arr = array();
			
			$data_obj = Db::queryRow('SELECT id FROM objednavky WHERE cislo_obj = ?', array($this->id_obj));
			
			$data_obj_p = Db::queryAll('SELECT OP.id_varianta, PV.ean_var 
			FROM objednavky_polozky OP 
			LEFT JOIN produkty_varianty PV ON PV.id=OP.id_varianta
			WHERE OP.typ=0 AND OP.id_obj = ?', array($data_obj['id']));
			foreach($data_obj_p as $row_obj_p)
			{
				$g_pr_arr[] = '{"gtin":"'.$row_obj_p['ean_var'].'"},';
			}
			

			$predpokladane_datum_doruceni = (time() + (3600*24*__GOOGLE_ZAK_RECENZE_DORUCENI__)); // za 10 dnů
			$produkty_google_recenze = rtrim($g_pr_arr, ","); // odstraníme poslední znak
			$ret .= '<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>';
			$ret .= '<script>
					  window.renderOptIn = function() {
					    window.gapi.load("surveyoptin", function() {
					      window.gapi.surveyoptin.render(
					        {
					          // REQUIRED FIELDS
					          "merchant_id": '.__GOOGLE_ZAK_RECENCE_MERCHANT_ID__.',
					          "order_id": "'.$this->id_obj.'",
					          "email": "'.$_SESSION['reg_kosik']['email'].'",
					          "delivery_country": "CZ",
					          "estimated_delivery_date": "'.date('Y-m-d',$predpokladane_datum_doruceni).'",
					
					          ';
					          
					          // ve smyčce produkty, které mají vyplněn EAN kód
					          
					          echo '"products": ['.$produkty_google_recenze.']
					        });
					    });
					  }
				</script>';	


		}
	    
	    
	    return $ret;
	
	}
	
	
	
	public function dopravaSpecial()
	{
		// metoda zjistí jestli je v košíku produkt s atributem speciální doprava
	    
	    $ret = false;
	    $data_kc = Db::queryAll('SELECT id_produkt FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
		if($data_kc)
		{
			   
		   foreach($data_kc as $row_kc)
		   {  
		         $data_d = Db::queryAffected('SELECT id FROM produkty WHERE id=? AND specialni_doprava=? ', array($row_kc['id_produkt'],1));
			     if($data_d > 0)
				 {
				    $ret = true;
				 }
		   }
		   
	    }
	    
	    return $ret;
	}
	
	public function dopravaSpecial2()
	{
		// metoda zjistí jestli je v košíku produkt s atributem speciální doprava 2
	    
	    $ret = false;
	    $data_kc = Db::queryAll('SELECT id_produkt FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
		if($data_kc)
		{
			   
		   foreach($data_kc as $row_kc)
		   {  
		         $data_d = Db::queryAffected('SELECT id FROM produkty WHERE id=? AND specialni_doprava2=? ', array($row_kc['id_produkt'],1));
			     if($data_d > 0)
				 {
				    $ret = true;
				 }
		   }
		   
	    }
	    
	    return $ret;
	}
	
	
	public function dopravaZdarma()
	{
	    // metoda zjistí jestli je v košíku produkt s atributem doprava zdarma
	    
	    $ret = false;
	    $data_kc = Db::queryAll('SELECT id_produkt FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
		if($data_kc)
		{
			   
		   foreach($data_kc as $row_kc)
		   {
		         $data_d = Db::queryAffected('SELECT id FROM produkty WHERE id=? AND doprava_zdarma=? ', array($row_kc['id_produkt'],1));
			     if($data_d > 0)
				 {
				    $ret = true;
				 }
		   }
		   
	    }
	    
	    return $ret;
	   
	   
	}
	
	
	
	public function dopravaZdarmaVse()
	{
	    // metoda zjistí jestli všechny produkty v košíku mají příznak doprava_zdarma
	    

	    $data_kc = Db::queryAll('SELECT id_produkt FROM kosik WHERE 1 '.$this->sql_kosik.' ', array());
		if($data_kc)
		{
			$pocet_k = count($data_kc);
			$pocet_p = 0;
		   	   
		    foreach($data_kc as $row_kc)
		    {
		         $data_d = Db::queryAffected('SELECT id FROM produkty WHERE id=? AND doprava_zdarma=? ', array($row_kc['id_produkt'],1));
			     if($data_d > 0)
				 {
				    $pocet_p++;
				 }
		    }
		    
		    if($pocet_k == $pocet_p)
		    {
			   return true;
			}
			else
			{
			   return false;
			}
		   
	    }
	    

	   
	   
	}
	
	
	
	
	public function vypocetCeny($cena,$dph,$sleva,$sleva_datum_od,$sleva_datum_do)
	{ 
	    // u tohoto projektu se zadávají v adminu ceny s DPH, není tedy nutné dopočítávat
	    // cenu bez DPH potřebujeme pro admin modul objednávek a musíme ji vracet zaokrouhlenou na 2 des. místa
	    // změna 16.2.2023 - v adminu se nastavuje jestli jsou ceny s nebo bez DPH
	     
	    if(__CENY_ADM__==1)
		{
			// ceny jsou bez DPH

			   if($sleva>0 && ($sleva_datum_od <= time() && $sleva_datum_do >= time()  )) 
			  	{
			  	  $cena_s_dph = round((($cena) - ($cena/100*$sleva)) * ($dph / 100 + 1),2);
			  	  $cena_puvodni_s_dph =  round($cena * ($dph / 100 + 1),2);
			  	  $sleva_ret = intval($sleva);
			  	  $cena_bez_dph = round((($cena) - ($cena/100*$sleva)),2);
			  	}
			  	else
			  	{
			  	  $sleva_ret = 0;
			  	  if(__SLEVOVA_SKUPINA__)
			  	  {
			  	    $cena_s_dph = round(($cena - ($cena/100 * __SLEVOVA_SKUPINA__)) * ($dph / 100 + 1),2);
			  	    $cena_bez_dph = round(($cena - ($cena/100 * __SLEVOVA_SKUPINA__)),2);
			  	  }
			  	  else
			  	  {
			  	    $cena_s_dph = round($cena * ($dph / 100 + 1),2);
			  	    $cena_bez_dph = round($cena,2);
			  	  }
			  	  
			  	  $cena_puvodni_s_dph =  round($cena * ($dph / 100 + 1),2);
			  	  
			  	}
		
			  	
		  
		}
		else
		{
			    if($sleva>0 && ($sleva_datum_od <= time() && $sleva_datum_do >= time()  )) 
			  	{
			  	  $cena_s_dph = round(($cena) - ($cena/100*$sleva));
			  	  $cena_puvodni_s_dph =  round($cena);
			  	  $sleva_ret = intval($sleva);
			  	  $cena_bez_dph = round((($cena) - ($cena/100*$sleva)) / ($dph / 100 + 1),2);
			  	}
			  	else
			  	{
			  	  $sleva_ret = 0;
			  	  if(__SLEVOVA_SKUPINA__)
			  	  {
			  	    $cena_s_dph = round(($cena) - ($cena/100 * __SLEVOVA_SKUPINA__));
			  	    $cena_bez_dph = round((($cena) - ($cena/100 * __SLEVOVA_SKUPINA__)) / ($dph / 100 + 1),2);
			  	  }
			  	  else
			  	  {
			  	    $cena_s_dph = round($cena);
			  	    $cena_bez_dph = round(($cena_s_dph / ($dph / 100 + 1)),2);
			  	  }
			  	  
			  	  $cena_puvodni_s_dph =  round($cena);
			  	  
			  	}
	
		}
	    

	  	
		// ceny vracíme v poli
		// úprava 16.8.2021 - vše zaokrouhlíme na celé částku
	  	$ret = array();
	  	$ret['cena_s_dph'] = round($cena_s_dph);
	  	$ret['cena_bez_dph'] = round($cena_bez_dph,2);
	  	$ret['cena_puvodni_s_dph'] = round($cena_puvodni_s_dph);
	  	$ret['sleva'] = $sleva_ret;
	  	$ret['dph'] = $dph;
	  	
	  	return $ret;
	
	}
	
	public function prepocetDopravaPlatba()
	{
	
	    $cena_celkem = round($this->kosikCenaCelkem());
	    
	    if($_SESSION['doprava']['id'])
	    {
			
			
			if(__DOPRAVA_ZDARMA_TYP__==1 && $this->dopravaZdarmaVse())
			{
				$_SESSION['doprava']['cena'] = 0;
			}
			elseif($this->dopravaZdarma())
			{
				$_SESSION['doprava']['cena'] = 0;
			}
			
			if($cena_celkem > __POSTOVNE_ZDARMA__)
			{
				$_SESSION['doprava']['cena'] = 0;
			}
			
		}
		
		if($_SESSION['platba']['id'])
		{
			if(__DOPRAVA_ZDARMA_TYP_2__==2)
			{
				  // zdarma i platba při splnění podmínek
				  
				  if(__DOPRAVA_ZDARMA_TYP__==1 && $this->dopravaZdarmaVse())
                  {
				    // všechny produkt v košíku musí mít příznak doprava zdarma
				    $_SESSION['platba']['cena'] = 0;
				  }
				  elseif($this->dopravaZdarma())
				  {
				    // stačí jeden produkt s příznakem doprava zdarma
				    $_SESSION['platba']['cena'] = 0;
				  }
				  
				  if($cena_celkem > __POSTOVNE_ZDARMA__)
				  {
				     $_SESSION['platba']['cena'] = 0;
				  }
			}
		
		}
	
	}

		
		
		
    
    
    public function okno($id_produkt,$id_varianta,$pocet)
    {
	
		if($id_produkt && $id_varianta && $pocet)
		{
				  $id_pr = intval($id_produkt);
				  $id_var = intval($id_varianta);
				  $pocet_p = intval($pocet);
				  
				  $data_p = Db::queryRow('SELECT P.nazev, V.nazev_var, V.foto_var, V.'.$this->typ_ceny.' AS CENA, V.sleva, V.sleva_datum_od, V.sleva_datum_do, P.id_dph, D.dph 
				  FROM produkty P 
				  LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
				  LEFT JOIN dph D ON D.id=P.id_dph
				  WHERE P.id=? AND V.id=? AND P.aktivni=? AND V.aktivni_var=? ', array($id_pr,$id_var,1,1));
				  if($data_p)
				  {
					  if($data_p['foto_var'] && $data_p['foto_var']!='bily.png')
					  {
					     $foto = $data_p['foto_var'];
					  }
					  else
					  {
					     // chybí foto varianty - použijeme hlavní foto
					     $data_pf = Db::queryRow('SELECT foto FROM produkty_foto WHERE id_produkt=? ORDER BY typ DESC', array($id_pr));
						  if($data_pf)
						  {
						    
						    $foto = $data_pf['foto'];
						  
						  }
						  
					  }
					  
					  $ceny = $this->vypocetCeny($data_p['CENA'],$data_p['dph'],$data_p['sleva'],$data_p['sleva_datum_od'],$data_p['sleva_datum_do']);
					  
					  $ret = '<div class="modal micromodal-slide" id="modal-cart">
						      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
						        <div class="modal__container --cart" role="dialog" aria-labelledby="modal-cart-title">
						          <div class="modal__image"><img src="/fotky/produkty/stredni/'.$foto.'" alt="'.$data_p['nazev'].'"></div>
						          <div class="modal__content" id="modal-cart-content">
						            <div class="modal__header">
						              <h2 class="modal__title" id="modal-cart-title">Přidáno do košíku</h2>
						              <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
						            </div>
						            <p class="modal__cart-title">'.$pocet.'x '.$data_p['nazev'].'</p>
						            <span class="modal__cart-variant">Varianta: '.$data_p['nazev_var'].'</span>
						            <span class="modal__cart-price">'.$ceny['cena_s_dph'].' '.__MENA__.'</span>
						            <span class="modal__cart-vat">'.$ceny['cena_bez_dph'].' '.__MENA__.' bez DPH</span>
						             <div class="modal__cart-btns"><span class="btn --cart-dark" aria-label="Close modal" data-micromodal-close>Zpět do obchodu</span>
						            <a class="btn --cart" href="/kosik?krok=1">Přejít do košíku <img src="/img/icons/cheveron-white.svg" alt="Pokračovat"></a>
						       
						            </div>
						          </div>
						        </div>
						      </div>
						    </div>
						    <script>
						      document.addEventListener("DOMContentLoaded", () => {
						          MicroModal.show(\'modal-cart\',{
						              openClass: \'is-open\', 
						              disableScroll: true, 
						              disableFocus: true, 
						              awaitOpenAnimation: true,
						              awaitCloseAnimation: true, 
						              debugMode: false 
						          }); 
						      });
						    </script>';
	    
	              }
	              
		}
		
		return $ret;
	
	}
	


}
