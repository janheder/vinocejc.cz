<?php
// generování výpisů do tabulek s řazením a stránkováním
Class Vypisy
{
	private $nazev_tabulky;
	private $podminka;
	private $cely_dotaz;
	private $razeni_default;
	private $sloupce_arr;
	private $sloupce_nazvy_arr;
	private $update;
	private $insert;
	private $delete;
	private $odkaz_jinam = array();
	
	function __construct($nazev_tabulky,$podminka,$cely_dotaz,$razeni_default,$sloupce_arr,$sloupce_nazvy_arr,$update=0,$insert=0,$delete=0,$odkaz_jinam=false,$pocet_na_str=false)
	{
		$this->nazev_tabulky = $nazev_tabulky;
		$this->podminka = $podminka;
		$this->cely_dotaz = $cely_dotaz;
		$this->razeni_default = $razeni_default;
		$this->sloupce_arr = $sloupce_arr;
		$this->sloupce_nazvy_arr = $sloupce_nazvy_arr;
		$this->update = $update;
		$this->insert = $insert;
		$this->delete = $delete;
		$this->odkaz_jinam = $odkaz_jinam;
		if($pocet_na_str)
		{
			$this->pocet_na_str = $pocet_na_str;
		}
		else
		{
			$this->pocet_na_str = __STRANKOVANI_ADMIN__;
		}

		
	}
	
	private function generujZahlavi()
	{
		// první sloupec pokud je id tak nastavíme šířku na 30px;
		// poslední je vždy akce = 60px; 
		$cs = 0;

		if($_GET['ord']=='ASC'){$ord = 'DESC';}
		else{$ord = 'ASC';}
		
		if(!$_GET['razeni'] && !$this->razeni_default){$_GET['razeni']='id';}
		elseif(!$_GET['razeni'] && $this->razeni_default){$_GET['razeni']=$this->razeni_default;}
		$html .= '<tr class="tr-header">';
		
		// GET parametry
		if($_GET)
		{
			$get_parametry = '?';
			
			foreach($_GET as $get_key=>$get_val)
			{
				if($get_key!='ord' && $get_key!='razeni')
				{
				   $get_parametry .= $get_key.'='.$get_val.'&';
				} 
				 
			}
		}
 
		if(is_array($this->sloupce_nazvy_arr))
		{
			if(is_array($this->sloupce_arr))
			{
				$this->pocet_zahlavi = count($this->sloupce_nazvy_arr);		
									
				foreach($this->sloupce_nazvy_arr as $sl_key=>$sl_val)
				{
					$html .= '<td';
					if($cs==0 && mb_strtolower($sl_val)=='id' && $this->nazev_tabulky=='objednavky'){ $html .=' style="width: 50px;" ';}
					elseif($cs==0 && mb_strtolower($sl_val)=='id'){ $html .=' style="width: 30px;" ';}
					elseif($cs>0 && mb_strtolower($sl_val)=='akce'){ $html .=' style="width: 80px;" ';}	
					$html .= '>';
					if(mb_strtolower($sl_val)!='akce'){$html .= '<a href="./index.php'.$get_parametry.'razeni='.$this->sloupce_arr[$cs].'&ord='.$ord.'">'.$sl_val;
					if($ord=='DESC' && ($_GET['razeni']==$this->sloupce_arr[$cs])){$html .= '&nbsp;<i class="fas fa-arrow-up" style="color:#000000;"></i>';}
					elseif($ord=='ASC' && ($_GET['razeni']==$this->sloupce_arr[$cs])){$html .= '&nbsp;<i class="fas fa-arrow-down" style="color:#000000;"></i>';}
					$html .= '</a>';}
					else{$html .= $sl_val;}	
					$html .= '</td>';
					$cs++;
				}
			
			}
		}
		
		$html .= '</tr>';
		
		
		return $html;
		
	}
	
	private function generujStrankovani()
	{
		$html = '';
		$limit = intval($_GET['limit']);
		if(!$limit){$limit = 0;}
		 
		$this->limit = $limit;
		
		if($this->podminka){$sql_where = 'WHERE '.$this->podminka;}
		else{$sql_where = 'WHERE 1';}
		 
		if($this->cely_dotaz)
		{ 
			$pocet = Db::queryAffected($this->cely_dotaz, array()); 
		}
		else
		{
			$pocet = Db::queryAffected('SELECT id FROM '.$this->nazev_tabulky.' '.$sql_where.' ', array()); 
		}
		
		
		if($this->pocet_na_str < $pocet)
		{ // pokud je méně výsledků než je výpis na stránku tak stránkování nezobrazujeme
			$html .= '<div class="strankovani">';

			// GET parametry
			if($_GET)
			{
				$get_parametry = '?';
				
				foreach($_GET as $get_key=>$get_val)
				{
					if($get_key!='limit')
					{
					   $get_parametry .= $get_key.'='.$get_val.'&';
					} 
					 
				}
				//$get_parametry = substr($get_parametry, 0, -1);
			}

			
			for ($px=0;$px<ceil($pocet/$this->pocet_na_str);$px++)
		    {
				$html .= ' <a href="./index.php'.$get_parametry.'limit='.$limit2.'">';
				if($limit==$limit2) {$html .= '<strong class="navlink">'.($px+1).'</strong>';}
				else{$html .= ($px+1);}
				$html .='</a> ';
				$limit2 = $limit2 + $this->pocet_na_str;
			}
			$html .= '</div>';
	    }
		
		
		return $html;
		
	}
	
	
	
	
	public function generujVypis()
	{
		$html .= $this->generujStrankovani();
		$html .= '<table border="0" class="ta" cellspacing="0" cellpadding="6">';
		
		if($this->sloupce_arr)
		{
			$sql_sloupce = implode(',',$this->sloupce_arr);
			$this->pocet_sloupce_db = count($this->sloupce_arr);
			
		}
		else
		{
			$sql_sloupce = ' * ';
			$this->pocet_sloupce_db = false;
		}
		
		if($this->podminka){$sql_where = 'WHERE '.$this->podminka;}
		else{$sql_where = 'WHERE 1';}
		
		$this->sql_where = $sql_where;

		if(!$_GET['ord']){$_GET['ord']='DESC';}
		if($_GET['razeni'])
		{
			// odstranime pripadny alias 
			$razeni_arr = array_reverse(explode('.',$_GET['razeni']));
			$razeni = $razeni_arr[0];
			$sql_order = 'ORDER BY '.addslashes($razeni).' '.addslashes($_GET['ord']);
		}
		elseif($this->razeni_default){$sql_order = 'ORDER BY '.$this->razeni_default;}
		else{$sql_order = 'ORDER BY id DESC';}
		
		if($this->cely_dotaz)
		{
			$data = Db::queryAll($this->cely_dotaz.' '.$sql_order.'  LIMIT '.$this->limit.','.$this->pocet_na_str.' ', array());
			 
		}
		else
		{
			$data = Db::queryAll('SELECT '.$sql_sloupce.'  FROM '.$this->nazev_tabulky.' '.$sql_where.' '.$sql_order.'  LIMIT '.$this->limit.','.$this->pocet_na_str.' ', array());
		}
		
		
 
		
		if($data !== FALSE && $this->sloupce_arr) 
        {

			
			$html .= $this->generujZahlavi();
			
			if($this->insert==1){$html .= '<tr><td colspan="'.count($this->sloupce_nazvy_arr).'" style="text-align: right;"><a href="./index.php?p='.strip_tags($_GET['p']).'&pp='.strip_tags($_GET['pp']).'&razeni='.strip_tags($_GET['razeni']).'&ord='.strip_tags($_GET['ord']).'&action=insert">přidat záznam</a></td></tr>';}
			 
			foreach ($data as $row) 
			{
				
				$html .= '<tr '.__TR_BG__.'>';
				$ps = 0;
 
				foreach ($this->sloupce_arr as $sl_key=>$sl_value) 
				{	 
					if($this->cely_dotaz)
					{
						// odstraneni nazvu aliasu tabulky
						list($sl_value2,$sl_value1) = explode('.',$sl_value);
						list($sl2,$sl1) = explode('.',$this->sloupce_arr[$ps]);
						
						$sl_value = $sl_value1;
						$nazev_sloupce = $sl1;
	
					}
					else
					{
						$nazev_sloupce = $this->sloupce_arr[$ps];
					}
					// odstranime sloupce, ktere nechceme vypisovat, ale potrebujeme jejich data pro generovani odkazu
					// tyto sloupce zadavame vzdy na konec pole

						if($this->odkaz_jinam)
						{   

							if($this->pocet_zahlavi >= ($ps+2))
							{ 	// datumy
								if($nazev_sloupce=='datum' || $nazev_sloupce=='datum_posledniho_prihlaseni' || $nazev_sloupce=='datum_registrace' || $nazev_sloupce=='datum_reg' || $nazev_sloupce=='datum_splatnosti' || $nazev_sloupce=='datum_vystaveni' || $nazev_sloupce=='datum_od' || $nazev_sloupce=='datum_do')
								{
									$html .= '<td>';
									if($row[$sl_value] > 0) {$html .=  date('d.m.Y H:i:s',$row[$sl_value]);} // prevod datumu
									$html .='</td>';
								}
								elseif($nazev_sloupce=='aktivni')
								{
									if($row[$sl_value]=='1'){$html .= '<td><span class="r">'.$_SESSION['stav_adm_arr'][$row[$sl_value]].'</span></td>'; }
									else{$html .= '<td><span class="b">'.$_SESSION['stav_adm_arr'][$row[$sl_value]].'</span></td>'; }
								}
								elseif($this->nazev_tabulky=='stranky' && $nazev_sloupce=='typ')
								{
									$html .= '<td>'.$_SESSION['stranky_adm_typ_arr'][$row[$sl_value]].'</td>'; 
								}
								elseif($nazev_sloupce=='na_uvod')
								{
									if($row[$sl_value]=='1'){$html .= '<td><span class="r">'.$_SESSION['stav_adm_sluzby_arr'][$row[$sl_value]].'</span></td>'; }
									else{$html .= '<td><span class="b">'.$_SESSION['stav_adm_sluzby_arr'][$row[$sl_value]].'</span></td>'; }
								}
								elseif($nazev_sloupce=='id_stav')
								{
									
									if($row[$sl_value]=='2'){$html .= '<td><span class="b">'.$_SESSION['stavy_fak_arr'][$row[$sl_value]].'</span></td>'; }
									else{$html .= '<td><span class="r">'.$_SESSION['stavy_fak_arr'][$row[$sl_value]].'</span></td>'; }
								}
								elseif($this->nazev_tabulky=='objednavky' && $nazev_sloupce=='id')
							    {
									$html .= '<td><label for="o_'.$row[$sl_value].'">'.$row[$sl_value].'&nbsp;<input type="checkbox" name="ido[]" id="o_'.$row[$sl_value].'" value="'.$row[$sl_value].'" style="float: right;"></label></td>'; 
							    }
							    elseif($this->nazev_tabulky=='objednavky' && $nazev_sloupce=='CISLO_OBJ_Z')
							    {
			
									// musíme rozdělit data
									list($cislo_obj, $hodnoceni_zak) = explode('-', $row[$sl_value]);
									$html .= '<td>'.$cislo_obj.'<br><img src="./img/'.$hodnoceni_zak.'.png" title="'.$_SESSION['hodnoceni_zak_arr'][$hodnoceni_zak].'">'; 
									
									// úprava pro balíkobot
									$data_bb = Db::queryRow('SELECT * FROM balikobot WHERE cislo_obj=?  ', array($cislo_obj));
									if($data_bb['status']==200)
									{
										$html .= '<br><span title="Balík zaslán do Balíkobotu '.date('d.m.Y H:i:s',$data_bb['datum_pridani']).' ">B</span>';
									}
									
									if($data_bb['status']==200 && $data_bb['objednan_svoz']==1 && $data_bb['datum_objednani_svozu'])
									{
										$html .= '&nbsp;&nbsp;<span title="Objednán svoz '.date('d.m.Y H:i:s',$data_bb['datum_objednani_svozu']).' " style="color: green;">S</span>';
									}
									
									
									$html .= '</td>';
							    }
								elseif($this->nazev_tabulky=='objednavky' && $nazev_sloupce=='STAV')
								{
									
									   // barevně odlišené buňky u spárovaných plateb
									   // úprava z 4.1.2023 - barvy jsou definovatelné
									   // musíme rozdělit data
									   //var_dump($row[$sl_value]);
									   list($stav_obj, $barva) = explode('-', $row[$sl_value]);
									   
									   $html .= '<td style="background-color: '.$barva.'">'.$stav_obj.'</td>';
										
										
									
									
								}
								elseif($this->nazev_tabulky=='objednavky' && $nazev_sloupce=='id_stav_uhrady')
								{
									   if($row[$sl_value]==2)
									    {
											$html .= '<td class="td_zelena">'.$_SESSION['stavy_fak_arr'][$row[$sl_value]].'</td>';
										}
										else
									    {
											$html .= '<td class="td_cervena">'.$_SESSION['stavy_fak_arr'][$row[$sl_value]].'</td>';
										}
								}
								else
								{   
									$html .= '<td>'.$row[$sl_value].'</td>';
								}
							}
						}
						else
						{	
							// datumy
							if($nazev_sloupce=='datum' || $nazev_sloupce=='datum_posledniho_prihlaseni' || $nazev_sloupce=='datum_registrace' || $nazev_sloupce=='datum_reg'  || $nazev_sloupce=='datum_splatnosti' || $nazev_sloupce=='datum_vystaveni' || $nazev_sloupce=='datum_od' || $nazev_sloupce=='datum_do')
							{	$html .= '<td>';
								if($row[$sl_value] > 0) {$html .=  date('d.m.Y H:i:s',$row[$sl_value]);} // prevod datumu
								$html .='</td>';
							}
							elseif($nazev_sloupce=='aktivni')
							{
								if($row[$sl_value]=='1'){$html .= '<td><span class="r">'.$_SESSION['stav_adm_arr'][$row[$sl_value]].'</span></td>'; }
								else{$html .= '<td><span class="b">'.$_SESSION['stav_adm_arr'][$row[$sl_value]].'</span></td>'; }
							}
							elseif($this->nazev_tabulky=='divky_top_vyber' && $nazev_sloupce=='pozice')
							{
								 $html .= '<td><span class="b">'.$_SESSION['divky_vysouvaci_menu_pozice'][$row[$sl_value]].'</span></td>'; 
							}
							elseif($this->nazev_tabulky=='slevove_kody' && $nazev_sloupce=='typ')
							{
								 $html .= '<td><span class="b">'.$_SESSION['typ_sl_kod_arr'][$row[$sl_value]].'</span></td>'; 
							}
							elseif($this->nazev_tabulky=='produkty_hromadne_slevy' && $nazev_sloupce=='kategorie')
							{
								 $html .= '<td>';
								 // pole kategorií
								 if($row[$sl_value])
								 {  
									$data_k = Db::queryAll('SELECT nazev FROM kategorie WHERE id IN('.implode(',',unserialize($row[$sl_value])).')  ', array());
								    if($data_k !== false ) 
								    {  
										foreach ($data_k as $row_k) 
										{
										  $html .= $row_k['nazev'].', ';
										}
									}
								 }
								 $html .='</td>'; 
							}
							elseif($this->nazev_tabulky=='bannery' && $nazev_sloupce=='banner')
							{
							     $html .= '<td><img src="/prilohy/b/'.$row[$sl_value].'" style="width: 250px;"></td>';
							}
							elseif($nazev_sloupce=='karta' || $nazev_sloupce=='prevodem' || $nazev_sloupce=='cp_balik_na_postu' || $nazev_sloupce=='cp_balik_do_balikovny' 
							|| $nazev_sloupce=='zasilkovna' || $nazev_sloupce=='za_registraci' || $nazev_sloupce=='opakovane_pouziti')
							{
									if($row[$sl_value]=='1'){$html .= '<td><span class="r">'.$_SESSION['stav_adm_sluzby_arr'][$row[$sl_value]].'</span></td>'; }
									else{$html .= '<td><span class="b">'.$_SESSION['stav_adm_sluzby_arr'][$row[$sl_value]].'</span></td>'; }
							}	
							elseif($this->nazev_tabulky=='zakaznici' && $nazev_sloupce=='bez_registrace')
							{
								$html .= '<td><span class="b">'.$_SESSION['zakaznik_typ_arr'][$row[$sl_value]].'</span></td>';
							}
							elseif($this->nazev_tabulky=='admin_log_udalosti' && $nazev_sloupce=='url')
							{
								// odkazy
								if($row[$sl_value])
								{
									$html .= '<td><a href="'.$row[$sl_value].'" target="_blank">'.$row[$sl_value].'</a></td>';
								}
								else
								{
									// pro login
									$html .= '<td>&nbsp;</td>';
								}
							}
							else
							{
								$html .= '<td>'.$row[$sl_value].'</td>';
							}
						}
				
						
					

					
						
					
					$ps++;
					
				}
				
				if($this->update==1 || $this->delete==1 || $this->odkaz_jinam)
				{
					$html .= '<td>';
					if($this->update==1)
					{
						$html .= '&nbsp;<a href="./index.php?p='.strip_tags($_GET['p']).'&pp='.strip_tags($_GET['pp']).'&razeni='.strip_tags($_GET['razeni']).'&ord='.strip_tags($_GET['ord']).'&action=update&id='.$row['id'].'">';
						 if($this->nazev_tabulky=='objednavky' || $this->nazev_tabulky=='faktury'){$html .= 'detail';}
						 else{$html .= 'upravit';}
						$html .= '</a>';
					}
					if($this->delete==1){$html .= '&nbsp;&nbsp;&nbsp;<a href="./index.php?p='.strip_tags($_GET['p']).'&pp='.strip_tags($_GET['pp']).'&razeni='.strip_tags($_GET['razeni']).'&ord='.strip_tags($_GET['ord']).'&action=delete&id='.$row['id'].'" '.__JS_CONFIRM__.'>smazat</a>';}
					
					// odkaz jinam
					if(is_array($this->odkaz_jinam) && count($this->odkaz_jinam) > 0)
					{ 
						if($this->odkaz_jinam[1])
						{
							$html .= '&nbsp;&nbsp;&nbsp;<a href="'.$this->odkaz_jinam[2].'&'.$this->odkaz_jinam[1].'='.$row[$this->odkaz_jinam[0]].'">'.$this->odkaz_jinam[3].'</a>';
						}
						else
						{    
							// bez &
							// úprava 22.3.2021 - pokud je proměnná prázdná tak negenerujeme
							if($row[$this->odkaz_jinam[0]])
							{
								$html .= '&nbsp;&nbsp;&nbsp;<a href="'.$this->odkaz_jinam[2].$row[$this->odkaz_jinam[0]].'">'.$this->odkaz_jinam[3].'</a>';
							}
						}
						
					}
					
					$html .= '</td>';
			    }
				$html .= '</tr>';
			}
			
		} 
		
		
		$html .= '</table>';
		
		return $html;
		
	}
	
}

?>
