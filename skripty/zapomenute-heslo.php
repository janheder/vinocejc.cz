<?php
// zapomenute heslo
$options_pass = ['cost' => 12,];

if($_GET['em_ver'])
{
	
  if($_SESSION['zmena_hesla'])
  {

	   if(($_SESSION['zmena_hesla']['time'] + 1800) > time())
	   {
	      // znovu zkontrolujeme email
	      $data_z = Db::queryRow('SELECT id FROM zakaznici WHERE email=? AND bez_registrace=0 AND aktivni=1 ', array(sanitize($_SESSION['zmena_hesla']['email'])));
		  if($data_z)
		  {
		     // vygenerujeme form pro změnu hesla
		     $obsah = '<div class="page --narrow">
		
		            <p>Zde si můžete nastavit své nové heslo. Pro Vaši bezpečnost použijte kombinaci malý a velkých písmen a čísel.</p>
		
		          <form method="post" id="password-form" action="/zapomenute-heslo">
		            <div class="form-group">    
		              <label for="password-email">Heslo</label>
		              <input type="password" id="password-email" name="heslo" required minlength="6" maxlength="100"  >
		            </div>
		            <div class="form-group"> 
		              <input type="hidden" name="r_u" value="'.sha1(time()).'">
		              <input type="hidden" name="em_ver" value="'.time().'">
		              <button class="btn --fullWidth" name="password-submit" id="password-submit">Změnit heslo <img src="/img/icons/cheveron-white.svg"></button>
		            </div>
		          </form>
		      </div>';
		     
		  }
		  else
		  {
		        $data_insert = array(
			    'email' => sanitize($_SESSION['zmena_hesla']['email']),
			    'ip' => sanitize(getip()),
			    'datum' => time()
			     );
			     
			     $query_insert = Db::insert('log_prihlaseni_heslo', $data_insert);	     
				 $obsah = 'Vámi zadaný email se v databázi nenachází. Pokud chcete nakupovat jako registrovaný zákazník tak si vytvořte novou <a href="/registrace">registraci</a>.';
		  }
	   }
	   else
	   {
	      $obsah = 'Odkaz pro změnu hesla již není platný.'; 
	   }
  }
  else
  {
    $obsah = 'Platnost odkazu vypršela.'; 
  }
   
}
elseif($_POST['heslo'] && $_SESSION['zmena_hesla'] && $_POST['r_u'])
{
	if(kontrola_ref())
	{
	  die(kontrola_ref());
	}
	// uživatel si změnil heslo
	// zjistíme jestli je odkaz ješte platný
 
    
       if(($_SESSION['zmena_hesla']['time']+ 1800) > time())
	   {
	      // znovu zkontrolujeme email
	      $data_z = Db::queryRow('SELECT id FROM zakaznici WHERE email=? AND bez_registrace=0 AND aktivni=1  ', array(sanitize($_SESSION['zmena_hesla']['email'])));
		  if($data_z)
		  {
		     // změníme heslo
		     if($_POST['heslo'])
		     {
				$data_update['heslo'] = password_hash($_POST['heslo'], PASSWORD_BCRYPT, $options_pass);
				
				$where_update = array('id' => intval($_SESSION['zmena_hesla']['id']));
				$query_update = Db::update('zakaznici', $data_update, $where_update);
		     
		        $obsah = '<div class="page --narrow">
		
		            <p>Vaše heslo pro účet '.sanitize($email).' bylo změněno. Nyní se můžete přihlásit s novým heslem.</p>
		
		         
		      </div>';
		      
		      unset($_SESSION['zmena_hesla']);
			 }
							
				
		     
		  }
		  else
		  {
		        $data_insert = array(
			    'email' => sanitize($_SESSION['zmena_hesla']['email']),
			    'ip' => sanitize(getip()),
			    'datum' => time()
			     );
			     
			     $query = Db::insert('log_prihlaseni_heslo', $data_insert);	     
				 $obsah = 'Vámi zadaný email se v databázi nenachází. Pokud chcete nakupovat jako registrovaný zákazník tak si vytvořte novou <a href="/registrace">registraci</a>.';
		  }
	   }
	   else
	   {
	      $obsah = 'Odkaz pro změnu hesla již není platný.'; 
	   }
	
}
elseif($_POST['password_email'] && $_POST['r_u'])
{
  if(kontrola_ref())
	{
	  die(kontrola_ref());
	}
			
  // kontrola počtu pokusů
  if(Uzivatel::blokujPristupZapomenuteHeslo() >=3 )
  { 
	  $obsah = 'Mnoho neúspěšných pokusů.'; 
  } 
  else
  {
	  // zkontrolujeme email v zákaznících  
	  $data_z = Db::queryRow('SELECT id, email FROM zakaznici WHERE email=? AND bez_registrace=0 AND aktivni=1  ', array(sanitize($_POST['password_email'])));
	  if($data_z)
	  {
	      // email nalezen
	      // odešleme postup s odkazem
	      $odkaz_zmena_hesla = __URL__.'/zapomenute-heslo?em_ver='.time();
	      
	      $body_zap_heslo = "Nové heslo pro přihlášení na ".__URL__." si můžete nastavit po kliku na tento odkaz: ".$odkaz_zmena_hesla."\nOdkaz je platný 30 minut.\nTento email je generovaný automaticky - neodpovídejte na něj.\n\n".__KONTAKTY_PATA__;
	      
	      // nastavíme si potřebné údaje do sešny
	      $data_zmena_hesla = array(
						     'id' => $data_z['id'],
						     'email' => $data_z['email'],
						     'ip' => sanitize(getip()),
						     'time' => time()
						     );
						     
						  
		  $_SESSION['zmena_hesla'] = $data_zmena_hesla;
	      
	      $eml = New Email('plaintext',false);
		  $eml->nastavFrom(__EMAIL_FROM__);
		  $eml->nastavTo(sanitize($_POST['password_email']));
		  $eml->nastavSubject('Zapomenuté heslo');
		  $eml->nastavBody($body_zap_heslo);
		  $eml_odeslani = $eml->odesliEmail();
		  
		  $obsah = 'Na vaši emailovou adresu byly zaslány pokyny pro změnu hesla.';
	  }
	  else
	  {
	        $data_insert = array(
		    'email' => sanitize($_POST['password_email']),
		    'ip' => sanitize(getip()),
		    'datum' => time()
		     );
		     
		     $query = Db::insert('log_prihlaseni_heslo', $data_insert);	     
			 $obsah = 'Vámi zadaný email se v databázi nenachází. Pokud chcete nakupovat jako registrovaný zákazník tak si vytvořte novou <a href="/registrace">registraci</a>.';
	  }
  }
			  	
   
}
else
{
	
  if(Uzivatel::blokujPristupZapomenuteHeslo() >=3 )
  { 
	  $obsah = 'Mnoho neúspěšných pokusů.'; 
  } 
  else
  {
	  
	$obsah = '<div class="page --narrow">
	
	            <p>Pokud jste zapomněli své heslo, vyplňte zde svůj email, který jste uvedli při registraci. Na něj Vám zašleme další postup jak heslo změnit.</p>
	
	          <form method="post" id="password-form">
	            <div class="form-group">    
	              <label for="password-email">Email</label>
	              <input type="email" id="register-email" name="password_email" required minlength="6" maxlength="255" pattern="([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})">
	            </div>
	            <div class="form-group"> 
	              <input type="hidden" name="r_u" value="'.sha1(time()).'">
	              <button class="btn --fullWidth" name="password-submit" id="password-submit">Poslat nové heslo <img src="/img/icons/cheveron-white.svg"></button>
	            </div>
	          </form>
	      </div>';
	}
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
$sablonka->pridejDoSablonky('{nadpis}','Zapomenuté heslo','html');
$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');


// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('txt'); 
?>
