<?php
// trida top menu

class TopMenu
{

public $get_parametry;  // klasicke GET parametry
public $skript; // nazev stranky


	function __construct($skript,$parametry)
	{
	 $this->parametry = $parametry;
	 $this->get_parametry = $_GET;
	 $this->skript = $skript;

	}


	public function zobrazTopMenu()
	{
	   
	   // menu statických stránek v záhlaví
	   $ret_menu = '';
	   $data_m = Db::queryAll('SELECT str, nadpis_menu FROM stranky WHERE aktivni=? AND typ=? ORDER BY razeni ', array(1,1));
	   if($data_m)
	   {
			   
			   foreach($data_m as $row_m)
			   {
				   $ret_menu .= '<a href="/'.$row_m['str'].'">'.$row_m['nadpis_menu'].'</a>';
			   }
		}
				

	   return $ret_menu;
	   
	   
	}
	
	
	
	public function infoLista($typ)
	{
	   
	   // infolišta pokud je aktivní
	   $ret_lista = ' ';
	   $data_l = Db::queryRow('SELECT * FROM infolista WHERE aktivni=? ORDER BY id DESC ', array(1));
	   if($data_l)
	   {
			  if($typ == 2 || ($typ == 1 && $this->skript == 'uvod'))	
		      $ret_lista .= ' <div class="header-notice"><div class="container">';
		      if($data_l['url'])
		      {
				   $ret_lista .= '<a href="'.$data_l['url'].'">';
			  }
		      $ret_lista .= $data_l['obsah'];
		      if($data_l['url'])
		      {
				   $ret_lista .= '</a>';
			  }
		      $ret_lista .= '</div></div>';

		}
				

	   return $ret_lista;
	   
	   
	}
	
	
	public function menuKategorieUvod()
	{ 
		 // menu kategorií v záhlaví / levý sloupec nebo top vodorovný vysouvací
		 $ret = '';
		 $mp = 0;
		 $data_k = Db::queryAll('SELECT id, str, nazev, foto FROM kategorie WHERE aktivni=? AND vnor=? ORDER BY razeni ASC ', array(1,1));
		 if($data_k)
		   {
			   
			   foreach($data_k as $row_k)
			   {
					// musíme zjistit jestli má zanoření
					$pocet_kz = 0;
					$pocet_kz = Db::queryCount2('kategorie','id','','vnor=2 AND id_nadrazeneho='.$row_k['id'].' ');
					if($pocet_kz)
					{
						// máme zanoření - zcela jiné formátování
						$ret .= '<div class="nav-item expandable"><a class="nav-linkDesktop" href="/kategorie/'.$row_k['str'].'-'.$row_k['id'].'">'.$row_k['nazev'].' <img src="/ikony/cheveron.svg" alt="Rozbalit"></a>
						          <div class="nav-link" id="subToggle-'.$mp.'">'.$row_k['nazev'].' <img src="/ikony/cheveron.svg" alt="Rozbalit"></div>
						          <div class="nav-submenu" id="sub-'.$mp.'">
						            <div class="nav-submenu__single --all"><a class="nav-submenu__title" href="/kategorie/'.$row_k['str'].'-'.$row_k['id'].'">Zobrazit vše</a></div>
						            <div class="container">
						              <div class="row">'; 
										  
										// podkategorie  
										 $data_k2 = Db::queryAll('SELECT id, str, nazev, foto FROM kategorie WHERE aktivni=? AND vnor=? AND id_nadrazeneho=? ORDER BY razeni ASC ', array(1,2,$row_k['id']));
										 if($data_k2)
										   {
											   
											   foreach($data_k2 as $row_k2)
											   {
												   if($row_k2['foto'])
												   {
												        $foto = $row_k2['foto'];
												   }
												   else
												   {
														$foto = 'product-thumb-medium.jpg'; 
												   }
												   
												   $ret .= '<div class="col-12 col-md-4 col-lg-3">
											                  <div class="nav-submenu__single">   <img src="/fotky/kategorie/male/'.$foto.'" alt="'.$row_k2['nazev'].'" title="'.$row_k2['nazev'].'">
											                    <ul> 
											                      <li> <a class="nav-submenu__title" href="/kategorie/'.$row_k2['str'].'-'.$row_k2['id'].'">'.$row_k2['nazev'].'</a></li>';
											                      
											                      // třetí úroveň
											                      $data_k3 = Db::queryAll('SELECT id, str, nazev, foto FROM kategorie WHERE aktivni=? AND vnor=? AND id_nadrazeneho=? ORDER BY razeni ASC ', array(1,3,$row_k2['id']));
																	 if($data_k3)
																	   {
																		   foreach($data_k3 as $row_k3)
																		   {
																				$ret .= '<li> <a class="nav-submenu__link" href="/kategorie/'.$row_k3['str'].'-'.$row_k3['id'].'">'.$row_k3['nazev'].'</a></li>';
																		   }
																	   }
											                      

											                   $ret .= ' </ul>
																	</div>
																</div>';
											   }
										   
										   }
										   
										   
						                
						                
						
						                
						              $ret .= '</div>
						            </div>
						          </div>
						        </div>';
						        
						        $mp++;
						
					}
					else
					{
						$ret .= '<div class="nav-item"><a class="nav-link" href="/kategorie/'.$row_k['str'].'-'.$row_k['id'].'">'.$row_k['nazev'].'</a></div>';
					}
				    
				
				
			   }
			  
		   }
		   
		   return $ret;
	}
	
	
	
	
	
	
}


?>	
