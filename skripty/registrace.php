<?php
// registrace
$obsah = '';


		
			$obsah .= '<div class="page --narrow" id="obal_p">
          <section class="page-heading">
            <h1>Registrace</h1>
            <p id="infotext">Po odeslání registrace Vám bude neprodleně doručen na e-mailovou adresu uvedenou v registračním formuláři aktivační e-mail.</p>
          </section>
          <div id="obal_form">
          <form method="post" id="register-form">
	                  <div class="form-group">    
	                    <label for="jmeno">Jméno</label>
	                    <input type="text" id="jmeno" name="jmeno" required minlength="2" maxlength="45" >
	                  </div>
	                  <div class="form-group">    
	                    <label for="prijmeni">Příjmení</label>
	                    <input type="text" id="prijmeni" name="prijmeni" required minlength="2" maxlength="45" >
	                  </div>
	                  <div class="form-group">    
	                    <label for="email">Email <span id="kontrola_email" style="color: #ffffff;"></span></label>
	                    <input type="email" id="email" name="email" required minlength="6" maxlength="255" autocomplete="new-password"  pattern="([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})"> ';
	                    
	                    // pomocí jquery na keyup zjišťujeme ajaxem jestli není zadaný email v databázi pro bez_registrace=0 AND aktivni=1

	                    
	                    $obsah .='
	                  </div>
	                  <div class="form-group">    
	                    <label for="telefon">Telefon </label><input type="tel" id="telefon" name="telefon" required pattern="^(?:0|\(?\+42\)?\s?|0042\s?)[0-9][. \s]?[0-9]{3}[. \s]?[0-9]{3}[. \s]?[0-9]{3}">
	                  </div>
	                  <div class="form-group">    
	                    <label for="heslo">Heslo</label>
	                    <input type="password" id="heslo" name="heslo" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d\w\W]{8,}$">
	                    <span class="show-password" id="showRegisterPassword" aria-label="Zobrazit heslo"></span> 
	                    <span class="label-subtext">(Minimum 8 znaků, musí obsahovat velké a malé písmena a alespoň 1 číslo)</span>
	                  </div>
	                  
	                  <div class="form-subtitle">Dodací údaje</div>
	                  <div class="form-group">    
	                        <label for="dodaci_nazev">Jméno a příjmení </label>
	                        <input type="text" id="dodaci_nazev" name="dodaci_nazev" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="dodaci_ulice">Ulice</label>
	                        <input type="text" id="dodaci_ulice" name="dodaci_ulice" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="dodaci_cislo">Číslo popisné</label>
	                        <input type="text" id="dodaci_cislo" name="dodaci_cislo" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="dodaci_obec">Město/obec</label>
	                        <input type="text" id="dodaci_obec" name="dodaci_obec" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="dodaci_psc">PSČ</label>
	                        <input type="text" id="dodaci_psc" name="dodaci_psc" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="dodaci_id_stat">Stát</label>
	                        <select name="dodaci_id_stat" id="dodaci_id_stat">';
	                          
	                           foreach($_SESSION['staty_arr'] as $staty_k=>$staty_v)
			                    {
									 $obsah .= '<option value="'.$staty_k.'" ';
									 $obsah .= '>'.$staty_v.'</option>';
								}
	                      
	                      
	                    $obsah .= '
	                        </select>
	                      </div>
	                  
	                  
	                  
	                    
	                  <div class="form-spacer"> </div>
	                 
	                  <div class="form-group --checkbox">
                      <label for="cart-fa-address" id="faToggle">Vyplnit fakturační adresu <span>(pokud je rozdílná od dodací adresy nebo nakupuji na firmu)</span></label>
                      <input type="checkbox" name="cart-fa-address" id="cart-fa-address" >
                      <div class="form-collapse">
                      
                      
						<div class="form-group">    
                        <label for="fakturacni_jmeno">Jméno a příjmení </label>
                        <input type="text" id="fakturacni_jmeno" name="fakturacni_jmeno" value="'.$fakturacni_jmeno.'" >
                        </div>
				                      
				                      
                        <div class="form-group --checkbox">
	                      <label for="cart-fa-company" id="faComToggle">Jsme firma a potřebujeme uvést také obchodní informace</label>
	                      <input type="checkbox" name="cart-fa-address" id="cart-fa-company">
	                      <div class="form-collapse">
	                        <div class="form-group">    
	                          <label for="fakturacni_jmeno">Název firmy</label>
	                          <input type="text" id="fakturacni_firma" name="fakturacni_firma"  >
	                        </div>
	                        <div class="form-group">    
	                          <label for="cart-fa-ico">IČO</label>
	                          <input type="text" id="ic" name="ic" >
	                        </div>
	                        <div class="form-group">    
	                          <label for="dic">DIČ</label>
	                          <input type="text" id="dic" name="dic" >
	                        </div>
	                      </div>
	                    </div>
	                    
	                      <div class="form-group">    
	                        <label for="fakturacni_ulice">Ulice</label>
	                        <input type="text" id="fakturacni_ulice" name="fakturacni_ulice" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="fakturacni_cislo">Číslo popisné</label>
	                        <input type="text" id="fakturacni_cislo" name="fakturacni_cislo" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="fakturacni_obec">Město/obec</label>
	                        <input type="text" id="fakturacni_obec" name="fakturacni_obec" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="fakturacni_psc">PSČ</label>
	                        <input type="text" id="fakturacni_psc" name="fakturacni_psc" >
	                      </div>
	                      <div class="form-group">    
	                        <label for="fakturacni_id_stat">Stát</label>
	                        <select name="fakturacni_id_stat" id="fakturacni_id_stat">';
	                          
	                           foreach($_SESSION['staty_arr'] as $staty_k=>$staty_v)
			                    {
									 $obsah .= '<option value="'.$staty_k.'" ';
									 $obsah .= '>'.$staty_v.'</option>';
								}
	                      
	                      
	                    $obsah .= '
	                        </select>
	                      </div>
	                    </div>
	                  </div>
	                 
	                  <div class="form-spacer"> </div>
	                  <div class="form-group --checkbox">
	                    <label for="nl">Chci odebírat newsletter</label>
	                    <input type="checkbox" name="nl" value="1" id="nl" ';
	                    if($data['dodaci_psc']==1){$obsah .= ' checked ';}
	                    $obsah .= '>
	                  </div>
	                  
	                   <div class="form-group --checkbox">
			              <label for="souhlas_ou">Souhlasím se <a href="/ochrana-osobnich-udaju">zpracováním osobních údajů</a> za účelem registrace.</label>
			              <input type="checkbox" name="souhlas_ou" value="1" id="souhlas_ou" required>
			            </div>
	                   
	                  <div class="form-group"> 
						<div class="g-recaptcha" data-sitekey="'.__CAPTCHA_SITE_KEY__.'"></div>
	                    <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
	                    <button class="btn --fullWidth" name="register-submit" id="register-submit" >Vytvořit účet <img src="/img/icons/cheveron-white.svg"></button>
	                  </div>
	                </form>
	                </div>
	                </div>';
	                

        
	
	
	



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
$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');


// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('registrace'); 

 
?>
