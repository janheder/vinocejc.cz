<?php
// trida produkty
// do tridy predavame 2 typy parametru v polich
// úprava - v adminu možno zadávat ceny s nebo bez DPH

class Produkty
{

public $parametry; // parametry za lomitky
public $get_parametry;  // klasicke GET parametry
public $skript; // nazev stranky
public $typ_ceny; // typ ceny (A,B,C,D)
	
	function __construct($skript,$parametry,$get_parametry,$typ_ceny)
	{
	 $this->skript = $skript;
	 $this->parametry = $parametry;
	 $this->get_parametry = $get_parametry;
	 $this->typ_ceny = 'cena_'.$typ_ceny;
	  
		 if($_GET['page'])
		 {
		     $this->limit = ((intval($_GET['page']) * __POCET_NAHLEDU_PRODUKTY__) - __POCET_NAHLEDU_PRODUKTY__); // pro stránkovací rutinu
	     }
	     else
	     {
			 $this->limit = 0;
		 }


	}
	
	
	public function vyhledavani()
	{
	   $nahledy = '';
	   
	   $nahledy .= '<section class="page-heading">
	        <div class="container">
	          <h1>Vyhledávání</h1>
	          <p id="pageDescription">Hledáte výraz <b>'.sanitize($_GET['searchbox']).'</b></p>
	         
	        </div>
	      
	      </section>';
	      
	      $nahledy .= '<section class="products">
	      <div class="container">';
	   
	   if($_GET['searchbox'])
	   {
	            // vyhledáváme
			    $orderby = 'ORDER BY P.id DESC';
			    $searchbox = sanitize(trim($_GET['searchbox']));
			    
			    // úprava z 17.3.2022 - odstraníme + a - z názvu
			    $searchbox = str_replace('+','',$searchbox);
			    $searchbox = str_replace('-','',$searchbox);
			    
			    $searchbox_arr = explode(' ',$searchbox);
			    
			    $sb_sql_r = array();
			    foreach( $searchbox_arr as $sb_key=>$sb_val)
			    {
				  $sb_sql_r[] = ' +'.$sb_val;
				}
				
				$sb_sql = implode(' ',$sb_sql_r);
				
				if(substr($sb_sql, -1)=='+')
				{
				   $sb_sql = substr($sb_sql, 0, -1); // pokud by nechal na konci mezeru tak SQL háže chybu
				}
				
				//var_dump($sb_sql );

			    $data_p = Db::queryAll("SELECT P.id, P.str, P.nazev, P.popis_kratky, P.akce, P.novinka, P.doporucujeme, P.doprodej, P.doprava_zdarma, P.jednotka, P.formular, D.dph, F.foto, F.popis,
			    MATCH(P.nazev) AGAINST ('". $searchbox."*') AS Relevance  
				FROM produkty P
				LEFT JOIN dph D ON D.id=P.id_dph
				LEFT JOIN produkty_foto F ON F.id_produkt=P.id
				LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
				WHERE P.aktivni=? AND V.aktivni_var=? AND F.typ=1 AND (MATCH(P.nazev) AGAINST ('". $searchbox."*' IN BOOLEAN MODE) OR MATCH(V.kat_cislo_var) AGAINST ('".$searchbox."*' IN BOOLEAN MODE))
				GROUP BY P.id 
				ORDER BY Relevance DESC LIMIT ".$this->limit.",".__POCET_NAHLEDU_PRODUKTY__." ", array(1,1));
				
				$pocet_pr = count($data_p);
				
				$pocet = Db::queryAffected("SELECT P.id, D.dph, F.foto, F.popis  
				FROM produkty P
				LEFT JOIN dph D ON D.id=P.id_dph
				LEFT JOIN produkty_foto F ON F.id_produkt=P.id
				LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
				WHERE P.aktivni=? AND V.aktivni_var=? AND F.typ=1 AND (MATCH(P.nazev) AGAINST ('". $searchbox."*' IN BOOLEAN MODE) OR MATCH(V.kat_cislo_var) AGAINST ('".$searchbox."*' IN BOOLEAN MODE))
				GROUP BY P.id", array(1,1));
				
				
				/*
				$data_p = Db::queryAll("SELECT P.id, P.str, P.nazev, P.popis_kratky, P.akce, P.novinka, P.doporucujeme, P.doprodej, P.doprava_zdarma, D.dph, F.foto, F.popis  
				  FROM produkty P 
				  LEFT JOIN dph D ON D.id=P.id_dph
				  LEFT JOIN produkty_foto F ON F.id_produkt=P.id
				  LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
				  WHERE P.aktivni=? AND V.aktivni_var=? 
				  AND (MATCH(P.nazev) AGAINST ('".$sb_sql."*' IN BOOLEAN MODE) OR MATCH(V.kat_cislo_var) AGAINST ('".$sb_sql."*' IN BOOLEAN MODE))
				  GROUP BY P.id ".$orderby." LIMIT ".$this->limit.",".__POCET_NAHLEDU_PRODUKTY__." ", array(1,1));
				  
				  
				  $pocet_pr = count($data_p);
				  
				  
				$pocet = Db::queryAffected("SELECT P.id
				FROM produkty P
				LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
				WHERE P.aktivni=? AND V.aktivni_var=? 
				 AND (MATCH(P.nazev) AGAINST ('".$sb_sql."*' IN BOOLEAN MODE) OR MATCH(V.kat_cislo_var) AGAINST ('".$sb_sql."*' IN BOOLEAN MODE))
				GROUP BY P.id", array(1,1));
					*/
				$nahledy .= $this->strankovani($pocet);
				$nahledy .= '
		          
		          <div class="products-count">Zobrazeno '.$pocet_pr.' z '.$pocet.' produktů</div>
		          <div class="products-grid">';
		          
				
				if($data_p)
				{   
					
					foreach($data_p as $row_p)
					{
						
					    // cena OD
						$data_var_cena = Db::queryRow('SELECT '.$this->typ_ceny.' AS CENA, sleva, sleva_datum_od, sleva_datum_do FROM produkty_varianty 
						WHERE id_produkt=? AND aktivni_var=? ORDER BY '.$this->typ_ceny.' ASC ', 
						array($row_p['id'],1));
						if($data_var_cena)
						{
						        // musíme zjistit počet aktivních variant kvůli prezentaci ceny
						        $pocet_v = Db::queryAffected('SELECT id FROM  produkty_varianty WHERE aktivni_var=? AND id_produkt=?  ', array(1,$row_p['id']));
						        if($pocet_v==1){$typ=1;}
						        else{$typ=2;}

							  	$ceny = $this->vypocetCeny($data_var_cena['CENA'],$row_p['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
							  	
							  	
							  	// dostupnost 
								$dostupnost = $this->dostupnostProduktu($row_p['id'],$row_p['jednotka']);
							 
								// úprava z 16.8.2021
								// zobrazení dostupnosti (zelená fajka nebo šedá křížek)
								$dostupnost_zobrazeni = $this->dostupnostZobrazeni($row_p['id']);
		
								
								$foto['foto'] =  $row_p['foto'];
								if($row_p['popis'])
								{
								     // máme popis fotky
								     $foto['popis'] = $row_p['popis'];
								}
								else
								{
									 $foto['popis'] = $row_p['nazev'];
								}
								
							    $nahledy .=  $this->produktySablona($row_p['id'],$row_p['str'],$row_p['nazev'],$row_p['popis_kratky'],$foto,$dostupnost,$dostupnost_zobrazeni,
							    $ceny['cena_s_dph'],$ceny['cena_puvodni_s_dph'],$ceny['sleva'],
							    $row_p['akce'],$row_p['novinka'],$row_p['doporucujeme'],$row_p['doprodej'],$row_p['doprava_zdarma'],$typ,$row_p['formular']); 
							    
								$pocet++; 	
						
						

						}
						/*else
						{
						  die('Není definována cena pro produkt s ID '.$row_p['id']);
						}*/
		

					}
					
		
					
					
				}
				else
				{
		
					// nemáme žádné produkty v této kategorii
					 $nahledy .=  '<br><br>Nenalezeny žádné produkty.<br><br>';
		 
				}
				
				
				$nahledy .=  '</div>';
				
				
	   }
	   else
	   {
	      $nahledy .= 'Nezadali jste žádné slovo k vyhledávání.';
	   }
	   
	   $nahledy .= '</div></section>';
	   
	   return $nahledy;
	
	}
	
	
	
	
	public function kategorie()
	{
	  $nahledy = '';
	  
	  if($this->parametry[1])
	  {
	     // získáme ID kategorie
	     $id_kat_arr_e = explode('-',$this->parametry[1]);
	     $id_kat_arr = array_reverse($id_kat_arr_e);
	     $id_kat = $id_kat_arr[0];
	     $this->id_kat = intval($id_kat);
	     
	     $_SESSION['last_category'] = $this->id_kat;
	     
	     // název + popis
	     $data_k = Db::queryRow('SELECT nazev, popis FROM kategorie WHERE id=? AND aktivni=?', array($this->id_kat,1));
		 if($data_k)
		 {
			 $nahledy .= '<section class="page-heading">
	        <div class="container">
	          <h1>'.$data_k['nazev'].'</h1>
	          <div id="pageDescription">'.StringUtils::shorten(strip_tags($data_k['popis']),250).' <span class="read-more" id="readMore">Číst více</span></div>
	          <div id="pageDescriptionFull">'.$data_k['popis'].'<span class="read-more --less" id="readLess">Skrýt popis</span></div>
	        </div>
	        <script>
	          document.getElementById(\'readMore\').onclick = function() {
              document.getElementById(\'pageDescription\').classList.toggle(\'--active\');
	          }
	          document.getElementById(\'readLess\').onclick = function() {
	              document.getElementById(\'pageDescription\').classList.toggle(\'--active\');
	          }
	          
	        </script>
	      </section>';
	      
		      // podkategorie
			 $data_pk = Db::queryAll('SELECT nazev, popis, str, id, foto FROM kategorie WHERE aktivni=? AND id_nadrazeneho = ? ORDER BY razeni ', array(1,$this->id_kat));
			 if($data_pk)
			 {
				  $nahledy .= '<section class="subcategories">
					<div class="container">
					<div class="row">';
					
				  foreach($data_pk as $row_pk)
				  {
				      
				        if($row_pk['foto']){$fotka = $row_pk['foto'];}
	                    else{$fotka = 'product-thumb-medium.jpg';}
	                    
				        $nahledy .= '<div class="col-8 col-sm-6 col-md-3">
			              <div class="subcategories__single">
			              <a class="subcategories__link" href="/kategorie/'.$row_pk['str'].'-'.$row_pk['id'].'"> 
			                  <div class="subcategories__img"><img class="lazyload" src="/img/load-symbol.svg" data-src="/fotky/kategorie/male/'.$fotka.'" 
			                  alt="'.$row_pk['nazev'].'" title="'.$row_pk['nazev'].'" width="46"  >
			                  </div>
			                  <div class="subcategories__title">'.$row_pk['nazev'].'</div>
			               </a>
			               </div>
			            </div>';
				      
				  }
				  
				        $nahledy .= '</div>
							 </div>
						   </section>';
			 }
			 
			 // kategorie - výpis produktů + řazení 
			 
		      $nahledy .= $this->kategorieVypis();
		      
		 }
		 else
		 {
			   include('./skripty/404.php');
			   exit();  
		 }
		 
		 
		 
	  
	  }
	  
	  return $nahledy;
	  
	}
	
	
	public function kategorieVypis()
	{
	
		$nahledy = '';
		
		// cena produktů v dané kategorii min - max
		if($this->id_kat)
		{
			$data_pc = Db::queryRow("SELECT P.id, min(V.cena_A) AS CENA_MIN, max(V.cena_A) AS CENA_MAX, D.dph
			FROM produkty P 
			LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
			LEFT JOIN dph D ON D.id=P.id_dph
			WHERE P.aktivni=1 AND V.aktivni_var=1 AND P.id_kat_arr LIKE '%\"".$this->id_kat."\"%'  ", array());
		    if($data_pc)
		    {
			    // dle nastavení zadávání cen
				if(__CENY_ADM__==1)
				{
				  // ceny jsou bez DPH
				    $cena_min = round($data_pc['CENA_MIN'] * ($data_pc['dph'] / 100 + 1));
					$cena_max = round($data_pc['CENA_MAX'] * ($data_pc['dph'] / 100 + 1));
				  
				}
				else
				{
					$cena_min = $data_pc['CENA_MIN'];
					$cena_max = $data_pc['CENA_MAX'];
			    }
		       
		    }
		    else
		    {
			   $cena_min = 1;
		       $cena_max = 9999;
			}
			
			// pokud máme GET ceny tak přepíšeme
			if($_GET['cena_od'])
			{
			   $cena_min = intval($_GET['cena_od']);
			}
			
			if($_GET['cena_do'])
			{
			   $cena_max = intval($_GET['cena_do']);
			}
			
		}
			

		// záhlaví s řazením
		$nahledy .= '<section class="products">
		   
        <div class="container">
          <div class="products-sort">
            <form method="get"> 
              <div class="products-sort__single">
                <input type="radio" id="sort-1" name="sort" value="1" ';
                if($_GET['sort']==1){$nahledy .=' checked ';}
                $nahledy .=' onchange="this.form.submit()" required>
                <label for="sort-1">Nejoblíbenější</label>
              </div>
              <div class="products-sort__single">
                <input type="radio" id="sort-2" name="sort" value="2" ';
                if($_GET['sort']==2 || !$_GET['sort']){$nahledy .=' checked ';}
                $nahledy .=' onchange="this.form.submit()" required>
                <label for="sort-2">Od nejnovějšího</label>
              </div>
              <div class="products-sort__single">
                <input type="radio" id="sort-3" name="sort" value="3" ';
                if($_GET['sort']==3){$nahledy .=' checked ';}
                $nahledy .=' onchange="this.form.submit()" required>
                <label for="sort-3">Od nejlevnějšího</label>
              </div>
              <div class="products-sort__single">
                <input type="radio" id="sort-4" name="sort" value="4" ';
                if($_GET['sort']==4){$nahledy .=' checked ';}
                $nahledy .=' onchange="this.form.submit()" required>
                <label for="sort-4">Od nejdražšího</label>';
              
                // zde musíme vygenerovat případné GET parametry z URL  
                if($_GET)
				{
	
					foreach($_GET as $get_key=>$get_val)
					{
						if($get_key!='page' && $get_key!='sort')
						{
						   // pro případ, kdy máme v GETu pole
						   if(is_array($get_val))
						   {
							    foreach($get_val as $get_key2=>$get_val2)
								{
								    $nahledy .= '<input type="hidden" name="'.$get_key.'[]" value="'.strip_tags($get_val2).'">';
								}
						   }
						   else
						   {
							    $nahledy .= '<input type="hidden" name="'.$get_key.'" value="'.strip_tags($get_val).'">';
						   }
	
	
						}
	
					}
				}
                
              $nahledy .= '</div>
            </form>
          </div>
          
          <div class="products-heading">
            <div class="filter" id="filter">
              <div class="filter__button" id="filterToggle">Filtrovat produkty <img src="/img/icons/filter.svg" alt="Filtr"></div>
              <div class="filter__content" id="filterContent">
                <form class="filter__form" method="get"> 
                
                  <div class="form-group --checkbox">
                    <label for="filter-stock">Produkty skladem</label>
                    <input type="checkbox" name="skladem" value="1" ';
                    if($_GET['skladem']==1){$nahledy .=' checked ';}
                    $nahledy .=' id="filter-stock">
                  </div>
                  
                  <div class="form-group --checkbox">
                    <label for="filter-delivery">Doprava zdarma</label>
                    <input type="checkbox" name="doprava_zdarma" value="1" ';
                    if($_GET['doprava_zdarma']==1){$nahledy .=' checked ';}
                     $nahledy .='id="filter-delivery">
                  </div>
                  
                  <div class="filter__item"> 
                    <p class="filter__title">Zvolit rozmezí ceny </p>
                    <div class="range-slider">
                      <div class="range-slider-wrap">
                        <input type="range" name="cena_od" id="filter-range-input-1" min="0" max="'.$cena_max.'" step="1" value="'.$cena_min.'">
                        <input type="range" name="cena_do" id="filter-range-input-2" min="'.$cena_min.'" max="'.$cena_max.'" step="1" value="'.$cena_max.'">
                      </div>
                      <div class="range-slider-value">
                        <label for="filter-range-input-1" id="filter-range-value-1">'.$cena_min.' Kč</label>
                        <label for="filter-range-input-2" id="filter-range-value-2">'.$cena_max.' Kč</label>
                      </div>
                    </div>
                  </div>
                  <div class="filter__item"> 
                    <p class="filter__title">Podle štítku  </p>
                    
                    <div class="form-group --checkbox">
                      <label for="filter-new">Novinka</label>
                      <input type="checkbox" name="novinka" value="1" ';
                      if($_GET['novinka']==1){$nahledy .=' checked ';}
                      $nahledy .=' id="filter-new">
                    </div>
                    
                    <div class="form-group --checkbox">
                      <label for="filter-sale">Akce</label>
                      <input type="checkbox" name="akce" value="1" ';
                      if($_GET['akce']==1){$nahledy .=' checked ';}
                      $nahledy .=' id="filter-sale">
                    </div>
                    
                    <div class="form-group --checkbox">
                      <label for="filter-recommended">Doporučujeme</label>
                      <input type="checkbox" name="doporucujeme" value="1" ';
                      if($_GET['doporucujeme']==1){$nahledy .=' checked ';}
                      $nahledy .=' id="filter-recommended">
                    </div>
                    
                    <div class="form-group --checkbox">
                      <label for="filter-doprodej">Doprodej</label>
                      <input type="checkbox" name="doprodej" value="1" ';
                       if($_GET['doprodej']==1){$nahledy .=' checked ';}
                      $nahledy .=' id="filter-doprodej">
                    </div>
                    
                  </div>
                  <button class="btn --fullWidth" name="filter-submit" value="'.time().'" id="filter-submit">Filtrovat <img src="/img/icons/cheveron-white.svg" alt="Filtrovat"></button>
                </form>
              </div>
            </div>
            <script>
              document.getElementById(\'filter-range-input-1\').addEventListener(\'input\', function() {
                  var i1 = document.getElementById(\'filter-range-input-1\').value ;
                  document.getElementById(\'filter-range-value-1\').innerHTML =  i1 + " Kč";
              });
              
              document.getElementById(\'filter-range-input-2\').addEventListener(\'input\', function() {
                  var i2 = document.getElementById(\'filter-range-input-2\').value ;
                  document.getElementById(\'filter-range-value-2\').innerHTML =  i2 + " Kč";
              });
            </script>';
            

          // výpis náhledů
           $nahledy .= $this->zobrazProduktyNahledy();
			  
      
      
      return $nahledy;
	
	}
	
	
	
	
	
	
	public function strankovani($pocet)
	{
		$ret = '';

		if(__POCET_NAHLEDU_PRODUKTY__ < $pocet)
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


			$ps = ceil($pocet / __POCET_NAHLEDU_PRODUKTY__);
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
	
	
	
	
	public function zobrazProduktyNahledy()
	{   
		$uvod_nahledy = '';
		$pocet = 0;
		$where = " AND P.id_kat_arr LIKE '%\"".$this->id_kat."\"%' ";
		
		if($_GET['skladem']==1)
		{
			// dostupnost
			if(__DOSTUPNOST_TYP__ == 1)
			{
			  $where .= ' AND V.id_dostupnost IN(1,9) ';
			}
			elseif(__DOSTUPNOST_TYP__ == 2)
			{
			  $where .= ' AND V.ks_skladem > 0 ';
			}
			elseif(__DOSTUPNOST_TYP__ == 3)
			{
			  $where .= ' AND V.ks_skladem > 0 ';
			}
							
		}
		
		if($_GET['doprava_zdarma']==1)
		{
			$where .= ' AND P.doprava_zdarma=1 ';
		}
		
		if($_GET['novinka']==1)
		{
			$where .= ' AND P.novinka=1 ';
		}
		
		if($_GET['akce']==1)
		{
			$where .= ' AND P.akce=1 ';
		}
		
		if($_GET['doporucujeme']==1)
		{
			$where .= ' AND P.doporucujeme=1 ';
		}
		
		if($_GET['doprodej']==1)
		{
			$where .= ' AND P.doprodej=1 ';
		}
		
		
		if(isset($_GET['cena_od']))
		{
			$where .= ' AND V.'.$this->typ_ceny.' >= '.intval($_GET['cena_od']).' ';
		}
		
		if(isset($_GET['cena_do']))
		{
			$where .= ' AND V.'.$this->typ_ceny.' <= '.intval($_GET['cena_do']).' ';
		}
		
		
		// řazení
		if($_GET['sort']==1)
		 {
			 $orderby = 'ORDER BY P.zobrazeno DESC';

		 }
		 elseif($_GET['sort']==2)
		 {
			 $orderby = 'ORDER BY P.id DESC';
		 }
		 elseif($_GET['sort']==3)
		 {
			 $orderby = 'ORDER BY P.cena_razeni ASC';
		 }
		 elseif($_GET['sort']==4)
		 {
			 $orderby = 'ORDER BY P.cena_razeni DESC';
		 }
		 else
		 {
			 // default nejnovější
			 $orderby = 'ORDER BY P.id DESC';
		 }
		 
		
		$data_p = Db::queryAll('SELECT P.id, P.str, P.nazev, P.popis_kratky, P.akce, P.novinka, P.doporucujeme, P.doprodej, P.doprava_zdarma, P.jednotka, P.formular, D.dph, F.foto, F.popis  
		FROM produkty P
		LEFT JOIN dph D ON D.id=P.id_dph
		LEFT JOIN produkty_foto F ON F.id_produkt=P.id
		LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
		WHERE P.aktivni=? AND F.typ=1 AND V.aktivni_var=? '.$where.'
		GROUP BY P.id 
		'.$orderby.' LIMIT '.$this->limit.','.__POCET_NAHLEDU_PRODUKTY__.' ', array(1,1));
		
		$pocet_pr = count($data_p);
		
		$pocet = Db::queryAffected('SELECT P.id, D.dph, F.foto, F.popis  
		FROM produkty P
		LEFT JOIN dph D ON D.id=P.id_dph
		LEFT JOIN produkty_foto F ON F.id_produkt=P.id
		LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
		WHERE P.aktivni=? AND F.typ=1 AND V.aktivni_var=? '.$where.'
		GROUP BY P.id', array(1,1));

		$nahledy .= $this->strankovani($pocet);
		$nahledy .= '</div>
          
          <div class="products-count">Zobrazeno '.$pocet_pr.' z '.$pocet.' produktů</div>
          <div class="products-grid">';
		
		if($data_p)
		{   

			foreach($data_p as $row_p)
			{
				
			    // cena OD
				$data_var_cena = Db::queryRow('SELECT '.$this->typ_ceny.' AS CENA, sleva, sleva_datum_od, sleva_datum_do 
				FROM produkty_varianty WHERE id_produkt=? AND aktivni_var=? ORDER BY '.$this->typ_ceny.' ASC ', array($row_p['id'],1));
				if($data_var_cena)
				{
				        // musíme zjistit počet aktivních variant kvůli prezentaci ceny
				        $pocet_v = Db::queryAffected('SELECT id FROM  produkty_varianty WHERE aktivni_var=? AND id_produkt=?  ', array(1,$row_p['id']));
				        if($pocet_v==1){$typ=1;}
				        else{$typ=2;}
					  	
					  	$ceny = $this->vypocetCeny($data_var_cena['CENA'],$row_p['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
					  	
					  	// dostupnost 
						$dostupnost = $this->dostupnostProduktu($row_p['id'],$row_p['jednotka']);
						
						// úprava z 16.8.2021
						// zobrazení dostupnosti (zelená fajka nebo šedá křížek)
						$dostupnost_zobrazeni = $this->dostupnostZobrazeni($row_p['id']);
						
						$foto['foto'] =  $row_p['foto'];
						if($row_p['popis'])
						{
						     // máme popis fotky
						     $foto['popis'] = $row_p['popis'];
						}
						else
						{
							 $foto['popis'] = $row_p['nazev'];
						}
						
					    $nahledy .=  $this->produktySablona($row_p['id'],$row_p['str'],$row_p['nazev'],$row_p['popis_kratky'],$foto,$dostupnost,$dostupnost_zobrazeni,
					    $ceny['cena_s_dph'],$ceny['cena_puvodni_s_dph'],$ceny['sleva'],
					    $row_p['akce'],$row_p['novinka'],$row_p['doporucujeme'],$row_p['doprodej'],$row_p['doprava_zdarma'],$typ,$row_p['formular']); 
			    
				}
				/*
				else
				{
				  die('Není definována cena pro produkt s ID '.$row_p['id']);
				}*/


				
			}

			
		}
		else
		{

			// nemáme žádné produkty v této kategorii
			 $nahledy .=  '<br><br>V této kategorii nejsou aktuálně žádné produkty.<br><br>';
 
		}
		
		
		  $nahledy .= '</div>
          <div class="products-footer">';
          $nahledy .= $this->strankovani($pocet);
          $nahledy .= '</div>
        </div>
      </section>';
		
		return $nahledy;
	}
	
	
	public function zobrazProduktyNovinky()
	{ 
		$uvod_nahledy = '';
		$foto = array();
		$data_uvod = Db::queryAll('SELECT P.id, P.str, P.nazev, P.popis_kratky, P.akce, P.novinka, P.doporucujeme, P.doprodej, P.doprava_zdarma, P.jednotka, P.formular, D.dph, F.foto, F.popis  
		FROM produkty P
		LEFT JOIN dph D ON D.id=P.id_dph
		LEFT JOIN produkty_foto F ON F.id_produkt=P.id
		WHERE (P.novinka=? OR P.akce=?) AND F.typ=1 AND P.aktivni=? 
		GROUP BY P.id 
		ORDER BY RAND() LIMIT '.__NOVINKY_UVOD__.' ', array(1,1,1));
		if($data_uvod)
		{   
			
			foreach($data_uvod as $row_uvod)
			{
				// cena OD
				$data_var_cena = Db::queryRow('SELECT '.$this->typ_ceny.' AS CENA, sleva, sleva_datum_od, sleva_datum_do 
				FROM produkty_varianty WHERE id_produkt=? AND aktivni_var=? ORDER BY '.$this->typ_ceny.' ASC ', array($row_uvod['id'],1));
				if($data_var_cena)
				{
				        // musíme zjistit počet aktivních variant kvůli prezentaci ceny
				        $pocet_v = Db::queryAffected('SELECT id FROM produkty_varianty WHERE aktivni_var=? AND id_produkt=?  ', array(1,$row_uvod['id']));
				        if($pocet_v==1){$typ=1;}
				        else{$typ=2;}
					  	
					  	$ceny = $this->vypocetCeny($data_var_cena['CENA'],$row_uvod['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
					  	
	  					// dostupnost 
						$dostupnost = $this->dostupnostProduktu($row_uvod['id'],$row_uvod['jednotka']);
						
						// úprava z 16.8.2021
						// zobrazení dostupnosti (zelená fajka nebo šedá křížek)
						$dostupnost_zobrazeni = $this->dostupnostZobrazeni($row_uvod['id']);
						
						$foto['foto'] =  $row_uvod['foto'];
						if($row_uvod['popis'])
						{
						     // máme popis fotky
						     $foto['popis'] = $row_uvod['popis'];
						}
						else
						{
							 $foto['popis'] = $row_uvod['nazev'];
						}
						
					    $uvod_nahledy .=  $this->produktySablona($row_uvod['id'],$row_uvod['str'],$row_uvod['nazev'],$row_uvod['popis_kratky'],$foto,$dostupnost,$dostupnost_zobrazeni,
					    $ceny['cena_s_dph'],$ceny['cena_puvodni_s_dph'],$ceny['sleva'],
					    $row_uvod['akce'],$row_uvod['novinka'],$row_uvod['doporucujeme'],$row_uvod['doprodej'],$row_uvod['doprava_zdarma'],$typ,$row_uvod['formular']); 
			    
			    
			    
				}
				/*else
				{
				  die('Není definována cena pro produkt s ID '.$row_uvod['id']);
				}*/

				

				
			}
			
			return $uvod_nahledy;

		}
	}
	
	
	
	public function zobrazSouvisejici($id_pr,$id_kat)
	{  
		$uvod_nahledy = '';
		$foto = array();
		$data_uvod = Db::queryAll("SELECT P.id, P.str, P.nazev, P.popis_kratky, P.akce, P.novinka, P.doporucujeme, P.doprodej, P.doprava_zdarma, P.jednotka, P.formular, D.dph, F.foto, F.popis  
		FROM produkty P
		LEFT JOIN dph D ON D.id=P.id_dph
		LEFT JOIN produkty_foto F ON F.id_produkt=P.id
		WHERE P.id_kat_arr LIKE '%\"".$id_kat."\"%'  AND F.typ=1 AND P.id!=? AND P.aktivni=? 
		GROUP BY P.id 
		ORDER BY RAND() LIMIT ".__POCET_SOUVISEJICI__." ", array($id_pr,1));
		if($data_uvod)
		{   
			
			foreach($data_uvod as $row_uvod)
			{
				// cena OD
				$data_var_cena = Db::queryRow('SELECT '.$this->typ_ceny.' AS CENA, sleva, sleva_datum_od, sleva_datum_do FROM produkty_varianty WHERE id_produkt=? AND aktivni_var=? ORDER BY '.$this->typ_ceny.' ASC ', array($row_uvod['id'],1));
				if($data_var_cena)
				{
				        // musíme zjistit počet aktivních variant kvůli prezentaci ceny
				        $pocet_v = Db::queryAffected('SELECT id FROM produkty_varianty WHERE aktivni_var=? AND id_produkt=?  ', array(1,$row_uvod['id']));
				        if($pocet_v==1){$typ=1;}
				        else{$typ=2;}
					  	
					  	$ceny = $this->vypocetCeny($data_var_cena['CENA'],$row_uvod['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
					  	
					  	// dostupnost
						$dostupnost = $this->dostupnostProduktu($row_uvod['id'],$row_uvod['jednotka']);
						
						// úprava z 16.8.2021
						// zobrazení dostupnosti (zelená fajka nebo šedá křížek)
						$dostupnost_zobrazeni = $this->dostupnostZobrazeni($row_uvod['id']);
						
						$foto['foto'] =  $row_uvod['foto'];
						if($row_uvod['popis'])
						{
						     // máme popis fotky
						     $foto['popis'] = $row_uvod['popis'];
						}
						else
						{
							 $foto['popis'] = $row_uvod['nazev'];
						}
						
					    $uvod_nahledy .=  $this->produktySablona($row_uvod['id'],$row_uvod['str'],$row_uvod['nazev'],$row_uvod['popis_kratky'],$foto,$dostupnost,$dostupnost_zobrazeni,
					    $ceny['cena_s_dph'],$ceny['cena_puvodni_s_dph'],$ceny['sleva'],
					    $row_uvod['akce'],$row_uvod['novinka'],$row_uvod['doporucujeme'],$row_uvod['doprodej'],$row_uvod['doprava_zdarma'],$typ,$row_uvod['formular']); 
					    
			    
				}
				/*else
				{
				  die('Není definována cena pro produkt s ID '.$row_uvod['id']);
				}*/

				
				
				
			}
			
			return $uvod_nahledy;

		}
	}
	
	
	
	
	
	public function zobrazPrislusenstvi($prislusenstvi_id_arr_ser)
	{  
		$uvod_nahledy = '';
		$foto = array();
		$prislusenstvi_id_arr = unserialize($prislusenstvi_id_arr_ser);

		$data_uvod = Db::queryAll("SELECT P.id, P.str, P.nazev, P.popis_kratky, P.akce, P.novinka, P.doporucujeme, P.doprodej, P.doprava_zdarma, P.jednotka, P.formular, D.dph, F.foto, F.popis  
		FROM produkty P
		LEFT JOIN dph D ON D.id=P.id_dph
		LEFT JOIN produkty_foto F ON F.id_produkt=P.id
		WHERE P.id IN(".implode(',',$prislusenstvi_id_arr).") AND F.typ=1 AND P.aktivni=? 
		GROUP BY P.id 
		ORDER BY P.id DESC", array(1));
		if($data_uvod)
		{   
			
			foreach($data_uvod as $row_uvod)
			{
				// cena OD
				$data_var_cena = Db::queryRow('SELECT '.$this->typ_ceny.' AS CENA, sleva, sleva_datum_od, sleva_datum_do FROM produkty_varianty 
				WHERE id_produkt=? AND aktivni_var=? ORDER BY '.$this->typ_ceny.' ASC ', array($row_uvod['id'],1));
				if($data_var_cena)
				{
				        // musíme zjistit počet aktivních variant kvůli prezentaci ceny
				        $pocet_v = Db::queryAffected('SELECT id FROM produkty_varianty WHERE aktivni_var=? AND id_produkt=?  ', array(1,$row_uvod['id']));
				        if($pocet_v==1){$typ=1;}
				        else{$typ=2;}

					  	
					  	$ceny = $this->vypocetCeny($data_var_cena['CENA'],$row_uvod['dph'],$data_var_cena['sleva'],$data_var_cena['sleva_datum_od'],$data_var_cena['sleva_datum_do']);
					  	
					  	
					  	// dostupnost
						$dostupnost = $this->dostupnostProduktu($row_uvod['id'],$row_uvod['jednotka']);
						
						// úprava z 16.8.2021
						// zobrazení dostupnosti (zelená fajka nebo šedá křížek)
						$dostupnost_zobrazeni = $this->dostupnostZobrazeni($row_uvod['id']);
						
						$foto['foto'] =  $row_uvod['foto'];
						if($row_uvod['popis'])
						{
						     // máme popis fotky
						     $foto['popis'] = $row_uvod['popis'];
						}
						else
						{
							 $foto['popis'] = $row_uvod['nazev'];
						}
						
					    $uvod_nahledy .=  $this->produktySablona($row_uvod['id'],$row_uvod['str'],$row_uvod['nazev'],$row_uvod['popis_kratky'],$foto,$dostupnost,$dostupnost_zobrazeni,
					    $ceny['cena_s_dph'],$ceny['cena_puvodni_s_dph'],$ceny['sleva'],
					    $row_uvod['akce'],$row_uvod['novinka'],$row_uvod['doporucujeme'],$row_uvod['doprodej'],$row_uvod['doprava_zdarma'],$typ,$row_uvod['formular']); 
					    
			    
				}
				/*else
				{
				  die('Není definována cena pro produkt s ID '.$row_uvod['id']);
				}*/

				
				
				
			}
			
			return $uvod_nahledy;

		}
	}
	
	
	
	public function topProduktyAktuality()
	{
	
	}
	
	
	public function infoProduktDetail($idp)
	{
	  // funkce vrací v poli ID, název, cenu pro FB pixel
	  if($this->parametry[1])
	  {
		     
		        $data_p = Db::queryRow('SELECT P.*, D.dph
				FROM produkty P 
				LEFT JOIN dph D ON D.id=P.id_dph
				WHERE P.aktivni=1 AND P.id=? ', array($idp));
			    if($data_p)
			    {
					// cena první varianty
					$data_var = Db::queryRow('SELECT V.*, V.'.$this->typ_ceny.' AS CENA
					FROM produkty_varianty V
					WHERE id_produkt=? AND aktivni_var=? ORDER BY id ASC', array($idp,1));
					
					// cena
				  	$ceny = $this->vypocetCeny($data_var['CENA'],$data_p['dph'],$data_var['sleva'],$data_var['sleva_datum_od'],$data_var['sleva_datum_do']);
				   
				   $ret_arr = array();
				   $ret_arr['id'] = $idp.'_'.$data_var['id'];
				   $ret_arr['nazev'] = $data_p['nazev'];
				   $ret_arr['cena'] = $ceny['cena_s_dph'];
				}
				else
				{
				  $ret_arr = false;
				}
				
				return $ret_arr;
	   }
    }
	
	
	
	public function zobrazProduktDetail()
	{ 
		
	  $ret = '';
	  	
	  if($this->parametry[1])
	  {
	     // získáme ID 
	     $id_pr_arr_e = explode('-',$this->parametry[1]);
	     $id_pr_arr = array_reverse($id_pr_arr_e);
	     $id_pr= intval($id_pr_arr[0]);
	     
	        $data_p = Db::queryRow('SELECT P.*, D.dph
			FROM produkty P 
			LEFT JOIN dph D ON D.id=P.id_dph
			WHERE P.aktivni=1 AND P.id=?    ', array($id_pr));
		    if($data_p)
		    {
  
				// navýšíme návštěvnost
				$result_navstevnost = Db::updateS('UPDATE produkty SET zobrazeno=zobrazeno+1 WHERE id= "'.intval($id_pr).'" ');
				
				 $ret .= '<section class="product-detail">
			        <div class="container">
			      
					<div class="product-detail__top">
			              <div class="product-detail__photos">
			                <div class="product-card__tags">';
			                
			                
			                 if($data_p['akce']==1)
							 {	
			                   $ret .= '<div class="product-card__tagSingle">
			                    <div class="tag --sale">Akce</div>
			                  </div>';
						     }
			                  
			                 if($data_p['novinka']==1)
							 {	
			                   $ret .= '<div class="product-card__tagSingle">   
			                    <div class="tag --new">Novinka</div>
			                  </div>';
						     }
			                  
			                 if($data_p['doporucujeme']==1)
							 {	
			                   $ret .= '<div class="product-card__tagSingle"> 
			                    <div class="tag --recommended">Doporučené</div>
			                  </div>';
						     }
			                  
			                 if($data_p['doprodej']==1)
							 {	
			                   $ret .= '<div class="product-card__tagSingle"> 
			                    <div class="tag --top">Doprodej</div>
			                  </div>';
						     }
			                  
			                $ret .= '</div>
			                <div class="product-card__tagsRight">';
			                
			  
			                 if($data_p['doprava_zdarma']==1)
							 {	
			                   $ret .= '<div class="product-card__tagSingle"> 
			                    <div class="tag --free-delivery">Doprava zdarma</div>
			                  </div>';
						     }
						     
						     // sleva 
						     // pokud je alespoň jedna aktivní varianta ve slevě tak zobrazíme atribut
						     $data_var_sleva = Db::queryRow('SELECT sleva
							 FROM produkty_varianty WHERE id_produkt=? AND aktivni_var=? AND sleva>0 AND sleva_datum_od<='.time().' AND sleva_datum_do>='.time().'    ', array($id_pr,1));
							 	
						     if($data_var_sleva['sleva']>0)
							 {
						      $ret .= '<div class="product-card__tagSingle"> 
								<div class="tag --circleSale">-'.$data_var_sleva['sleva'].'%</div>
							  </div>';	
						     }
			                  
			                $ret .= '</div>
			                <div class="product-detail__carousel" id="product-carousel">';
			                
			                // fotky
			                $fotky_arr = array();
			                $data_foto = Db::queryAll('SELECT * FROM produkty_foto WHERE id_produkt=? ORDER BY typ DESC, razeni ', array($id_pr));
							if($data_foto)
							{   
								
								foreach($data_foto as $row_foto)
								{
								  if($row_foto['popis']){$popis = $row_foto['popis']; }
								  else{$popis = $data_p['nazev'];}
								  $fotky_arr[$row_foto['foto']] = $popis;
								}
							}
			                  
			                  
			                  // galerie
			                  if(is_array($fotky_arr))
			                  {
							  
							      foreach($fotky_arr as $f_key => $f_val)
							      {
								      $ret .= '<div class="product-detail__carouselItem">
					                    <div class="product-detail__carouselLink"><img src="/fotky/produkty/velke/'.$f_key.'" alt="'.$f_val.'" title="'.$f_val.'" loading="lazy">
					                    <span>'.$f_val.'</span></div>
					                  </div>';
								  }
							  
							  }
 
			                  
			               $ret .= '</div>
			                <button class="prev" aria-label="Zpět"><img src="/img/icons/cheveron.svg" alt="Zpět"></button>
			                <button class="next" aria-label="Vpřed"><img src="/img/icons/cheveron.svg" alt="Vpřed"></button>
			                <div class="lightbox" id="lightbox"></div>
			                <div class="product-detail__thumbs">';
			                 
			                 
			                  if(is_array($fotky_arr))
			                  {
							      $fx = 0;
							      foreach($fotky_arr as $f_key => $f_val)
							      {
									  $ret .= '<div class="product-detail__thumbWrap">
					                    <div class="product-detail__thumb" id="thumb'.$fx.'">
					                      <div class="product-detail__thumbInner"><img src="/fotky/produkty/stredni/'.$f_key.'" alt="'.$f_val.'"></div>
					                    </div>
					                  </div> ';
					                  
					                  $fx++;
								  }
							  }
 
			                
			                 $ret .= '</div>
			              </div>

			              <div class="product-detail__info">
			                <h1 class="product-detail__title">'.$data_p['nazev'].'</h1>';
			                
			                // musíme zjistit jesli má produkt varianty
			                $data_var_pocet = Db::queryAffected('SELECT id FROM produkty_varianty WHERE id_produkt=? AND aktivni_var=? ', array($id_pr,1));
			                
			                $ret .= '<div class="product-detail__code">';
			                
			                if($data_var_pocet > 1)
			                {
								$ret .= '<div>Kód produktu: <span id="product-code">Zvolte variantu</span></div>';
							
							}
							else
							{
								// jen jedna varianta ****************************************************************
								
								$data_var = Db::queryRow('SELECT V.*, V.'.$this->typ_ceny.' AS CENA, D.dostupnost, D.zobrazeni 
								FROM produkty_varianty V
								LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
								WHERE id_produkt=? AND aktivni_var=? ', array($id_pr,1));
					 
								// cena
							  	$ceny = $this->vypocetCeny($data_var['CENA'],$data_p['dph'],$data_var['sleva'],$data_var['sleva_datum_od'],$data_var['sleva_datum_do']);

								$ret .= '<div>Kód produktu: <span id="product-code">'.$data_var['kat_cislo_var'].'</span></div>';
							}
			                  
			                  
			                  $ret .= '<a href="https://www.facebook.com/sharer/sharer.php?u='.__URL__.$_SERVER['REQUEST_URI'].'" target="_blank"><img src="/img/icons/share.svg" alt="Sdílet"> Sdílet</a>
			                </div>
			                <p class="product-detail__text">'.$data_p['popis_kratky'].'... <a href="#productDescription">Číst více</a></p>
			                <form class="product-detail__form" method="post">';
			                
			                if($data_var_pocet > 1 && $data_p['formular']!=1)
			                {
			                 
			                  $ret .= '<div class="variants" id="variants">
			                    <div class="variants__title">Výběr varianty</div>
			                    <div class="variants__list-wrap">
			                      <div class="variants__list">';
									 
									 // naplníme cenou nejnižší varianty
			                          $data_var = Db::queryRow('SELECT V.*, V.'.$this->typ_ceny.' AS CENA, D.dostupnost 
									  FROM produkty_varianty V
								      LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
								      WHERE id_produkt=? AND aktivni_var=? ORDER BY V.'.$this->typ_ceny.' ASC LIMIT 1 ', array($id_pr,1));
								      
								    // cena
									$ceny = $this->vypocetCeny($data_var['CENA'],$data_p['dph'],$data_var['sleva'],$data_var['sleva_datum_od'],$data_var['sleva_datum_do']);

									// úprava z 17.8.2021
									// pokud je cena původní stejná jako cena tak negenerujeme do formuláře
									if($ceny['cena_puvodni_s_dph'] > $ceny['cena_s_dph'])
									{
									    $cena_p = 'od '.$ceny['cena_puvodni_s_dph'].' '.__MENA__;
									}
									else
									{
										$cena_p = '';
									}
									
			                        $ret .= '<div class="variants__item default';
			                        // úprava pro heureku
			                        if(!$_GET['var']){ $ret .= ' --active';}
			                        $ret .= '" data-code="Zvolte variantu" 
			                        data-stockstatus="default" 
			                        data-stock="Zvolte variantu" 
			                        data-priceold="'.$cena_p.'" 
			                        data-price="od '.$ceny['cena_s_dph'].' '.__MENA__.'" 
			                        data-pricevat="od '.$ceny['cena_bez_dph'].' '.__MENA__.'"> 
			                          <input type="radio" name="variant-empty" id="variantId" value="" aria-label="Zvolte variantu" required>
			                          <div class="variants__item-text">Zvolte variantu</div>
			                        </div>';
			                        
									 // varianty
									 $data_var2 = Db::queryAll('SELECT V.*, V.'.$this->typ_ceny.' AS CENA, D.dostupnost, D.zobrazeni
									  FROM produkty_varianty V
									  LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
									  WHERE id_produkt=? AND aktivni_var=? ORDER BY V.'.$this->typ_ceny.' ASC ', array($id_pr,1));
									 if($data_var2)
									 {   
									     $vx = 1;
									     $varianty2 = array();
								         foreach($data_var2 as $row_var2)
								         {
											 
											// výpis variant ve smyčce 
											
											// cena
										  	$ceny2 = $this->vypocetCeny($row_var2['CENA'],$data_p['dph'],$row_var2['sleva'],$row_var2['sleva_datum_od'],$row_var2['sleva_datum_do']);
										  	
										  	
										  	// dostupnost - nejdříve musíme zjistit jaké řešení dostupnosti má nastaveno
											if(__DOSTUPNOST_TYP__ == 1)
											{
											   // textová dostupnost dle výběru ve variantách
											   $dostupnost = $row_var2['dostupnost'];
											   $zobrazeni = $row_var2['zobrazeni'];
											}
											elseif(__DOSTUPNOST_TYP__ == 2)
											{
											     // počty kusů 
												 if($row_var2['ks_skladem']<1)
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
													$dostupnost = $row_var2['ks_skladem'].' '.$data_p['jednotka'];
													$zobrazeni = $row_var2['zobrazeni'];
												 } 
											     
											  
											}
											elseif(__DOSTUPNOST_TYP__ == 3)
											{
											     // textová dle počtu kusů od - do
												 if($row_var2['ks_skladem']<1)
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
												  $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE ks_od<=? AND ks_do>=?  ', array($row_var2['ks_skladem'],$row_var2['ks_skladem']));
											      $dostupnost = $data_var_dostupnost2['dostupnost'];
											      $zobrazeni = $data_var_dostupnost2['zobrazeni'];
											     }
											}
											
											if($zobrazeni==1)
											{
											    $zobrazeni = 'active';
											}
											else
											{
												$zobrazeni = 'disabled';
											}
											
											// úprava z 17.8.2021
											// pokud je cena původní stejná jako cena tak negenerujeme do formuláře
											if($ceny2['cena_puvodni_s_dph'] > $ceny2['cena_s_dph'])
											{
											    $cena_p2 = $ceny2['cena_puvodni_s_dph'].' '.__MENA__;
											}
											else
											{
												$cena_p2 = '';
											}
							  	
					                        $ret .= '<div class="variants__item';
					                        // úprava pro heureku
					                        if($_GET['var']==$row_var2['id']){ $ret .= ' --active';}
					                        $ret .='" 
					                        data-code="'.$row_var2['kat_cislo_var'].'" 
					                        data-stockstatus="'.$zobrazeni.'" 
					                        data-stock="'.$dostupnost.'" 
					                        data-priceold="'.$cena_p2.'" 
					                        data-price="'.$ceny2['cena_s_dph'].' '.__MENA__.'" 
					                        data-pricevat="'.$ceny2['cena_bez_dph'].' '.__MENA__.'"> 
					                          <input type="radio" name="varianta" id="variantId'.$vx.'" value="'.$row_var2['id'].'" 
					                          aria-label="'.$row_var2['nazev_var'].'" required>
					                          <img src="/fotky/produkty/male/'.$row_var2['foto_var'].'" alt="'.$row_var2['nazev_var'].'" data-src="/fotky/produkty/velke/'.$row_var2['foto_var'].'">
					                          <div class="variants__item-text">'.$row_var2['nazev_var'].'</div>
					                        </div>';

					                        
					                        
					                        $vx++;
										 }
									 }	  
									 	
			                         
			                        
		

			                        
			                      $ret .= '</div>
			                    </div>
			                  </div>';
			                  
			                  $ret .= '<div class="product-detail__stock" id="stock" data-status="default">Pro dostupnost zvolte variantu    </div>';
			                  $ret .= '
			                  <div class="product-detail__price"> 
			                    <div class="product-detail__priceMain">';
			                    $ret .='<span id="price-old">';
			                     if($ceny['cena_puvodni_s_dph'] > $ceny['cena_s_dph'])
			                     {
			                        $ret .= 'od '.$ceny['cena_puvodni_s_dph'].' '.__MENA__;
								 } 
								 $ret .='</span>';
			                     
			                     $ret .= '<span id="price-main">od '.$ceny['cena_s_dph'].' '.__MENA__.'</span></div>
			                    <div class="product-detail__priceVAT"><span id="price-vat">od '.$ceny['cena_bez_dph'].' '.__MENA__.'</span> bez DPH</div>
			                  </div>';

						    }
						    else
						    {
								// jen jedna varianta ****************************************************************

								// dostupnost - nejdříve musíme zjistit jaké řešení dostupnosti má nastaveno
								if(__DOSTUPNOST_TYP__ == 1)
								{
								   // textová dostupnost dle výběru ve variantách
								   $dostupnost = $data_var['dostupnost'];
								   $zobrazeni = $data_var['zobrazeni'];
								}
								elseif(__DOSTUPNOST_TYP__ == 2)
								{
								     // počty kusů 
									 if($data_var['ks_skladem']<1)
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
										$dostupnost = $data_var['ks_skladem'].' '.$data_p['jednotka'];
										$zobrazeni = $data_var['zobrazeni'];
									 } 
								     
								  
								}
								elseif(__DOSTUPNOST_TYP__ == 3)
								{
								     // textová dle počtu kusů od - do
								     if($data_var['ks_skladem']<1)
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
										$data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE ks_od<=? AND ks_do>=?  ', array($data_var['ks_skladem'],$data_var['ks_skladem']));
										$dostupnost = $data_var_dostupnost2['dostupnost'];
										$zobrazeni = $data_var_dostupnost2['zobrazeni'];
									 }
								}
		 
								if($zobrazeni==1)
								{
									$ret .= '<div class="product-detail__stock" id="stock" data-status="active">'.$dostupnost.'</div>';
								}
								else
								{
									$ret .= '<div class="product-detail__stock" id="stock" data-status="disabled">'.$dostupnost.'</div>';
								}
								
								$ret .= '
				                  <div class="product-detail__price">';
				                  
				                   // u formu 1 skryjeme cenu
				                    if($data_p['formular']!=1)
			                        { 
					                    $ret .= '<div class="product-detail__priceMain">';
					                    
					                    if($ceny['cena_puvodni_s_dph'] > $ceny['cena_s_dph'])
					                    {
					                        $ret .= '<span id="price-old">'.$ceny['cena_puvodni_s_dph'].' '.__MENA__.'</span> ';
									    }
									    
									    $ret .= '<span id="price-main">'.$ceny['cena_s_dph'].' '.__MENA__.'</span>';
					                    
					                    $ret .= '</div>
					                    <div class="product-detail__priceVAT"><span id="price-vat">'.$ceny['cena_bez_dph'].' '.__MENA__.'</span> bez DPH</div>';
								    }
				                  $ret .= '</div>';
				                  
				                  $ret .= '<input type="hidden" name="varianta" value="'.$data_var['id'].'" >';
							}
			                  
			                  
			                  
			                   $ret .= '<div class="product-detail__cta">';
			                   
			                    if($data_p['formular']==1)
			                    {
									 $ret .= '<div class="product-detail__button"> 
									 <div class="btn --green" data-micromodal-trigger="modal-poptat"><img src="/img/icons/cart-white.svg" alt="Poptat produkt"> Poptat produkt</div>
									 </div>';
								}
								else
								{
									$ret .= '<div class="product-detail__qty stepper" id="qty-stepper"> <span class="minus">–</span>
				                      <input id="qty-stepper-input" type="number" name="pocet" value="1" min="1" max="100" step="1" aria-label="Počet položek" required><span class="plus">+</span>
				                    </div>
				                    <div class="product-detail__button"> 
				                      <input type="hidden" name="produkt" value="'.$id_pr.'" >
				                      <input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
				                      <button class="btn --green" name="add-to-cart"><img src="/img/icons/cart-white.svg" alt="Vložit do košíku"> Vložit do košíku</button>
				                    </div>';
								} 
			                    
			                  $ret .= '</div>
			                </form>
			                
			              </div>
			            </div>
						<div class="row">
			            <div class="col-12 col-lg-7">
			              <div class="product-detail__description" id="productDescription">
			                <h2>Popis produktu</h2>
			                <p>'.$data_p['popis'].'</p>
			              </div>
			            </div>
			            <div class="col-12 col-lg-1"></div>
			            <div class="col-12 col-lg-4">
			              <div class="product-detail__parameters">';
			              
			              // parametry pokud jsou
			              $data_par = Db::queryAll('SELECT * FROM produkty_tech_par WHERE id_produkt=? ORDER BY id ASC ', array($id_pr));
							if($data_par)
							{   
								$ret .= '<h2>Parametry</h2>
								<table> 
								<tbody>'; 
								
								foreach($data_par as $row_par)
								{
									$ret .= '<tr>
			                        <td>'.$row_par['nazev'].'</td>
			                        <td>'.$row_par['hodnota'].'</td>
			                        </tr> ';
								}
								
								$ret .= '</tbody>
								</table>';
							}
							
			                
			              $ret .= '</div>
			            </div>
			          </div>';
			      
			      			// přílohy
						    $data_prilohy = Db::queryAll('SELECT * FROM produkty_prilohy WHERE id_produkt=?  ', array($id_pr));
								 if($data_prilohy)
								 {   
									 
									 $ret .= '<div class="product-detail__files-wrap">
										<h3>Přílohy</h3>
										<div class="product-detail__files">';

							         foreach($data_prilohy as $row_prilohy)
							         {
										 if($row_prilohy['nazev'])
										 {
										    $nazev_p = $row_prilohy['nazev'];
										 }
										 else
										 {
											$nazev_p = $row_prilohy['priloha'];
										 }
										 
									     $ret .= '<a href="/prilohy/'.$row_prilohy['priloha'].'" download >
											<img src="/img/icons/download.svg" alt="Stáhnout">
											<span>'.$nazev_p.'</span>
											<div class="btn">Stáhnout</div>
										 </a>';
									 }
									 
									 $ret .= '</div>	
									</div>';
								 }  
			      
					$ret .= '</div></section>';
					
					
					
					// modal formulare
			      if($data_p['formular']==1)
				  {
				   $ret .= '<div class="modal micromodal-slide" id="modal-poptat">
				      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
				        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-poptat-title">
				          <div class="modal__content" id="modal-poptat-content" >
				            <div class="modal__header">
				              <h2 class="modal__title" id="modal-poptat-title">Formulář poptání produktu</h2>
				              <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
				            </div>
				            <div id="obal_form1">
				            <form method="post" id="poptat-form">
				              <div class="form-group"> 
				                <label for="poptat-jmeno">Jméno a příjmení</label>
				                <input type="text" id="poptat-jmeno" name="poptat-jmeno" required>
				              </div>
				              <div class="form-group"> 
				                <label for="poptat-email">Email</label>
				                <input type="email" id="poptat-email" name="poptat-email" required>
				              </div>
				              <div class="form-group"> 
				                <label for="poptat-tel">Telefon</label>
				                <input type="tel" id="poptat-tel" name="poptat-tel" required>
				              </div>
				              <div class="form-group"> 
				                <label for="poptat-info">Doplňující informace</label>
				                <textarea id="poptat-info" name="poptat-info" rows="3"></textarea>
				              </div>
				              <div class="form-group --checkbox">
				                <label for="poptat-agree">Souhlasím se <a href="/ochrana-osobnich-udaju" target="_blank">zpracováním osobních údajů</a>.</label>
				                <input type="checkbox" name="poptat-agree" value="1" id="poptat-agree" required>
				              </div>
				              <div class="form-group"> 
				                <div class="g-recaptcha" data-sitekey="'.__CAPTCHA_SITE_KEY__.'"></div>
				                <input type="hidden" name="produkt_nazev" id="produkt_nazev" value="'.$data_p['nazev'].'">
				                <input type="hidden" name="produkt_id" id="produkt_id" value="'.$data_p['id'].'">
								<input type="hidden" name="r_u" id="r_u" value="'.sha1(time()).'">
				                <button type="button" class="btn --fullWidth" name="poptat-submit" id="poptat-submit" onclick="OdeslatForm1();">Odeslat poptávku <img src="/img/icons/cheveron-white.svg" alt="Odeslat poptávku"></button>
				              </div>
				            </form>
				            </div>
				          </div>
				        </div>
				      </div>
				    </div>';
				  }

			      // příslušenství
			      if($data_p['prislusenstvi_id_arr'])
			      {
				      $ret .= '<section class="products">
						        <div class="container"> 
						          <h1>Příslušenství</h1>
						          <div class="products-grid">';
		          
		              $ret .= $this->zobrazPrislusenstvi($data_p['prislusenstvi_id_arr']);
		              
		              $ret .= '</div>
						        </div>
						      </section>';

				  }
				  

 
		      // související
		      $ret .= '<section class="products">
		        <div class="container"> 
		          <h1>Související</h1>
		          <div class="products-grid">';
		          
		          // náhodný výběr produktů ze stejné kategorie
		          if($data_p['id_kat_arr'])
		          {
				     $kat_arr_raw = unserialize($data_p['id_kat_arr']);
				     $kat_arr = array_reverse($kat_arr_raw);
				     $kat_souvisejici = $kat_arr[0];
				     
				     $ret .= $this->zobrazSouvisejici($id_pr,$kat_souvisejici);
				  }
				  

		                
		          $ret .= '</div>
		        </div>
		      </section>';
			      
			      
			}
			else
			{
			   //$ret .= 'Produkt nenalezen';
			   include('./skripty/404.php');
			   exit();  
			}
	     
	     

	  }
	  else
	  {
	     $ret .= 'Chybí ID produktu';
	  }
	  
	  
	  return $ret;
	     
		
	}
	
	
	
	
	public function produktySablona($id,$str,$nazev,$popis,$foto_arr,$dostupnost,$dostupnost_zobrazeni,$cena,$cena_puvodni,$sleva,$akce,$novinka,$doporucujeme,$doprodej,$doprava_zdarma,$typ)
	{  
		// typ - pokud má produkt jen jednu variantu tak 1 jinak 2
		// podle toho zobrazujeme cenu
		$ret = '<div class="product-card"> <a class="product-card__link" href="/produkty/'.$str.'-'.$id.'">
                <div class="product-card__top">    
                  <div class="product-card__thumb">
                    <div class="product-card__tags">';
                    
                     if($akce)
                     {
					   $ret .= '<div class="product-card__tagSingle">
                        <div class="tag --sale">Akce</div>
                        </div>';
					 }
					 
					 if($novinka)
                     {
					   $ret .= '<div class="product-card__tagSingle">   
                        <div class="tag --new">Novinka</div>
                      </div>';
					 }
					 
					 if($doporucujeme)
                     {
					   $ret .= '<div class="product-card__tagSingle"> 
                        <div class="tag --recommended">Doporučené</div>
                      </div>';
					 }
					 
					 if($doprodej)
                     {
					   $ret .= '<div class="product-card__tagSingle"> 
                        <div class="tag --top">Doprodej</div>
                      </div>';
					 }
					 
					 $ret .= ' </div>';
					 
					$ret .= '<div class="product-card__tagsRight">';
					 
					if($doprava_zdarma)
                    { 
					$ret .= '<div class="product-card__tagSingle"> 
					<div class="tag --free-delivery">Doprava zdarma</div>
					</div>';
					}
					
					 // sleva 
				     // pokud je alespoň jedna aktivní varianta ve slevě tak zobrazíme atribut
				     if($sleva>0)
					 {
				      $ret .= '<div class="product-card__tagSingle"> 
						<div class="tag --circleSale">-'.$sleva.'%</div>
					  </div>';	
				     }
				     
				     $ret .= '</div>';
					
                    if($foto_arr['foto']){$fotka = $foto_arr['foto'];}
                    else{$fotka = 'neni.png';}
                    
                    if($foto_arr['popis']){$fotka_popis = $foto_arr['popis'];}
                    else{$fotka_popis = $nazev;}
                    
                    $ret .= ' 
                    <div class="product-card__imgWrap"><img class="lazyload product-card__img" src="/img/load-symbol.svg" data-src="/fotky/produkty/male/'.$fotka.'" alt="'.$fotka_popis.'" 
                    title="'.$fotka_popis.'" ></div>
                  </div>
                  <div class="product-card__title">'.$nazev.'</div>
                </div>
                <div class="product-card__bottom">
                  <div class="product-card__description">'.$popis.'</div>';
                  
                  // úprava zobrazení dostupnosti z 16.8.2021
                  if($dostupnost_zobrazeni==1)
                  {
					$ret .= '<div class="product-card__delivery"><img src="/ikony/check.svg" alt="'.$dostupnost.'">
                    <div class="product-card__deliveryText">'.$dostupnost.'</div>';
				  }
				  else
				  {
					$ret .= '<div class="product-card__delivery"><img src="/ikony/cross.svg" alt="'.$dostupnost.'">
                    <div class="product-card__deliveryText --unavailable">'.$dostupnost.'</div>';
				  }
                  
                  
                    
                  $ret .= '</div>
                  <div class="product-card__price">
                    ';
                    
                    if($typ==1)
                    {
						if($cena_puvodni>$cena)
	                    {
						  $ret .= '<div class="product-card__priceOld">'.$cena_puvodni.' '.__MENA__.'</div>';
						}
						
	                    $ret .= '<div class="product-card__priceMain">'.$cena.' '.__MENA__.'</div>';
					}
					else
					{
						if($cena_puvodni>$cena)
	                    {
						  $ret .= '<div class="product-card__priceOld">od '.$cena_puvodni.' '.__MENA__.'</div>';
						}
						
	                    $ret .= '<div class="product-card__priceMain">od '.$cena.' '.__MENA__.'</div>';
					}
					
                    
                  $ret .= '</div>
                  <div class="btn product-card__btn">Detail produktu</div>
                </div></a></div>';
                
                return $ret;
		
	}
	
	
	
	
	
	public function vypocetCeny($cena,$dph,$sleva,$sleva_datum_od,$sleva_datum_do)
	{ 
	    // u tohoto projektu se zadávají v adminu ceny s DPH, není tedy nutné dopočítávat
	    // změna 16.2.2023 - v adminu se nastavuje jestli jsou ceny s nebo bez DPH
		
		if(__CENY_ADM__==1)
		{
			// ceny jsou bez DPH

			   if($sleva>0 && ($sleva_datum_od <= time() && $sleva_datum_do >= time()  )) 
			  	{
			  	  $cena_s_dph = round((($cena) - ($cena/100*$sleva)) * ($dph / 100 + 1),2);
			  	  $cena_puvodni_s_dph =  round($cena * ($dph / 100 + 1),2);
			  	  $sleva_ret = intval($sleva);
			  	}
			  	else
			  	{
			  	  $sleva_ret = 0;
			  	  if(__SLEVOVA_SKUPINA__)
			  	  {
			  	    $cena_s_dph = round(($cena - ($cena/100 * __SLEVOVA_SKUPINA__)) * ($dph / 100 + 1),2);
			  	  }
			  	  else
			  	  {
			  	    $cena_s_dph = round($cena * ($dph / 100 + 1),2);
			  	  }
			  	  
			  	  $cena_puvodni_s_dph =  round($cena * ($dph / 100 + 1),2);
			  	}
		
			  	$cena_bez_dph = round($cena,2);
		  
		}
		else
		{
			// ceny jsou s DPH
			
				if($sleva>0 && ($sleva_datum_od <= time() && $sleva_datum_do >= time()  )) 
			  	{
			  	  $cena_s_dph = round(($cena) - ($cena/100*$sleva),2);
			  	  $cena_puvodni_s_dph =  round($cena,2);
			  	  $sleva_ret = intval($sleva);
			  	}
			  	else
			  	{
			  	  $sleva_ret = 0;
			  	  if(__SLEVOVA_SKUPINA__)
			  	  {
			  	    $cena_s_dph = round(($cena) - ($cena/100 * __SLEVOVA_SKUPINA__),2);
			  	  }
			  	  else
			  	  {
			  	    $cena_s_dph = round($cena,2);
			  	  }
			  	  
			  	  $cena_puvodni_s_dph =  round($cena,2);
			  	}
		
			  	$cena_bez_dph = round(($cena_s_dph / ($dph / 100 + 1)),2);
		}
		

	  	
		// ceny vracíme v poli
		// úprava 16.8.2021 - vše zaokrouhlíme na celé částku
	  	$ret = array();
	  	$ret['cena_s_dph'] = round($cena_s_dph);
	  	$ret['cena_bez_dph'] = round($cena_bez_dph);
	  	$ret['cena_puvodni_s_dph'] = round($cena_puvodni_s_dph);
	  	$ret['sleva'] = $sleva_ret;
	  	
	  	return $ret;
	
	}
	
	
	
	
	
	public function dostupnostProduktu($id_pr,$jednotka)
	{ 
		// rozdělení dle typu
		$dostupnost = 'Nezjištěno';
		
		// nejdříve musíme zjistit jaké řešení dostupnosti má nastaveno
		if(__DOSTUPNOST_TYP__ == 1)
		{
		  // textová dostupnost dle výběru ve variantách
		  $data_var_dostupnost = Db::queryRow('SELECT V.id_dostupnost, D.dostupnost 
		  FROM produkty_varianty V
		  LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
		  WHERE V.id_produkt=? AND V.aktivni_var=? ORDER BY V.id_dostupnost ASC ', array($id_pr,1));
		  if($data_var_dostupnost)
		  {
		     $dostupnost = $data_var_dostupnost['dostupnost'];
		  }
		  
		}
		elseif(__DOSTUPNOST_TYP__ == 2)
		{
		  // počty kusů - součet všech aktivních variant
		  $data_var_dostupnost = Db::queryRow('SELECT sum(ks_skladem) AS KS_CELKEM FROM produkty_varianty WHERE id_produkt=? AND aktivni_var=?  ', array($id_pr,1));
		  if($data_var_dostupnost)
		  {
			 if($data_var_dostupnost['KS_CELKEM']<1)
			 {
				 // pokud je dostupnost 0 ks
				 $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost FROM produkty_dostupnost WHERE id=? ', array(__PREDVYBRANA_DOSTUPNOST_NULA__));
				 if($data_var_dostupnost2)
				 {
					$dostupnost = $data_var_dostupnost2['dostupnost'];
				 }
			 
			 }
			 else
			 {
				$dostupnost = $data_var_dostupnost['KS_CELKEM'].' '.$jednotka;
			 } 
		     
		  }
		}
		elseif(__DOSTUPNOST_TYP__ == 3)
		{
		  // textová dle počtu kusů od - do
		  $data_var_dostupnost = Db::queryRow('SELECT sum(ks_skladem) AS KS_CELKEM FROM produkty_varianty WHERE id_produkt=? AND aktivni_var=?  ', array($id_pr,1));
		  if($data_var_dostupnost)
		  {
		     if($data_var_dostupnost['KS_CELKEM']<1)
			 {
				 // pokud je dostupnost 0 ks
				 $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost FROM produkty_dostupnost WHERE id=? ', array(__PREDVYBRANA_DOSTUPNOST_NULA__));
				 if($data_var_dostupnost2)
				 {
					$dostupnost = $data_var_dostupnost2['dostupnost'];
				 }
			 
			 }
			 else
			 {
		     
				$data_var_dostupnost2 = Db::queryRow('SELECT dostupnost FROM produkty_dostupnost WHERE ks_od<=? AND ks_do>=?  ', array($data_var_dostupnost['KS_CELKEM'],$data_var_dostupnost['KS_CELKEM']));
				$dostupnost = $data_var_dostupnost2['dostupnost'];
		     }
		      
		  }
		}
		
		return $dostupnost;
						
	}
	
	
	
	public function dostupnostZobrazeni($id_pr)
	{ 

		  $zobrazeni = '1';

		  /*$data_var_dostupnost = Db::queryRow('SELECT V.id_dostupnost, D.zobrazeni 
		  FROM produkty_varianty V
		  LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
		  WHERE V.id_produkt=? AND V.aktivni_var=? ORDER BY V.id_dostupnost ASC ', array($id_pr,1));
		  if($data_var_dostupnost)
		  {
		     $dostupnost = $data_var_dostupnost['zobrazeni'];
		  }*/
		  
		  
		    if(__DOSTUPNOST_TYP__ == 1)
			{
			   // textová dostupnost dle výběru ve variantách
			      $data_var_dostupnost = Db::queryRow('SELECT V.id_dostupnost, D.zobrazeni 
				  FROM produkty_varianty V
				  LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
				  WHERE V.id_produkt=? AND V.aktivni_var=? ORDER BY V.id_dostupnost ASC ', array($id_pr,1));
				  if($data_var_dostupnost)
				  {
				     $zobrazeni = $data_var_dostupnost['zobrazeni'];
				  }
		  
 
			}
			elseif(__DOSTUPNOST_TYP__ == 2)
			{
			     // počty kusů 
			      $data_var_dostupnost = Db::queryRow('SELECT V.id_dostupnost, V.ks_skladem, D.zobrazeni 
				  FROM produkty_varianty V
				  LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
				  WHERE V.id_produkt=? AND V.aktivni_var=? ORDER BY V.ks_skladem DESC ', array($id_pr,1));
				  
				 if($data_var_dostupnost['ks_skladem']<1)
				 {
					 // pokud je dostupnost 0 ks
					 $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE id=? ', array(__PREDVYBRANA_DOSTUPNOST_NULA__));
					 if($data_var_dostupnost2)
					 {
						$zobrazeni = $data_var_dostupnost2['zobrazeni'];
					 }
				 
				 }
				 else
				 {
					$zobrazeni = $data_var_dostupnost['zobrazeni'];
				 } 
			     
			  
			}
			elseif(__DOSTUPNOST_TYP__ == 3)
			{
			     // textová dle počtu kusů od - do
			      $data_var_dostupnost = Db::queryRow('SELECT V.id_dostupnost, V.ks_skladem, D.zobrazeni 
				  FROM produkty_varianty V
				  LEFT JOIN produkty_dostupnost D ON D.id=V.id_dostupnost
				  WHERE V.id_produkt=? AND V.aktivni_var=? ORDER BY V.ks_skladem DESC ', array($id_pr,1));
				  
				 if($data_var_dostupnost['ks_skladem']<1)
				 {
					 // pokud je dostupnost 0 ks
					 $data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE id=? ', array(__PREDVYBRANA_DOSTUPNOST_NULA__));
					 if($data_var_dostupnost2)
					 {
						$zobrazeni = $data_var_dostupnost2['zobrazeni'];
					 }
				 
				 }
				 else
				 {
					$data_var_dostupnost2 = Db::queryRow('SELECT dostupnost, zobrazeni FROM produkty_dostupnost WHERE ks_od<=? AND ks_do>=?  ', array($data_var_dostupnost['ks_skladem'],$data_var_dostupnost['ks_skladem']));
					$zobrazeni = $data_var_dostupnost2['zobrazeni'];
				 }
				 
			}
		  
		
		
		return $zobrazeni;
						
	}
	
	
	
	public function pocetKat()
	{
		 $pocet = 0;
	     
	     if($this->parametry[1])
	     {
		     // získáme ID kategorie
		     $id_kat_arr_e = explode('-',$this->parametry[1]);
		     $id_kat_arr = array_reverse($id_kat_arr_e);
		     $id_kat = intval($id_kat_arr[0]);
	 
		     $where = " AND P.id_kat_arr LIKE '%\"".$id_kat."\"%' ";
		     
		        if($_GET['skladem']==1)
			    {
					// dostupnost
					if(__DOSTUPNOST_TYP__ == 1)
					{
					  $where .= ' AND V.id_dostupnost IN(1,9) ';
					}
					elseif(__DOSTUPNOST_TYP__ == 2)
					{
					  $where .= ' AND V.ks_skladem > 0 ';
					}
					elseif(__DOSTUPNOST_TYP__ == 3)
					{
					  $where .= ' AND V.ks_skladem > 0 ';
					}
								
			    }
				
				if($_GET['doprava_zdarma']==1)
				{
					$where .= ' AND P.doprava_zdarma=1 ';
				}
				
				if($_GET['novinka']==1)
				{
					$where .= ' AND P.novinka=1 ';
				}
				
				if($_GET['akce']==1)
				{
					$where .= ' AND P.akce=1 ';
				}
				
				if($_GET['doporucujeme']==1)
				{
					$where .= ' AND P.doporucujeme=1 ';
				}
				
				if($_GET['doprodej']==1)
				{
					$where .= ' AND P.doprodej=1 ';
				}
				
				
				if(isset($_GET['cena_od']))
				{
					$where .= ' AND V.'.$this->typ_ceny.' >= '.intval($_GET['cena_od']).' ';
				}
				
				if(isset($_GET['cena_do']))
				{
					$where .= ' AND V.'.$this->typ_ceny.' <= '.intval($_GET['cena_do']).' ';
				}
				
				$pocet = Db::queryAffected('SELECT P.id  
				FROM produkty P
				LEFT JOIN produkty_varianty V ON V.id_produkt=P.id
				WHERE P.aktivni=? AND V.aktivni_var=? '.$where.'
				GROUP BY P.id', array(1,1));
		
				if($pocet > 0)
				{
					return ceil($pocet / __POCET_NAHLEDU_PRODUKTY__);
				}
				else
				{
				   return $pocet;
				}
	     
	     }
	
	}
	
	

	


}
