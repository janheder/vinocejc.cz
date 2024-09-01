<?php
// trida Recenze
// do tridy predavame 2 typy parametru v polich

class Recenze
{

public $parametry; // parametry za lomitky
public $get_parametry;  // klasicke GET parametry
public $skript; // nazev stranky
	
	
	function __construct($skript,$parametry,$get_parametry)
	{
	 $this->skript = $skript;
	 $this->parametry = $parametry;
	 $this->get_parametry = $get_parametry;
	 
	     if($_GET['page'])
		 {
		     $this->limit = ((intval($_GET['page']) * __POCET_RECENZI__) - __POCET_RECENZI__); // pro stránkovací rutinu
	     }
	     else
	     {
			 $this->limit = 0;
		 }

	}
	
	
	
	public function recenzePata($x)
	{ 
		$ret = '';
		
		if(__RECENZE_ZAKAZNIKU__==1)
		{
			
			if(__RECENZE_ZAKAZNIKU_ZOBRAZENI__==2 || (__RECENZE_ZAKAZNIKU_ZOBRAZENI__==1 && $this->skript=='uvod'))
			{
				$pocet_recenzi = Db::queryCount2('recenze','id','','aktivni=1');
				$recenze_prumer = Db::queryRow('SELECT AVG(hvezdy) AS PRUMER FROM recenze WHERE aktivni=?',array(1));
				
				$ret .= '<section id="ratingSection">
					        <div class="container">
					        <div class="rating-section-inner">
					          <div class="rating-content">
					            <div class="rate-average-inner">
					              <div class="span rate-average">'.round($recenze_prumer['PRUMER'],2).'</div><span class="rate-star-wrap">
					                <div class="stars">';

					                $ret .= $this->recenzeHvezdy(round($recenze_prumer['PRUMER']));
	
					                $ret .= '</div></span>
					            </div>
					            <div class="rating-content-title">
					              <h3>Co o nás říkají naši zákazníci</h3>
					              <div class="rating-content-text"><span id="ratingSectionCount"><span class="stars-label">'.$pocet_recenzi.' hodnocení</span></span><a href="/recenze">Zobrazit více hodnocení</a></div>
					            </div>
					          </div>
					          <div class="vote-grid">';
				
				   $data_r = Db::queryAll('SELECT jmeno, hvezdy, recenze, datum FROM recenze WHERE aktivni=? ORDER BY id DESC LIMIT '.$x.' ', array(1));
				   if($data_r)
				   {
						   
						   foreach($data_r as $row_r)
						   {
		
							    $ret .= '<div class="vote-wrap">
				                <div class="vote-header"><span class="vote-pic"></span><span class="vote-summary"><span class="vote-name vote-name--nowrap"><span>'.$row_r['jmeno'].'</span>
				                <span>'.date('d.m.Y',$row_r['datum']).'</span></span>
				                <span class="vote-rating">
				                      <div class="stars">';
				                      $ret .= $this->recenzeHvezdy($row_r['hvezdy']);  
				                      $ret .='</div>
				                      <div class="vote-checked">Ověřená recenze</div></span></span></div>
				                <div class="vote-content">'.$row_r['recenze'].'</div>
				              </div>';
              
               
						   }
				    }
				    
				    
				    
				      $ret .= '</div>
			          </div>
			          </div>
			      </section>';
			}
			
		}
		
		return $ret;
		
	}
	
	
	public function recenzeHvezdy($pocet)
	{
		$ret = '';
	  
		for ($i = 0; $i < $pocet; $i++) 
		{
		    $ret .= '<a class="star star-on"></a>';
		}
 
		if($i<5)
		{
			for ($ii = $i; $ii < 5; $ii++) 
			{
			    $ret .= '<a class="star star-off"></a>';
			}
		}
		
		return $ret;
	}
	
	
	public function recenzeVypis()
	{ 
		// vypis všech se stránkováním
		$ret = '';
		
		$pocet_recenzi = Db::queryCount2('recenze','id','','aktivni=1');
		$recenze_prumer = Db::queryRow('SELECT AVG(hvezdy) AS PRUMER FROM recenze WHERE aktivni=?',array(1));
		
		if($pocet_recenzi)
		{
			 // počty recenzí a procent
			 
			 $pocet_recenzi1 = Db::queryCount2('recenze','id','','aktivni=1 AND hvezdy=1');
			 $pocet_recenzi2 = Db::queryCount2('recenze','id','','aktivni=1 AND hvezdy=2');
			 $pocet_recenzi3 = Db::queryCount2('recenze','id','','aktivni=1 AND hvezdy=3');
			 $pocet_recenzi4 = Db::queryCount2('recenze','id','','aktivni=1 AND hvezdy=4');
			 $pocet_recenzi5 = Db::queryCount2('recenze','id','','aktivni=1 AND hvezdy=5');

			 // 100% = $pocet_recenzi
			 
			 $pocet_recenzi_1_hv = round($pocet_recenzi1 / $pocet_recenzi * 100);
			 $pocet_recenzi_2_hv = round($pocet_recenzi2 / $pocet_recenzi * 100);
			 $pocet_recenzi_3_hv = round($pocet_recenzi3 / $pocet_recenzi * 100);
			 $pocet_recenzi_4_hv = round($pocet_recenzi4 / $pocet_recenzi * 100);
			 $pocet_recenzi_5_hv = round($pocet_recenzi5 / $pocet_recenzi * 100);
			  
			
			 $ret .= '<div class="infomessage">Hodnocení eshopu může provést pouze zákazník, který dokončil objednávku. Po několika dnech po převzetí objednávky je zákazníkovi zaslán email, kde je uveden odkaz na hodnotící formulář.</div>
            <div class="votepage-content">
              <div class="votepage-summary">
                <div class="rate-average-wrap">
                  <div class="rate-average-inner">
                    <div class="rate-average">'.round($recenze_prumer['PRUMER'],2).'</div>
                    <div class="rate-star-wrap">
                      <div class="stars">
                        <div class="star star-on"></div>
                        <div class="star star-on"></div>
                        <div class="star star-on"></div>
                        <div class="star star-on"></div>
                        <div class="star star-off"></div>
                      </div>
                      <div class="stars-label">'.$pocet_recenzi.' hodnocení</div>
                    </div>
                  </div>
                  <div class="rate-graph">
                    <div class="rate-list" data-score="5">
                      <div class="rate-star stars">
                        <div class="rate-value">5</div>
                        <div class="star star-off"></div>
                      </div>
                      <div class="rate-block">
                        <div class="rate-bar" style="width: '.$pocet_recenzi_5_hv.'%;"></div>
                      </div>
                    </div>
                    <div class="rate-list" data-score="4">
                      <div class="rate-star stars">
                        <div class="rate-value">4</div>
                        <div class="star star-off"></div>
                      </div>
                      <div class="rate-block">
                        <div class="rate-bar" style="width: '.$pocet_recenzi_4_hv.'%;"></div>
                      </div>
                    </div>
                    <div class="rate-list" data-score="3">
                      <div class="rate-star stars">
                        <div class="rate-value">3</div>
                        <div class="star star-off"></div>
                      </div>
                      <div class="rate-block">
                        <div class="rate-bar" style="width: '.$pocet_recenzi_3_hv.'%;"></div>
                      </div>
                    </div>
                    <div class="rate-list" data-score="2">
                      <div class="rate-star stars">
                        <div class="rate-value">2</div>
                        <div class="star star-off"></div>
                      </div>
                      <div class="rate-block">
                        <div class="rate-bar" style="width: '.$pocet_recenzi_2_hv.'%;"> </div>
                      </div>
                    </div>
                    <div class="rate-list" data-score="1">
                      <div class="rate-star stars">
                        <div class="rate-value">1</div>
                        <div class="star star-off"></div>
                      </div>
                      <div class="rate-block">
                        <div class="rate-bar" style="width: '.$pocet_recenzi_1_hv.'%;"> </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>';
			
			$ret .= '<div class="vote-grid">';
			
			
				$data_r = Db::queryAll('SELECT * FROM recenze WHERE aktivni=? ORDER BY id DESC LIMIT '.$this->limit.','.__POCET_RECENZI__.' ', array(1));
				$pocet = Db::queryAffected('SELECT id FROM recenze WHERE aktivni=? ORDER BY id DESC', array(1));
		
				   foreach($data_r as $row_r)
				   {

				    $ret .= '<div class="vote-wrap">
                  <div class="vote-rating">
                    <div class="stars">';
                      
                       $ret .= $this->recenzeHvezdy($row_r['hvezdy']);  
                       
                    $ret .= '</div>
                  </div>
                  <div class="vote-content">'.$row_r['recenze'].'</div>
                  <div class="vote-header"><span class="vote-pic"></span>
                    <div class="vote-summary">
                      <div class="vote-name vote-name--nowrap"> '.$row_r['jmeno'].'</div>
                      <div class="vote-time">'.date('d.m.Y',$row_r['datum']).'</div>
                    </div>
                    <div class="vote-checked">Ověřená recenze</div>
                  </div>
                </div>';
 
				   }
				   
				    $ret .= '</div>';
				   
				   
				   $ret .= $this->strankovani($pocet);
		    }
		
		return $ret;
		
	}
	
	

