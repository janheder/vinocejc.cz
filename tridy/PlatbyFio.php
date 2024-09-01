<?php
// trida pro parovani plateb objednavek s uctem u fio banky

class PlatbyFio
{

private  $fio_api_token;  
private  $fio_url;
public $pocet_sparovani = 0;

	function __construct($fio_api_token)
	{
		 $this->fio_api_token = $fio_api_token;
		 $vcera = (time() - (24 * 3600));
		 $this->fio_url = 'https://www.fio.cz/ib_api/rest/periods/'.$fio_api_token.'/'.date('Y-m-d',$vcera).'/'.date('Y-m-d').'/transactions.csv';
		 $this->pocet_sparovani = $pocet_sparovani;
		 
		  
	}

	public function pocetSparovani()
	{
	  return $this->pocet_sparovani;
	}
	
	public function generovaniFaktury($id_registrace,$cislo_objednavky,$var_symbol,$id_objednavky,$radky_faktury,$sluzby_arr_eml,$sluzby_arr_ceny,$id_sluzby_arr,$cena_celkem,$id_stav)
	{
		
	          // vygenerujeme faktury ve Fakturoidu
			   $f = new Fakturoid\Client(__FAKTUROID_SLUG__, __FAKTUROID_EMAIL__, __FAKTUROID_API_KEY__, 'Bezpasaka.cz <info@w-software.com>');
			   
			   // zjistíme fakturační údaje
			   $data_reg = Db::queryRow('SELECT * FROM registrace WHERE id = ?', array($id_registrace));
			   if($data_reg)
               {
				   // client_name, client_street, client_city, client_zip, client_registration_no (IČ), subject_custom_id (id registrace), status (paid), order_number (č. obj)
				   // vat_price_mode(null, without_vat, from_total_with_vat), public_html_url
				   
				   // musíme získat slug pro emailovou šablonu
				   if($data_reg['typ']==1)
				   {
					    // divka
					    $data_divka = Db::queryRow('SELECT str FROM divky WHERE id_registrace = ?', array($data_reg['id']));
						if($data_divka)
						{
						  $url_profilu = __URL__.'/divky/'.$data_divka['str'];
						}
				   }
				   elseif($data_reg['typ']==2)
				   {
					   // privat
					    $data_privat = Db::queryRow('SELECT str FROM privaty WHERE id_registrace = ?', array($data_reg['id']));
						if($data_privat)
						{
						  $url_profilu = __URL__.'/divky/'.$data_privat['str'];
						}
				   }
				   elseif($data_reg['typ']==3)
				   {
					   // nocni klub
					   $data_klub = Db::queryRow('SELECT str FROM nocni_kluby WHERE id_registrace = ?', array($data_reg['id']));
						if($data_klub)
						{
						  $url_profilu = __URL__.'/divky/'.$data_klub['str'];
						}
				   }
				   
				   $client_data_arr = array();
				   
				   // jmeno
				   if($data_reg['nazev_dle_or'])
				   {
					   $client_name = $data_reg['nazev_dle_or'];
				   }
				   else
				   {
					   $client_name = $data_reg['jmeno'];
				   }
				   
				   // ič
				   if($data_reg['ic'])
				   {

					   $client_data_arr['registration_no'] = $data_reg['ic'];
				   }


				   
				   $client_data_arr['name'] = $client_name;
				   $client_data_arr['email'] = $data_reg['email'];
				   $client_data_arr['subject_custom_id'] = $data_reg['id'];
				   $client_data_arr['order_number'] = $cislo_objednavky;
				   
				   
				   // create subject
				   $response = $f->createSubject($client_data_arr);
				   $subject  = $response->getBody();
					
				   // create invoice with lines
				   $lines    = $radky_faktury;
				   $response = $f->createInvoice(array('subject_id' => $subject->id, 'lines' => $lines));
				   $invoice  = $response->getBody();
				   $faktura_url = $invoice->public_html_url;
				   $cislo_faktury = $invoice->number;
				   $f->fireInvoice($invoice->id, 'pay'); // uhrazeno


				   // odešleme email s info o zaplacení a odkaz na fakturu
				   if($faktura_url)
				   {
					    // uložíme fakturu do naší DB
					     $data_insert_f = array(
							    'cislo_faktury' => $cislo_faktury,
							    'id_registrace' => $data_reg['id'],
							    'id_objednavky' => $id_objednavky,
							    'id_sluzby_arr' => serialize($id_sluzby_arr),
							    'text' => implode('<br>',$sluzby_arr_ceny),
							    'cena' => $cena_celkem,
							    'datum_vystaveni' => time(),
							    'datum_splatnosti' => (time() + (__SPLATNOST__ * 3600 * 24)),
							    'datum_plneni' => time(),
							    'var_symbol' => $var_symbol,
							    'faktura_url' => $faktura_url
							     );
					     
	                     $query_insert_f = Db::insert('faktury', $data_insert_f);
					    
					    
					    // id sablony poděkování za platbu = 15
						// vybereme sablonu emailu z db a odesleme email ********************************************************************
						// změna podle přeplatku nebo nedoplatku zasíláme různé emaily a SMS
						if($id_stav==5)
						{
								// přeplatek
								$eml_id = 25;
								
								// odešleme email na správce systému
								$eml_admin = New Email('plaintext',false);
								$eml_admin->nastavFrom(__EMAIL_FROM__);
								$eml_admin->nastavTo(__FORM_EMAIL__);
								$eml_admin->nastavSubject('Preplatek u objednavky c.'.$cislo_objednavky);
								$body_eml = 'Při spárování plateb u objednávky č.: '.$cislo_objednavky.' byl zjištěn přeplatek\nDetailní info zde: '.__URL__.'/admin/index.php?p=objednavky&id='.$id_objednavky.'\n\nTento email je generován automaticky systémem, neodpovídejte na něj.';
								$eml_admin->nastavBody($body_eml);
								$eml_admin_odeslani = $eml_admin->odesliEmail();
								
						}
						else
						{
								$eml_id = 15;
						}
						
						$data_eml = Db::queryRow('SELECT * FROM email_sablony WHERE id = ?', array($eml_id));
					    if($data_eml)
					    {
							$eml = New Email('html',false);
							$eml->nastavFrom(__EMAIL_FROM__);
							$eml->nastavTo($data_reg['email']);
							$eml->nastavSubject($data_eml['subject_cz']);
							$body_eml = $data_eml['sablona_cz'];
							// nahradime 
							$body_eml = str_replace('[NAZEV_SLUZBY]',implode('<br>',$sluzby_arr_eml),$body_eml);
							$body_eml = str_replace('[URL_PROFILU]','<a href="'.$url_profilu.'">'.$url_profilu.'</a>',$body_eml);
							$body_eml = str_replace('[ODKAZ_FAKTURA]','<a href="'.$faktura_url.'">'.$faktura_url.'</a>',$body_eml);
							$eml->nastavBody($body_eml);
							$eml_odeslani = $eml->odesliEmail();
							
							if($eml_odeslani)
							{
								/*echo '<div class="alert-success">V pořádku!<br>Email byl odeslán na '.sanitize($data_reg['email']).'<br><a href="./index.php?p='.strip_tags($_GET['p']).'&pp='.strip_tags($_GET['pp']).'">Přejít na přehled</a></div>';*/
							}
							else
							{
								echo '<div class="alert-warning">Chyba!<br>Nepodařilo se odeslat email na '.$data_reg['email'].'<br></div>';
							}
							
							
						}
						else
						{
							echo '<div class="alert-warning">Chyba!<br>Nepodařilo se načíst šablonu emailu s id 15<br></div>';
						}
						
						
						
						// odešleme SMS s poděkováním za platbu
						
						if($data_reg['telefon'])
						{   // id = 4 = SMS sablona podekovani za platbu
							if($id_stav==5)
							{
								// přeplatek
								$sms_id = 18;
								
							}
							else
							{
								$sms_id = 4;
							}
							
							$data_sms = Db::queryRow('SELECT * FROM sms_sablony WHERE id = ?', array($sms_id));
						    if($data_sms)
						    {	
								// z tel. cisla odstranime mezery
								$telefonni_cislo = str_replace(' ','',$data_reg['telefon']);
								$sms_zprava = $data_sms['sms_cz'];
								$sms = New Sms($telefonni_cislo,$sms_zprava,$data_reg['id']);
								$sms_odeslani = $sms->odesliSMS();
								
								if($sms_odeslani)
								{
									/*echo '<div class="alert-success">V pořádku!<br>SMS byla odeslána na '.sanitize($telefonni_cislo).'<br><a href="./index.php?p='.strip_tags($_GET['p']).'&pp='.strip_tags($_GET['pp']).'">Přejít na přehled</a></div>';*/
								}
								else
								{
									echo '<div class="alert-warning">Chyba!<br>Nepodařilo se odeslat SMS<br></div>';
								}
							}
							else
							{
								echo '<div class="alert-warning">Chyba!<br>Nepodařilo se načíst šablonu SMS<br></div>';
							}
						}
						
						
						
				   }
				
			   }
		
		
		
	}

	
	public function sparujPlatby()
	{
		 
		if($this->fio_api_token && $this->fio_url)
		{
			// stáhneme transakce
			$transakce_csv = file_get_contents($this->fio_url);
		    $r = 0;	
		    $radky = explode("\n",$transakce_csv);
		    //var_dump($transakce_csv);
		    foreach($radky as $k_r => $v_r)
		    {
		      // na řádku 12 začínají hlavičky sloupcu
		      // 'ID pohybu;Datum;Objem;Měna;Protiúčet;Název protiúčtu;Kód banky;Název banky;KS;VS;SS;Uživatelská identifikace;Zpráva pro příjemce;Typ;Provedl;Upřesnění;Komentář;BIC;ID pokynu'
		      if($r > 12)
		      {
				  
				 if($v_r)
				 {
					 $parametry = explode(";",$v_r);  
						 
					 $id_pohybu = $parametry[0];
					 $datum_pohybu = $parametry[1];
					 $castka =  str_replace(',','.',$parametry[2]);
					 $mena = $parametry[3];
					 $protiucet = $parametry[4]."/".$parametry[6];
					 $var_symbol = $parametry[9];
					 
					 //var_dump($castka);
					 
					 if($castka{0}=='-')
					 {
						 // výdaj
					 }
					 else
					 {	 
						// příjem
						// kontrolujeme vs v objednávkách
	
						$data_o = Db::queryRow('SELECT * FROM objednavky WHERE sparovano=0 AND id_stav=1 AND id_platba!=3 AND variabilni_symbol=? ORDER BY id DESC', array($var_symbol));
						if($data_o !== false ) 
				        {
						   // platba spárována
						   // kontrolujeme jestli odpovídá částka
						   if(intval($data_o['cena_celkem']) == intval($castka))
						   {
							   // částka sedí
							   $id_stav = 2;
						   }
						   elseif(intval($data_o['cena_celkem']) > intval($castka))
						   {
							   // nezaplatila celou částku
							   // negenerujeme fakturu
							   $id_stav = 4;
						   }
						   elseif(intval($data_o['cena_celkem']) < intval($castka))
						   {
							   // přeplatek
							   $id_stav = 5;
						   }
						   
						   					   
						   $data_update = array('zaplatila' => $castka,'sparovano' => 1,'id_stav' => $id_stav);
						   $where_update = array('id' => $data_o['id']);
						   $query = Db::update('objednavky', $data_update, $where_update);
						   
						   $radky_faktury = array();
						   $sluzby_arr_eml = array();
						   $id_sluzby_arr = array();
						   $sluzby_arr_ceny = array();
						   
						   
						   // nastavíme u objednaných služeb datumy platnosti od-do
						   // velká změna - služby mají nově prioritu, 1= nejvyšší priorita !!!
						   // dle toho se musí seřadit za sebou i s datumy - pokud se jedná o služby prodlužující platnost účtu
						   // $data_op = Db::queryAll('SELECT * FROM objednavky_polozky WHERE id_objednavky=?  ', array($data_o['id']));
						   
						   $data_op = Db::queryAll('SELECT OP.*, S.priorita
							FROM objednavky_polozky OP 
							LEFT JOIN sluzby S ON S.id=OP.id_sluzby
							WHERE OP.id_objednavky=? ORDER BY OP.prodluzuje_platnost_uctu DESC, S.priorita ASC, OP.id ASC', array($data_o['id']));
						   if($data_op !== false ) 
					       {
							   foreach ($data_op as $row_op) 
								{
									
									$radky_faktury[] = array('name' => $row_op['polozka'], 'quantity' => $row_op['mnozstvi'], 'unit_price' => $row_op['cena_ks']);
									$id_sluzby_arr[] =  strval($row_op['id']);
									
									
									
									if($row_op['prodluzuje_platnost_uctu']==1)
									{
									    // zjistíme délku služby
										$data_s = Db::queryRow('SELECT * FROM sluzby WHERE id = ?', array($row_op['id_sluzby']));
										if($data_s)
				                        {
										  // pokud se nejedná o první položku v řadě tak datum_od je konec platnosti první položky = datum_do
										  if($datum_od_p > 0)
										  {
											  $datum_od_p = $datum_do_p;
										  }
										  else
										  { 
											  // pokud se jedná o první položku tak musíme zkontrolovat jestli daná dívka nemá aktivní službu, která prodlužuje platnost účtu
											  // pokud ano tak datum startu první nově objednané služby je o sekundu vyšší než konec právě aktivní služby	
											  $data_s2 = Db::queryRow('SELECT OP.datum_do
											  FROM objednavky_polozky OP 
											  LEFT JOIN objednavky O ON O.id=OP.id_objednavky
											  WHERE O.id_registrace = ? AND O.sparovano = ? AND OP.prodluzuje_platnost_uctu = ? ORDER BY OP.datum_do DESC', array($data_o['id_registrace'],1,1));
											  if($data_s2)
											  {
											       if($data_s2['datum_do'] > time())
											       {
														// služba ještě běží
														$datum_od_p = ($data_s2['datum_do'] + 1);
												   }
												   else
												   {
														// konec staré služby
														$datum_od_p = time();
												   }
											  } 
											  else
											  {
												  $datum_od_p = time();
											  }	
											  
										  }
										  
										  $datum_do_p = ($datum_od_p + $data_s['delka_sluzby'] );
										  
										  $data_update2 = array('datum_od' => $datum_od_p,'datum_do' => $datum_do_p);
										  $where_update2 = array('id' => $row_op['id']);
										  $query2 = Db::update('objednavky_polozky', $data_update2, $where_update2);
										  
										}
										
										// pro parametr do fakturace
										$datum_do =  $datum_do_p;
									   
									}
									else
									{
									
									    // zjistíme délku služby
										$data_s = Db::queryRow('SELECT * FROM sluzby WHERE id = ?', array($row_op['id_sluzby']));
										if($data_s)
				                        {
										  
										  // zde musíme ošetřit případ, kdy je objednaná pozice na HP obsazená
										  // musíme dohledat kdy končí poslední uhrazená služba na této pozici a od té nastavit datumy od a do	
										  
										  // další změna - VIP HP a TOP jsou placené služby
										  // po spárování musíme propsat i do registrace
										  // VIP HP však můžeme propsat až tehdy kdy time() = datum_od

										  if($data_s['vip_hp']==1)
										  {
											  // služba je VIP HP
											  $data_vip = Db::queryRow('
											  SELECT OP.id_objednavky, OP.datum_od, OP.datum_do, O.id_stav
											  FROM objednavky_polozky OP 
											  LEFT JOIN objednavky O ON O.id=OP.id_objednavky
											  WHERE OP.id_sluzby = ? AND OP.id_objednavky != ? AND O.id_stav IN(2,4,5) AND O.sparovano=1 AND OP.vip_na_hp_pozice = ? AND OP.datum_do <= UNIX_TIMESTAMP() 
											  ORDER BY OP.datum_do DESC  ', 
											  array($row_op['id_sluzby'],$data_o['id'],$row_op['vip_na_hp_pozice']));
											  
											  if($data_vip)
											  {
												  $datum_od = ($data_vip['datum_do'] + 1); // přidáme jednu sekundu ke konci poslední platné služby
												  $datum_do = ($data_vip['datum_do'] + 1 + $data_s['delka_sluzby'] );
											  }
											  else
											  {
												  $datum_od = time();
												  $datum_do = (time() + $data_s['delka_sluzby'] );
											  }
											  
											  // VIP HP
												$data_update_r1 = array(
												'zvyrazneni_vip_na_hp' => 1,
												'vip_na_hp_datum_od' => $datum_od,
												'vip_na_hp_datum_do' => $datum_do,
												'vip_na_hp_pozice' => $row_op['vip_na_hp_pozice']
												);
					
												// update registrace
												$where_update_r1 = array('id' => $data_o['id_registrace']);
												$query_update_r1 = Db::update('registrace', $data_update_r1, $where_update_r1);
												
												// update badge
												if($data_o['typ_registrace']==1)
												{
													$data_kontrola_d = Db::queryRow('SELECT id, id_badge_arr FROM divky WHERE id_registrace = ?', array($data_o['id_registrace']));
													if($data_kontrola_d['id_badge_arr'] && $data_kontrola_d['id_badge_arr'] !='0')		
													{ 
														$badge_arr = unserialize($data_kontrola_d['id_badge_arr']);
														array_unshift($badge_arr, "13");					
											             
													}
													else
													{
														 // nejsou žádné stávající badge 
														 $badge_arr = array();
														 $badge_arr[] = strval("13");
													}
											 
													     $badge_sql = serialize($badge_arr);
														 $data_d_update = array('id_badge_arr' => $badge_sql);
														 $where_d_update = array('id' => $data_kontrola_d['id']);
														 $query_d = Db::update('divky', $data_d_update, $where_d_update);
												 }
											  
											  
										  }
										  elseif($data_s['top']==1)
										  {
											$datum_od = time();
											$datum_do = (time() + $data_s['delka_sluzby'] );
											
											// TOP
											// kupuje si vždy první pozici
											$data_update_r2 = array(
											'zvyrazneni_top_v_kategorii' => 1,
											'top_v_kategorii_pozice' => 1
											);
					
											// update registrace
											$where_update_r2 = array('id' => $data_o['id_registrace']);
											$query_update_r2 = Db::update('registrace', $data_update_r2, $where_update_r2);
											
											// update badge
											if($data_o['typ_registrace']==1)
											{
												$data_kontrola_d = Db::queryRow('SELECT id, id_badge_arr FROM divky WHERE id_registrace = ?', array($data_o['id_registrace']));
												if($data_kontrola_d['id_badge_arr'] && $data_kontrola_d['id_badge_arr'] !='0')		
												{ 
													$badge_arr = unserialize($data_kontrola_d['id_badge_arr']);
													array_unshift($badge_arr, "12");					
										             
												}
												else
												{
													 // nejsou žádné stávající badge 
													 $badge_arr = array();
													 $badge_arr[] = strval("12");
												}
										 
												     $badge_sql = serialize($badge_arr);
													 $data_d_update = array('id_badge_arr' => $badge_sql);
													 $where_d_update = array('id' => $data_kontrola_d['id']);
													 $query_d = Db::update('divky', $data_d_update, $where_d_update);
											 }
										  }
										  else
										  {
											$datum_od = time();
											$datum_do = (time() + $data_s['delka_sluzby'] );
										  }
											
										  
										  
										  $data_update2 = array('datum_od' => $datum_od,'datum_do' => $datum_do);
										  $where_update2 = array('id' => $row_op['id']);
										  $query2 = Db::update('objednavky_polozky', $data_update2, $where_update2);
										  
										}
									
									}

									
									$sluzby_arr_eml[] = $row_op['polozka'].' a je aktivní do: '.date('d.m.Y',$datum_do);
									$sluzby_arr_ceny[] = $row_op['polozka'].' / '.$row_op['mnozstvi'].' ks / '.$row_op['cena_mnozstvi'].' Kč  aktivní do: '.date('d.m.Y',$datum_do);
									
									
									
								}
						   }
									   
						   $this->pocet_sparovani ++;
						   
						   // generujeme fakturu a odesilame email + SMS
							
						   if($id_stav==2 || $id_stav==5)
						   {	
						    
						    $this->generovaniFaktury($data_o['id_registrace'],$data_o['cislo_objednavky'],$data_o['variabilni_symbol'],$data_o['id'],$radky_faktury,$sluzby_arr_eml,$sluzby_arr_ceny,$id_sluzby_arr,$data_o['cena_celkem'],$id_stav);
					       
					       }
					       elseif($id_stav==4)
					       {
							   // částečná úhrada
							   // odešleme SMS a email s jiným zněním
							   // negenerujeme fakturu
							   
							   
							   // odešleme email na správce systému
								$eml_admin = New Email('plaintext',false);
								$eml_admin->nastavFrom(__EMAIL_FROM__);
								$eml_admin->nastavTo(__FORM_EMAIL__);
								$eml_admin->nastavSubject('Nedoplatek u objednavky c.'.$data_o['cislo_objednavky']);
								$body_eml = 'Při spárování plateb u objednávky č.: '.$data_o['cislo_objednavky'].' byl zjištěn nedoplatek\nDetailní info zde: '.__URL__.'/admin/index.php?p=objednavky&id='.$data_o['id'].'\n\nTento email je generován automaticky systémem, neodpovídejte na něj.';
								$eml_admin->nastavBody($body_eml);
								$eml_admin_odeslani = $eml_admin->odesliEmail();
								
							   
							   $data_reg = Db::queryRow('SELECT * FROM registrace WHERE id = ?', array($data_o['id_registrace']));
							   if($data_reg)
				               {
									
								   // SMS	
								   if($data_reg['telefon'])
									{   // id = 17 = SMS sablona částečná úhrada
										$data_sms = Db::queryRow('SELECT * FROM sms_sablony WHERE id = ?', array(17));
									    if($data_sms)
									    {	
											// z tel. cisla odstranime mezery
											$telefonni_cislo = str_replace(' ','',$data_reg['telefon']);
											$sms_zprava = $data_sms['sms_cz'];
											$sms = New Sms($telefonni_cislo,$sms_zprava,$data_reg['id']);
											$sms_odeslani = $sms->odesliSMS();
											
											if($sms_odeslani)
											{
												/*echo '<div class="alert-success">V pořádku!<br>SMS byla odeslána na '.sanitize($telefonni_cislo).'<br><a href="./index.php?p='.strip_tags($_GET['p']).'&pp='.strip_tags($_GET['pp']).'">Přejít na přehled</a></div>';*/
											}
											else
											{
												echo '<div class="alert-warning">Chyba!<br>Nepodařilo se odeslat SMS<br></div>';
											}
										}
										else
										{
											echo '<div class="alert-warning">Chyba!<br>Nepodařilo se načíst šablonu SMS<br></div>';
										}
										
									}
									else
									{
										echo '<div class="alert-warning">Chyba!<br>Nepodařilo se načíst šablonu SMS<br></div>';
									}
									
									
									// email
									$data_eml = Db::queryRow('SELECT * FROM email_sablony WHERE id = ?', array(26));
								    if($data_eml)
								    {
										
										// musíme získat slug pro emailovou šablonu
										   if($data_reg['typ']==1)
										   {
											    // divka
											    $data_divka = Db::queryRow('SELECT str FROM divky WHERE id_registrace = ?', array($data_reg['id']));
												if($data_divka)
												{
												  $url_profilu = __URL__.'/divky/'.$data_divka['str'];
												}
										   }
										   elseif($data_reg['typ']==2)
										   {
											   // podnik
										   }
										   elseif($data_reg['typ']==3)
										   {
											   // nocni klub
										   }
				   
				   
										$eml = New Email('html',false);
										$eml->nastavFrom(__EMAIL_FROM__);
										$eml->nastavTo($data_reg['email']);
										$eml->nastavSubject($data_eml['subject_cz']);
										$body_eml = $data_eml['sablona_cz'];
										// nahradime 
										$body_eml = str_replace('[NAZEV_SLUZBY]',implode('<br>',$sluzby_arr_eml),$body_eml);
										$body_eml = str_replace('[URL_PROFILU]','<a href="'.$url_profilu.'">'.$url_profilu.'</a>',$body_eml);
										$eml->nastavBody($body_eml);
										$eml_odeslani = $eml->odesliEmail();
										
										if($eml_odeslani)
										{
											/*echo '<div class="alert-success">V pořádku!<br>Email byl odeslán na '.sanitize($data_reg['email']).'<br><a href="./index.php?p='.strip_tags($_GET['p']).'&pp='.strip_tags($_GET['pp']).'">Přejít na přehled</a></div>';*/
										}
										else
										{
											echo '<div class="alert-warning">Chyba!<br>Nepodařilo se odeslat email na '.$data_reg['email'].'<br></div>';
										}
										
										
									}
									else
									{
										echo '<div class="alert-warning">Chyba!<br>Nepodařilo se načíst šablonu emailu s id 15<br></div>';
									}
										    
							    
							    
							    }
							   
						   }
						   
						   
						   
						   
						   
						   
						}
						else
						{
						  // není žádná objednávka s tímto VS	
						}
					  
					  
					  }
					  
					  
					 
				 } 
				 
				 
			  }

			 
			  $r++;
			  
		    }
	 
	        return true;
				
			
		}
		else
		{
			return false;
		}
		
	}
	
  
  
	
	
	
	
}

?>
