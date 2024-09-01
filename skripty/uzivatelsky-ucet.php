<?php
// uživatelský účet
$obsah = '';
$options_pass = ['cost' => 12,];

if($_SESSION['uzivatel'])
{
	
	$obsah = '<section class="page-heading --inline">
          <div class="container">
            <h2>Přihlášen jako: '.$_SESSION['uzivatel']['jmeno'].' '.$_SESSION['uzivatel']['prijmeni'].'</h2>
            <div class="logout-wrap"> <a class="btn" href="/odhlaseni" data-no-instant>Odhlásit se</a></div>
          </div>
        </section>';
        
        
	
	if($_POST['r_u'])
	{
		
			if($_SESSION['uzivatel']['id'])
			{		

					$data_update = array(
					'jmeno' => sanitize($_POST['jmeno']),
					'prijmeni' => sanitize($_POST['prijmeni']),
					'uz_jmeno' => sanitize($_POST['email']),
					'email' => sanitize($_POST['email']),
					'telefon' => sanitize(str_replace(' ','',$_POST['telefon'])),
					
					'dodaci_nazev' => sanitize($_POST['dodaci_nazev']),
					'dodaci_ulice' => sanitize($_POST['dodaci_ulice']),
					'dodaci_cislo' => sanitize($_POST['dodaci_cislo']),
					'dodaci_obec' => sanitize($_POST['dodaci_obec']),
					'dodaci_psc' => sanitize($_POST['dodaci_psc']),
					'dodaci_id_stat' => intval($_POST['dodaci_id_stat']),
					
					'fakturacni_jmeno' => sanitize($_POST['fakturacni_jmeno']),
					'fakturacni_firma' => sanitize($_POST['fakturacni_firma']),
					'fakturacni_ulice' => sanitize($_POST['fakturacni_ulice']),
					'fakturacni_cislo' => sanitize($_POST['fakturacni_cislo']),
					'fakturacni_obec' => sanitize($_POST['fakturacni_obec']),
					'fakturacni_psc' => sanitize($_POST['fakturacni_psc']),
					'fakturacni_id_stat' => intval($_POST['fakturacni_id_stat']),
					'ic' => sanitize($_POST['ic']),
					'dic' =>sanitize( $_POST['dic']),

					'nl' => intval($_POST['nl']));
					
					 // změnil heslo
				    if($_POST['heslo'])
				    {
						$data_update['heslo'] = password_hash($_POST['heslo'], PASSWORD_BCRYPT, $options_pass);
					}
					
					$where_update = array('id' => intval($_SESSION['uzivatel']['id']));
					$query_update = Db::update('zakaznici', $data_update, $where_update);

					
					$obsah .= '<div class="alert-success">Uloženo!<br>Záznam byl v pořádku změněn.</div>';
					  
				  	     
			
			}
			else
			{
				$obsah .= '<div class="alert-warning">Chyba!<br>Chybí ID záznamu<br></div>';
			}
			
	}
	else
	{
		$data = Db::queryRow('SELECT * FROM zakaznici WHERE id = ?', array(intval($_SESSION['uzivatel']['id'])));
		if($data)
		{
				$obsah .= '<section class="page-content">
	          <div class="container"> 
	            <div class="row"> 
	              <div class="col-12 col-lg-4">
	                <h2>Úprava osobních údajů</h2>
	                <form method="post" id="register-form">
	                  <div class="form-group">    
	                    <label for="register-name">Jméno</label>
	                    <input type="text" id="register-name" name="jmeno" required minlength="2" maxlength="45" value="'.$data['jmeno'].'">
	                  </div>
	                  <div class="form-group">    
	                    <label for="register-surname">Příjmení</label>
	                    <input type="text" id="register-surname" name="prijmeni" required minlength="2" maxlength="45" value="'.$data['prijmeni'].'">
	                  </div>
	                  <div class="form-group">    
	                    <label for="register-email">Email</label><input type="email" id="register-email" name="email" required minlength="6" maxlength="255"  value="'.$data['email'].'" pattern="([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})">
	                  </div>
	                  <div class="form-group">    
	                    <label for="register-tel">Telefon </label><input type="tel" id="register-tel" name="telefon" required pattern="\d{1,15}" value="'.$data['telefon'].'">
	                  </div>
	                  <div class="form-group">    
	                    <label for="register-password">Heslo <small>(pokud heslo nezměníte tak zůstane původní)</small></label>
	                    <input type="password" id="register-password" name="heslo" >
	                    <span class="show-password" id="showLoginPassword" aria-label="Zobrazit heslo"></span> 
	                  </div>
	                  <div class="form-subtitle">Dodací údaje</div>
	                  
	                   <div class="form-group">    
	                        <label for="register-da-name">Jméno a příjmení </label>
	                        <input type="text" id="register-da-name" name="dodaci_nazev" value="'.$data['dodaci_nazev'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-da-street">Ulice</label>
	                        <input type="text" id="register-da-street" name="dodaci_ulice" value="'.$data['dodaci_ulice'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-da-street">Číslo popisné</label>
	                        <input type="text" id="register-da-street" name="dodaci_cislo" value="'.$data['dodaci_cislo'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-da-city">Město/obec</label>
	                        <input type="text" id="register-da-city" name="dodaci_obec" value="'.$data['dodaci_obec'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-da-psc">PSČ</label>
	                        <input type="text" id="register-da-psc" name="dodaci_psc" value="'.$data['dodaci_psc'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="dodaci_id_stat">Stát</label>
	                        <select name="dodaci_id_stat" id="register-da-state">';
	                          
	                           foreach($_SESSION['staty_arr'] as $staty_k=>$staty_v)
			                    {
									 $obsah .= '<option value="'.$staty_k.'" ';
									 if($data['dodaci_id_stat']==$staty_k){$obsah .= ' selected ';}
									 $obsah .= '>'.$staty_v.'</option>';
								}
	                      
	                      
	                    $obsah .= '
	                        </select>
	                      </div>
	                    
	                  <div class="form-spacer"> </div>
	                 
	                  <div class="form-group --checkbox">
	                    <label for="register-fa-address" id="faToggle">Vyplnit fakturační adresu</label>
	                    <input type="checkbox" name="register-fa-address" id="register-fa-address">
	                    <div class="form-collapse">
	                      <div class="form-group">    
	                        <label for="register-fa-name">Jméno a příjmení (nepovinné)</label>
	                        <input type="text" id="register-fa-name" name="fakturacni_jmeno" value="'.$data['fakturacni_jmeno'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-company">Název firmy</label>
	                        <input type="text" id="register-fa-company" name="fakturacni_firma" value="'.$data['fakturacni_firma'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-ico">IČO</label>
	                        <input type="text" id="register-fa-ico" name="ic" value="'.$data['ic'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-dic">DIČ (nepovinné)</label>
	                        <input type="text" id="register-fa-dic" name="dic" value="'.$data['dic'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-street">Ulice</label>
	                        <input type="text" id="register-fa-street" name="fakturacni_ulice" value="'.$data['fakturacni_ulice'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-street">Číslo popisné</label>
	                        <input type="text" id="register-fa-street" name="fakturacni_cislo" value="'.$data['fakturacni_cislo'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-city">Město/obec</label>
	                        <input type="text" id="register-fa-city" name="fakturacni_obec" value="'.$data['fakturacni_obec'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-psc">PSČ</label>
	                        <input type="text" id="register-fa-psc" name="fakturacni_psc" value="'.$data['fakturacni_psc'].'">
	                      </div>
	                      <div class="form-group">    
	                        <label for="register-fa-state">Stát</label>
	                        <select name="fakturacni_id_stat" id="register-fa-state">';
	                          
	                           foreach($_SESSION['staty_arr'] as $staty_k=>$staty_v)
			                    {
									 $obsah .= '<option value="'.$staty_k.'" ';
									 if($data['fakturacni_id_stat']==$staty_k){$obsah .= ' selected ';}
									 $obsah .= '>'.$staty_v.'</option>';
								}
	                      
	                      
	                    $obsah .= '
	                        </select>
	                      </div>
	                    </div>
	                  </div>
	                 
	                  <div class="form-spacer"> </div>
	                  <div class="form-group --checkbox">
	                    <label for="register-agree1">Chci odebírat newsletter a souhlasím se <a href="/ochrana-osobnich-udaju">zpracováním osobních údajů</a> za účelem zasílání informací o novinkách a slevách.</label>
	                    <input type="checkbox" name="nl" value="1" id="register-agree1" ';
	                    if($data['nl']==1){$obsah .= ' checked ';}
	                    $obsah .= '>
	                  </div>
	                   
	                  <div class="form-group"> 
	                    <input type="hidden" name="r_u" value="'.sha1(time()).'">
	                    <button class="btn --fullWidth" name="register-submit" id="register-submit">Aktualizovat údaje <img src="/img/icons/cheveron-white.svg"></button>
	                  </div>
	                </form>
	                
	              <div class="delete-account">
                  <h2>Smazat účet</h2>
                  <button class="btn --fullWidth" data-micromodal-trigger="modal-delete" data-no-instant>Smazat účet</button>
                  
                  <div class="modal micromodal-slide" id="modal-delete">
                    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
                      <div class="modal__container" role="dialog" aria-labelledby="modal-delete-title">
                        <div class="modal__content" id="modal-delete-content">
                          <div class="modal__header">
                            <h2 class="modal__title" id="modal-delete-title">Opravdu si přejete smazat svůj účet?</h2>
                            <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                          </div>
                          <form method="post" action="/smazat-ucet" data-no-instant> 
                            <button class="btn --fullWidth" data-micromodal-trigger="modal-delete" data-no-instant>Smazat účet</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

	                
	                
	                
	              </div>
	              <div class="col-12 col-lg-1"></div>
	              <div class="col-12 col-lg-7">
	                <h2>Historie objednávek</h2>
	                <table class="order-history"> 
	                  <thead> 
	                    <th>Číslo nákupu</th>
	                    <th>Datum </th>
	                    <th>Cena </th>
	                    <th colspan="2">Stav objednávky</th>
	                  </thead><tbody>';
	                  
	                  $data_o = Db::queryAll('SELECT O.*, OS.nazev 
	                  FROM objednavky O 
	                  LEFT JOIN objednavky_stavy OS ON OS.id=O.id_stav
	                  WHERE O.id_zakaznik=? ORDER BY O.id DESC ', array(intval($_SESSION['uzivatel']['id'])));
	                  if($data_o)
					  {   
			
						  foreach($data_o as $row_o)
						  {
						     $obsah .= '
			                    <tr class="order-history__item" id="detail-'.$row_o['id'].'"> 
			                      <td>'.$row_o['cislo_obj'].'</td>
			                      <td>'.date('d.m.Y',$row_o['datum']).'</td>
			                      <td>'.$row_o['cena_celkem_s_dph'].' '.__MENA__.'</td>
			                      <td class="--finished">'.$row_o['nazev'].'</td>
			                      <td><a href="#detail-'.$row_o['id'].'">Detail</a></td>
			                    </tr>';
			                    
			                      $data_od = Db::queryAll('SELECT * FROM objednavky_polozky WHERE id_obj=? ORDER BY id ASC ', array(intval($row_o['id'])));
				                  if($data_od)
								  {   
									   $obsah .= '<tr class="order-history__item-detail">
									   <td colspan="2">';
									  
									  foreach($data_od as $row_od)
									  {
										  $obsah .= '<span>'.$row_od['polozka'].'</span>';
									  }
									  
									  $obsah .= '</td>
			                          <td>';
			                          
			                          foreach($data_od as $row_od)
									  {
										  $obsah .= '<span>'.$row_od['pocet'].' ks</span>';
									  }
									  
									  $obsah .= '</td>
			                          <td>';
			                          
			                          foreach($data_od as $row_od)
									  {
										  $obsah .= '<span>'.round($row_od['cena_za_ks'] * ($row_od['dph'] / 100 + 1)).' '.__MENA__.'</span>';
									  }
									  
									  $obsah .= '</td>
			                          <td>';
			                          
			                          
			                          foreach($data_od as $row_od)
									  {
										  $obsah .= '<span>'.round(round($row_od['cena_za_ks'] * ($row_od['dph'] / 100 + 1)) * $row_od['pocet']).' '.__MENA__.'</span>';
									  }
									  
									  $obsah .= '</td>
			                          </tr>';
									  

							      }
							      
							      		
			                     
			                 
						  }
					  }
					  
					  $obsah .= '</tbody>';
				                  

	                  
	                $obsah .= '</table>
	              </div>
	            </div>
	          </div>
	        </section>';
	        
		}
		else
		{
			$obsah .= '<div class="alert-warning">Chyba!<br>Chybí ID záznamu<br></div>';
		}
				
		
        
        
	}
	
	




}
else
{
   $obsah .= 'Nejste přihlášeni. Pro zobrazení této stránky se přihlaste vpravo nahoře.';
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
$sablonka->pridejDoSablonky('{nadpis}','Uživatelský účet','html');
$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');


// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('txt'); 

 
?>