	public function strankovani($pocet)
	{
		$ret = '';

		if(__POCET_RECENZI__ < $pocet)
		{
			// pokud je méně výsledků než je výpis na stránku tak stránkování nezobrazujeme

			$url = $_SERVER['REDIRECT_SCRIPT_URI'];

			if($_GET)
			{
				$get_parametry = '?';

				foreach($_GET as $get_key=>$get_val)
				{
					if($get_key!='page')
					{
					   // pro případ, kdy máme v GETu pole
					   if(is_array($get_val))
					   {
						    foreach($get_val as $get_key2=>$get_val2)
							{
							    $get_parametry .= $get_key.'[]='.strip_tags($get_val2).'&';
							}
					   }
					   else
					   {
						   $get_parametry .= $get_key.'='.strip_tags($get_val).'&';
					   }


					}

				}
			}
			else
			{
				$get_parametry = '?';
			}




			$ps = ceil($pocet / __POCET_RECENZI__);
			if(!$_GET['page'])
			{
			$ps2 = 1;
			}
			else
			{
			$ps2 = intval($_GET['page']);
			}

			$leva = intval(max(1,$ps2-5));
			$prava = intval(min($ps,$ps2+5));
			$leva_pocet = $ps2 - $leva;
			$prava_pocet = $prava - $ps2;

			if ( $leva_pocet + $prava_pocet != 5 )
			{
				if ( $leva_pocet < 5 )
					$prava = min($ps, $prava + ( 5 - $leva_pocet ));

				if ( $prava_pocet < 5 )
					$leva = max(1, $leva - ( 5 - $prava_pocet ));
			}

			$ret .= '<div class="pagination" aria-label="Stránkování produktů">
              <ul class="pagination__list">';

			if($leva>1)
			{
				$ret .= '<li class="pagination__item"><a class="pagination__link" href="'.$url.$get_parametry.'page=1">1</a></li>';

			}

			for ($px=$leva;$px<=$prava;$px++)
			{

				if($px==$ps2)
				{
					$ret .= '<li class="pagination__item --active"><a class="pagination__link" href="'.$url.$get_parametry.'page='.$px.'">'.$px.'</a></li>';


				}
				else
				{
					$ret .= '<li class="pagination__item"><a class="pagination__link" href="'.$url.$get_parametry.'page='.$px.'">'.$px.'</a></li>';
				}

			}

			if($prava<$ps)
			{
				$ret .= '<li class="pagination__item"><a class="pagination__link" href="'.$url.$get_parametry.'page='.$ps.'">'.$ps.'</a></li>';
			}

              $ret .= '</ul>
            </div>';
		}


		return $ret;
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
			   
			   if(!$_POST['r_id_obj'])
		       {
			     $err .= 'Chybí číslo objednávky<br>';
			   }
			   
			   if(!$_POST['r_id_zak'])
		       {
			     $err .= 'Chybí číslo zákazníka<br>';
			   }
			   
			   if(!$_POST['r_hvezdy'])
		       {
			     $err .= 'Nevyplnili jste počet hvězd<br>';
			   }
			   
			   if(!$_POST['r_recenze'])
		       {
			     $err .= 'Nevyplnili jste recenzi<br>';
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
			   
			   
			   $body_rec = "Jméno: ".strip_tags($_POST['r_jmeno'])."\nČíslo objednávky: ".strip_tags($_POST['r_cislo_obj'])."\nPočet hvězd: ".strip_tags($_POST['r_hvezdy'])."\nRecenze: ".strip_tags($_POST['r_recenze']);
			   
			  // zpracování
			  if(!$err)
			  {
				  // uložíme do db
				  $data_insert = array(
							    'ido' => intval($_POST['r_id_obj']),
							    'idz' => intval($_POST['r_id_zak']),
							    'jmeno' => sanitize($_POST['r_jmeno']),
							    'hvezdy' => intval($_POST['r_hvezdy']),
							    'recenze' => sanitize($_POST['r_recenze']),
							    'datum' => time(),
							    'ip' => sanitize(getip()),
							    'aktivni' => 0
							     );
					     
				$query_insert = Db::insert('recenze', $data_insert);
				  
				$eml = New Email('plaintext',false);
			    $eml->nastavFrom(__EMAIL_FROM__);
			    $eml->nastavTo(__FORM_EMAIL__);
			    //$eml->nastavTo('info@w-software.com');
			    $eml->nastavSubject('Recenze objednávky '.intval($_POST['r_cislo_obj']));
			    $eml->nastavBody($body_rec);

			    $eml_odeslani = $eml->odesliEmail();
			    
			    if($eml_odeslani)
			    {
				  $ret = '<div class="review-thanks"><img src="/img/icons/thumbsup.svg" alt="Děkujeme za vaše hodnocení">
					          <h2>Děkujeme za vaše hodnocení</h2>
					        </div>';
				}
				else
				{
				  $ret = '<br>Recenzi se nepodařilo odeslat.';
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
		$err = '';
		
		// kontrolujeme jestli souhlasí číslo objednávky a hash
		if($_GET['ido'] && $_GET['ho'])
		{
			$recenze_kontrola_obj = Db::queryRow('SELECT id, id_zakaznik FROM objednavky WHERE cislo_obj=? AND hash_recenze=?',array($_GET['ido'],$_GET['ho']));
			if($recenze_kontrola_obj['id'])
			{
				// kontrola hashe je OK
				// nyní zkontrolujeme jestli už nemá uloženou recenzi
				
				$recenze_kontrola = Db::queryRow('SELECT id, datum FROM recenze WHERE ido=? AND idz=?',array($recenze_kontrola_obj['id'],$recenze_kontrola_obj['id_zakaznik']));
				if($recenze_kontrola['id'])
				{
					$err .= '<div class="review-error"><img src="/img/icons/info.svg" alt="Už jste hodnotili">
					          <h2>Už jste hodnotili</h2>
					          <p>Hodnocení pro objednávku č.'.sanitize($_GET['ido']).' bylo provedeno dne '.date('d.m.Y',$recenze_kontrola['datum']).'. Objednávku nelze hodnotit opakovaně.</p>
					        </div>';
        

				}
			}
			else
			{
				$err .= 'Chybný kontrolní řetězec objednávky';
			}
			
		}
		else
		{
		  $err .= 'Chybí číslo objednávky nebo kontrolní řetězec';
		}
		
		
		
		if(!$err)
	    {
			
			$zakaznik = Db::queryRow('SELECT jmeno, prijmeni FROM zakaznici WHERE id=? ',array($recenze_kontrola_obj['id_zakaznik']));
			
		$form = '<div class="container"> 
		<div class="page --narrow">
		
          <section class="page-heading">
            <h1>Recenze</h1>
            <p>'.__RECENZE_TEXT_FORM__.'</p>
          </section>
          
          <form method="post" id="rma-form">
            <div class="form-group">    
              <label for="rma-name">Vaše jméno</label>
              <input type="text" id="rma-name" name="r_jmeno" value="'.$zakaznik['jmeno'].' '.$zakaznik['prijmeni'].'" required>
            </div>';
            /*
            $form = '<div class="form-group">    
              <label for="rma-id">Počet hvězd</label>
              <select name="r_hvezdy" required>';
              
              foreach($_SESSION['hvezdy_arr'] as $hvezdy_k=>$hvezdy_v)
              {
					$form .= '<option value="'.$hvezdy_k.'">'.$hvezdy_v.'</option>';
			  }
			  
              $form .= '</select>
            </div>';
            */
            
            $form .= '<div class="form-group"> 
              <div class="rating-box"><span>Zvolte počet hvězdiček:</span>
                <div class="rating-container">
                  <input type="radio" name="r_hvezdy" value="5" id="star-5" required>
                  <label for="star-5">&#9733;</label>
                  <input type="radio" name="r_hvezdy" value="4" id="star-4" required>
                  <label for="star-4">&#9733;</label>
                  <input type="radio" name="r_hvezdy" value="3" id="star-3" required>
                  <label for="star-3">&#9733;</label>
                  <input type="radio" name="r_hvezdy" value="2" id="star-2" required>
                  <label for="star-2">&#9733;</label>
                  <input type="radio" name="r_hvezdy" value="1" id="star-1" required>
                  <label for="star-1">&#9733;                                </label>
                </div>
              </div>
            </div>';
            
            $form .= '<div class="form-group">    
              <label for="rma-text">Recenze</label>
              <textarea type="text" id="r_recenze" name="r_recenze" rows="4" required></textarea>
            </div>

            <div class="form-group"> 
				<div class="g-recaptcha" data-sitekey="'.__CAPTCHA_SITE_KEY__.'"></div>
	            <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
	            <input type="hidden" name="r_id_obj" id="r_id_obj" value="'.$recenze_kontrola_obj['id'].'">
	            <input type="hidden" name="r_id_zak" id="r_id_zak" value="'.$recenze_kontrola_obj['id_zakaznik'].'">
	            <input type="hidden" name="r_cislo_obj" id="r_cislo_obj" value="'.intval($_GET['ido']).'">
              <button class="btn --fullWidth" name="rma-submit" id="rma-submit">Odeslat recenzi <img src="/img/icons/cheveron-white.svg"></button>
            </div>
          </form>
        </div>
      </div>';
      
      
         
         }
         else
         {
			$form = '<div class="container"><br><span style="color: red">'.$err.'</span></div>';
		 }
		

		
		return $form;
		
	}
	


}
