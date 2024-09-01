<?php
// trida Reklamace
// do tridy predavame 2 typy parametru v polich

class Reklamace
{

public $parametry; // parametry za lomitky
public $get_parametry;  // klasicke GET parametry
public $skript; // nazev stranky
	
	
	function __construct($skript,$parametry,$get_parametry)
	{
	 $this->skript = $skript;
	 $this->parametry = $parametry;
	 $this->get_parametry = $get_parametry;


	}
	
	
	
	public function zobrazForm()
	{
       
		$ret = '';
		
		if($_POST['r_u'])
		{
		   // form byl odeslán
		   $ret = $this->zpracujForm();
		}
		else
		{
		  $ret = $this->formular();
		}
		
		return $ret;
    
    }
    
    
    
    
    
    public function zpracujForm()
    {
		   $ret = '';
		   $err = '';
	
		   if(kontrola_ref())
		   {
		      $ret = kontrola_ref();
		   }
		   else
		   {
		       
		       // kontrolujeme jestli jsou vyplněné položky
		       if(!$_POST['r_jmeno'])
		       {
			     $err .= 'Nevyplnili jste jméno a příjmení<br>';
			   }
			   
			   if(!$_POST['r_cislo_obj'])
		       {
			     $err .= 'Nevyplnili jste číslo objednávky<br>';
			   }
			   
			   if(!$_POST['r_email'])
		       {
			     $err .= 'Nevyplnili jste email<br>';
			   }
			   
			   if(!$_POST['r_telefon'])
		       {
			     $err .= 'Nevyplnili jste telefon<br>';
			   }
			   
			   if(!$_POST['r_vyjadreni'])
		       {
			     $err .= 'Nevyplnili jste vyjádření k závadě<br>';
			   }
			   
			   // kontrola captchy
			   $captcha_secret_key = __CAPTCHA_SECRET_KEY__;
			   $captcha = $_POST['g-recaptcha-response'];
				
			   $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$captcha_secret_key."&response=".$captcha);
			   $response = json_decode($verify, true);

				if($response['success']==1)
				{
				  // OK
				}
				else
				{
				   $err .= 'Chyba při ověření captcha<br>';
				}
			   
			   
			   $body_rek = "Jméno: ".strip_tags($_POST['r_jmeno'])."\nEmail: ".strip_tags($_POST['r_email'])."\nTelefon: ".strip_tags($_POST['r_telefon'])."\nČíslo objednávky: ".strip_tags($_POST['r_cislo_obj'])."\nVyjádření k závadě: ".strip_tags($_POST['r_vyjadreni'])."\nNávrh: ".strip_tags($_POST['r_navrh']);
			   
			  // zpracování
			  if(!$err)
			  {
				$eml = New Email('reklamace',true);
			    $eml->nastavFrom(sanitize($_POST['r_email']));
			    $eml->nastavTo(__FORM_EMAIL__);
			    //$eml->nastavTo('info@w-software.com');
			    $eml->nastavSubject('Reklamace objednávky '.sanitize($_POST['r_cislo_obj']));
			    $eml->nastavBody($body_rek);
			    
			    if($_FILES['r_file']['size'][0] || $_FILES['r_file']['size'][1] || $_FILES['r_file']['size'][2])
			    {
					// přílohy pokud jsou
					$eml->pridejPrilohyReklamace($_FILES['r_file']);
				}
			    
			    $eml_odeslani = $eml->odesliEmail();
			    
			    if($eml_odeslani)
			    {
				  $ret = '<br>Vaše reklamace byla v pořádku odeslána.';
				}
				else
				{
				  $ret = '<br>Reklamaci se nepodařilo odeslat.';
				}

			    
			  }
			  else
			  {
			    // výpis chyb
			    $ret = '<br><span style="color: red">'.$err.'</span>';
			  }
			  
		   
		   }
		   
		   return $ret;
	
	}
	

	
	
	public function formular()
	{ 
		
		
		$form = '<div class="container"> 
		<div class="page --narrow">
		
          <section class="page-heading">
            <h1>Reklamace</h1>
            <p>Zde podávejte případné reklamace. Máte možnost přiložit fotografie a popsat případné nedostatky. Pro zaslání reklamace, po předchozím e-mailu, zaslat na adresu:<span>OZEO s.r.o., Beskydská 1488, 73801  Frýdek-Místek</span></p>
          </section>
          
          <form method="post" id="rma-form" enctype="multipart/form-data">
            <div class="form-group">    
              <label for="rma-name">Jméno a příjmení</label>
              <input type="text" id="rma-name" name="r_jmeno" required>
            </div>
            <div class="form-group">    
              <label for="rma-id">Číslo objednávky</label>
              <input type="text" id="rma-id" name="r_cislo_obj" required>
            </div>
            <div class="form-group">    
              <label for="rma-email">Email</label>
              <input type="email" id="rma-email" name="r_email" required>
            </div>
            <div class="form-group">    
              <label for="rma-tel">Telefon</label>
              <input type="tel" id="rma-tel" name="r_telefon" required>
            </div>
            <div class="form-group">    
              <label for="rma-text">Vyjádření k závadě</label>
              <textarea type="text" id="rma-text" name="r_vyjadreni" rows="4" required></textarea>
            </div>
            <div class="form-group">    
              <input type="file" id="rma-file1" name="r_file[]" accept="image/*,.pdf,.doc,.docx">
            </div>
            <div class="form-group">    
              <input type="file" id="rma-file2" name="r_file[]" accept="image/*,.pdf,.doc,.docx">
            </div>
            <div class="form-group">    
              <input type="file" id="rma-file3" name="r_file[]" accept="image/*,.pdf,.doc,.docx">
            </div>
            <div class="form-group">    
              <label for="rma-solution">Případný návrh řešení problému (nepovinné)</label>
              <textarea type="text" id="rma-solution" name="r_navrh" rows="4"></textarea>
            </div>
            <div class="form-group --checkbox">
              <label for="rma-agree1">Souhlasím se <a href="/ochrana-osobnich-udaju">podmínkami ochrany osobních údajů.</a></label>
              <input type="checkbox" name="r_souhlas" id="rma-agree1" required>
            </div>
            <div class="form-group"> 
				<div class="g-recaptcha" data-sitekey="'.__CAPTCHA_SITE_KEY__.'"></div>
	            <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
              <button class="btn --fullWidth" name="rma-submit" id="rma-submit">Odeslat reklamaci <img src="/img/icons/cheveron-white.svg"></button>
            </div>
          </form>
        </div>
      </div>';
		

		
		return $form;
		
	}
	
	

	


}
