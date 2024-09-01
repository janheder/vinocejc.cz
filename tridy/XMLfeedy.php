<?php

// generování XML feedů pro cron i admin

class XMLfeedy
{
	public static function generujXMLHeureka($xml_soubor)
    {
		
		$xml_feed = '<?xml version="1.0" encoding="UTF-8"?><SHOP>';

		$data_pr = Db::queryAll('SELECT V.*, P.nazev, P.popis, P.id as IDP, P.str, P.nazev_heureka, P.category_text_heureka, P.id_kat_arr, P.gift_heureka, P.gift_id_heureka, D.dph, VYR.vyrobce, DOST.dostupnost_porovnavace 
		FROM produkty_varianty V 
		LEFT JOIN produkty P ON P.id = V.id_produkt
		LEFT JOIN dph D ON D.id = P.id_dph
		LEFT JOIN produkty_vyrobci VYR ON VYR.id = P.id_vyrobce
		LEFT JOIN produkty_dostupnost DOST ON DOST.id = V.id_dostupnost
		WHERE P.aktivni=? AND V.aktivni_var=? ', array(1,1));
		if($data_pr !== false ) 
		{
			   foreach ($data_pr as $row_pr) 
				{
					   $xml_feed .= '<SHOPITEM>';
					   $xml_feed .= '<ITEM_ID>'.$row_pr['IDP'].'_'.$row_pr['id'].'</ITEM_ID>';
					   if($row_pr['nazev_heureka'])
					   {
					     $xml_feed .= '<PRODUCTNAME><![CDATA['.stripslashes($row_pr['nazev_heureka']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCTNAME>';
					     $xml_feed .= '<PRODUCT><![CDATA['.stripslashes($row_pr['nazev_heureka']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCT>';
					   }
					   else
					   {
					     $xml_feed .= '<PRODUCTNAME><![CDATA['.stripslashes($row_pr['nazev']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCTNAME>';
					     $xml_feed .= '<PRODUCT><![CDATA['.stripslashes($row_pr['nazev']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCT>';
					   }
					    
					   $xml_feed .= '<DESCRIPTION><![CDATA['.stripslashes($row_pr['popis']).']]></DESCRIPTION>';
					   $xml_feed .= '<URL>'.__URL__.'/produkty/'.$row_pr['str'].'-'.$row_pr['IDP'].'?var='.$row_pr['id'].'</URL>';
					   if($row_pr['foto_var'])
					   {
					     $xml_feed .= '<IMGURL>'.__URL__.'/fotky/produkty/velke/'.$row_pr['foto_var'].'</IMGURL>';
				       }
				       else
				       {
						   $data_f = Db::queryRow('SELECT foto FROM produkty_foto WHERE id_produkt=? AND typ=? ', array($row_pr['IDP'],1));
						   if($data_f !== false)
						   {
						     $xml_feed .= '<IMGURL>'.__URL__.'/fotky/produkty/velke/'.$data_f['foto'].'</IMGURL>';
					       }
					   }
					   // fotky další
					   $data_fd = Db::queryAll('SELECT foto FROM produkty_foto WHERE id_produkt=? AND typ=? ', array($row_pr['IDP'],0));
					   if($data_fd !== false)
					   {
							foreach ($data_fd as $row_fd) 
							{
								$xml_feed .= '<IMGURL_ALTERNATIVE>'.__URL__.'/fotky/produkty/velke/'.$row_fd['foto'].'</IMGURL_ALTERNATIVE>';
							}
					   }
					   
					   
					   // dle nastavení zadávání cen
						if(__CENY_ADM__==1)
						{
						  // ceny jsou bez DPH
						   if($row_pr['sleva'] > 0 &&  ($row_pr['sleva_datum_od'] < time() && $row_pr['sleva_datum_do'] > time() ) )
						   {
								$xml_feed .= '<PRICE_VAT>'.round((($row_pr['cena_A'] - ($row_pr['cena_A'] / 100 * $row_pr['sleva'])) ) * ($row_pr['dph']/100+1)).'</PRICE_VAT>';
						   }
						   else
						   {
								$xml_feed .= '<PRICE_VAT>'.round($row_pr['cena_A'] * ($row_pr['dph']/100+1)).'</PRICE_VAT>';
						   }
						  
						  
						}
						else
						{
						  // ceny jsou s DPH
						  // cena vždy s DPH
						   if($row_pr['sleva'] > 0 &&  ($row_pr['sleva_datum_od'] < time() && $row_pr['sleva_datum_do'] > time() ) )
						   {
								$xml_feed .= '<PRICE_VAT>'.round((($row_pr['cena_A'] - ($row_pr['cena_A'] / 100 * $row_pr['sleva'])) )).'</PRICE_VAT>';
						   }
						   else
						   {
								$xml_feed .= '<PRICE_VAT>'.round(($row_pr['cena_A'])).'</PRICE_VAT>';
						   }
						  
						}
						
					   	
					   	
					   
					   $xml_feed .= '<VAT>'.$row_pr['dph'].'%</VAT>';
					   $xml_feed .= '<MANUFACTURER><![CDATA['.$row_pr['vyrobce'].']]></MANUFACTURER>';
					   
					   // dárek
					   if($row_pr['gift_heureka'] && $row_pr['gift_id_heureka'])
					   {
						  $xml_feed .= '<GIFT ID="'.$row_pr['gift_id_heureka'].'">'.$row_pr['gift_heureka'].'</GIFT>';
					   }
					   
					   // category text
					   if($row_pr['category_text_heureka'])
					   {
							$xml_feed .= '<CATEGORYTEXT>'.$row_pr['category_text_heureka'].'</CATEGORYTEXT>';
					   }
					   else
					   {
							$xml_feed .= '<CATEGORYTEXT><![CDATA[';
							if($row_pr['id_kat_arr'])
							{

							       // pokud není vyplněno tak zkusíme dohledat přes párovací modul
							       $kat_arr = unserialize($row_pr['id_kat_arr']);
							       // vezmeme poslední kategorii
							       $kat_arr_p = array_reverse($kat_arr);
							       $kat = $kat_arr_p[1];
							       
							       
							       $data_k = Db::queryRow('SELECT id_heureka_cz FROM kategorie WHERE id=? ', array($kat));
							       if($data_k['id_heureka_cz'])
							       {
									   

								       $data_k_ct = Db::queryRow('SELECT category_fullname FROM kategorie_heureka WHERE id_heureka=? ', array($data_k['id_heureka_cz']));
								       if($data_k_ct['category_fullname'])
								       {

										  $xml_feed .=  "<CATEGORYTEXT>".$data_k_ct['category_fullname']."</CATEGORYTEXT>\n";
									   }
							       }
							       else
							       {
										 $data_k = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($kat_arr_p[0]));
										 if($data_k)
										 {
											  if($data_k['vnor']==3)
											  {
												 $data_k3 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k['id_nadrazeneho']));
												 if($data_k3)
												 {	  	
													 $data_k2 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k3['id_nadrazeneho']));
													 if($data_k2)
													 {
												        $xml_feed .= $data_k2['nazev'].' | ';
												        $xml_feed .= $data_k3['nazev'].' | ';
												        $xml_feed .= $data_k['nazev'];
												     }
												 }
												 
											  }
											  elseif($data_k['vnor']==2)
											  {
												 $data_k2 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k['id_nadrazeneho']));
												 if($data_k2)
												 {
											        $xml_feed .= $data_k2['nazev'].' | ';
											        $xml_feed .= $data_k['nazev'];
											     }
											  }
											  elseif($data_k['vnor']==1)
											  {
											     $xml_feed .= $data_k['nazev'];
											  }  
											  			
										 }
								   
								   }
					       
 
							}
							
							$xml_feed .= ']]></CATEGORYTEXT>';
					   }
					    
					   if($row_pr['ean_var']) 
					   {
							$xml_feed .= '<EAN>'.$row_pr['ean_var'].'</EAN>';
					   }
	
					   $xml_feed .= '<PRODUCTNO>'.$row_pr['kat_cislo_var'].'</PRODUCTNO>';
					   // parametry
					   $data_par= Db::queryAll('SELECT nazev,hodnota FROM produkty_tech_par WHERE id_produkt=? ', array($row_pr['IDP']));
					   if($data_par !== false)
					   {	
							foreach ($data_par as $row_par) 
							{
								$xml_feed .= '<PARAM>';
								$xml_feed .= '<PARAM_NAME>'.stripslashes($row_par['nazev']).'</PARAM_NAME>';
								$xml_feed .= '<VAL>'.stripslashes($row_par['hodnota']).'</VAL>';
								$xml_feed .= '</PARAM>';
							}
							
						}
	 
					    $xml_feed .= '<DELIVERY_DATE>'.$row_pr['dostupnost_porovnavace'].'</DELIVERY_DATE>';
					    
					    // doprava
					    $data_dop= Db::queryAll('SELECT D.*, DP.dph FROM doprava D 
					    LEFT JOIN dph DP ON DP.id=D.id_dph
					    WHERE D.aktivni_porovnavace=? ', array(1));
					    if($data_dop !== false)
					    {	
							foreach ($data_dop as $row_dop) 
							{
								$xml_feed .= '<DELIVERY>
								<DELIVERY_ID>'.$row_dop['dopravce_heureka'].'</DELIVERY_ID>';
								
								 // dle nastavení zadávání cen
								if(__CENY_ADM__==1)
								{
								  // ceny jsou bez DPH
								  $xml_feed .= '<DELIVERY_PRICE>'.round($row_dop['cena'] * ($row_pr['dph']/100+1)).'</DELIVERY_PRICE>';
								}
								else
								{
								  // ceny jsou s DPH
								  $xml_feed .= '<DELIVERY_PRICE>'.round($row_dop['cena']).'</DELIVERY_PRICE>';
								}
								
								
								$xml_feed .= '</DELIVERY>';
							}
						 }
	
					    $xml_feed .= '<ITEMGROUP_ID>P_'.$row_pr['IDP'].'</ITEMGROUP_ID>';
					    $xml_feed .= '</SHOPITEM>';
				}
				
				$xml_feed .= '</SHOP>';
				file_put_contents($xml_soubor, $xml_feed);
				return 'XML feed vygenerován'; 
				
		}
		else
		{
		    return 'Chyba při generování XML '.$_POST['t'];
	    }
			
			
	        
	}
	
	
	
	
	
	public static function generujXMLHeurekaDostupnost($xml_soubor)
    {
	
		$xml_feed = '<?xml version="1.0" encoding="UTF-8"?><item_list>';

		$data_pr = Db::queryAll('SELECT V.*, P.nazev, P.id as IDP
		FROM produkty_varianty V 
		LEFT JOIN produkty P ON P.id = V.id_produkt
		WHERE P.aktivni=? AND V.aktivni_var=? AND V.ks_skladem > 0  ', array(1,1));
		if($data_pr !== false ) 
		{
			   foreach ($data_pr as $row_pr) 
				{
						// musíme zjistit jestli už je více hodin než je definováno v obecném nastavení 
						if(date('G') > intval(__HEUREKA_CAS_OBJEDNANI__))
						{
						  // už nemůžeme garantovat dodání 
						  $d_time = (time() + (3600*24*(__HEUREKA_DOBA_DODANI__+1)));
						  $d_time2 = (time() + (3600*24));
						  $deadline = date('Y-m-d',$d_time2).' '.intval(__HEUREKA_CAS_OBJEDNANI__).':00';
						  $deliverytime = date('Y-m-d H:i',$d_time);
						}
						else
						{
						  $d_time = (time() + (3600*24*__HEUREKA_DOBA_DODANI__));
						  $deadline = date('Y-m-d').' '.intval(__HEUREKA_CAS_OBJEDNANI__).':00';
						  $deliverytime = date('Y-m-d H:i',$d_time);
						}
						
					    $xml_feed .= '<item id="'.$row_pr['IDP'].'_'.$row_pr['id'].'">
					    <stock_quantity>'.$row_pr['ks_skladem'].'</stock_quantity>
					    <delivery_time orderDeadline="'.$deadline.'">'.$deliverytime.'</delivery_time>
					    </item>';
	
					 
				}
				
				$xml_feed .= '</item_list>';
				file_put_contents($xml_soubor, $xml_feed);
				return 'XML feed vygenerován'; 
				
		}
		else
		{
		    return 'Chyba při generování XML '.$_POST['t'];
	    }
	
	}
	
	
	
	
	public static function generujXMLZbozi($xml_soubor)
    {
	
		$xml_feed = '<?xml version="1.0" encoding="utf-8"?><SHOP xmlns="http://www.zbozi.cz/ns/offer/1.0">';

		$data_pr = Db::queryAll('SELECT V.*, P.nazev, P.popis, P.id as IDP, P.str, P.nazev_zbozi, P.product, P.category_text_heureka, P.id_kat_arr, 
		P.id_category_text_zbozi, P.id_category_text_zbozi2, P.productno, P.extramessage, P.extramessage2, P.extramessage3, P.brand, P.vydejnimisto, 
		P.vydejnimisto2, P.vydejnimisto3, P.productline, P.maxcpc, P.maxcpc_search, P.itemgroupid, P.visibility, P.CUSTOM_LABEL_0, P.CUSTOM_LABEL_1, 
		P.CUSTOM_LABEL_2, P.CUSTOM_LABEL_3, P.nazev_zbozi, P.product, P.parovaci_kod, P.gift_heureka, 
		D.dph, VYR.vyrobce, DOST.dostupnost_porovnavace 
		FROM produkty_varianty V 
		LEFT JOIN produkty P ON P.id = V.id_produkt
		LEFT JOIN dph D ON D.id = P.id_dph
		LEFT JOIN produkty_vyrobci VYR ON VYR.id = P.id_vyrobce
		LEFT JOIN produkty_dostupnost DOST ON DOST.id = V.id_dostupnost
		WHERE P.aktivni=? AND V.aktivni_var=? ', array(1,1));
		if($data_pr !== false ) 
		{
			   foreach ($data_pr as $row_pr) 
				{
					   $xml_feed .= '<SHOPITEM>';
					   $xml_feed .= '<ITEM_ID>'.$row_pr['IDP'].'_'.$row_pr['id'].'</ITEM_ID>';
					   if($row_pr['nazev_zbozi'])
					   {
					     $xml_feed .= '<PRODUCTNAME><![CDATA['.stripslashes($row_pr['nazev_zbozi']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCTNAME>';
					   }
					   else
					   {
					     $xml_feed .= '<PRODUCTNAME><![CDATA['.stripslashes($row_pr['nazev']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCTNAME>';
					   }
					   
					   if($row_pr['product'])
					   {
					     $xml_feed .= '<PRODUCT><![CDATA['.stripslashes($row_pr['product']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCT>';
					   }
					   else
					   {
					     $xml_feed .= '<PRODUCT><![CDATA['.stripslashes($row_pr['nazev']).' '.stripslashes($row_pr['nazev_var']).']]></PRODUCT>';
					   }
					    
					   $xml_feed .= '<DESCRIPTION><![CDATA['.stripslashes($row_pr['popis']).']]></DESCRIPTION>';
					   $xml_feed .= '<URL>'.__URL__.'/produkty/'.$row_pr['str'].'-'.$row_pr['IDP'].'</URL>';
					   if($row_pr['foto_var'])
					   {
					     $xml_feed .= '<IMGURL>'.__URL__.'/fotky/produkty/velke/'.$row_pr['foto_var'].'</IMGURL>';
				       }
				       else
				       {
						   $data_f = Db::queryRow('SELECT foto FROM produkty_foto WHERE id_produkt=? AND typ=? ', array($row_pr['IDP'],1));
						   if($data_f !== false)
						   {
						     $xml_feed .= '<IMGURL>'.__URL__.'/fotky/produkty/velke/'.$data_f['foto'].'</IMGURL>';
					       }
					   }
					   // fotky další
					   $data_fd = Db::queryAll('SELECT foto FROM produkty_foto WHERE id_produkt=? AND typ=? ', array($row_pr['IDP'],0));
					   if($data_fd !== false)
					   {
							foreach ($data_fd as $row_fd) 
							{
								$xml_feed .= '<IMGURL_ALTERNATIVE>'.__URL__.'/fotky/produkty/velke/'.$row_fd['foto'].'</IMGURL_ALTERNATIVE>';
							}
					   }
					   
					   
					   
					   // dle nastavení zadávání cen
						if(__CENY_ADM__==1)
						{
						  // ceny jsou bez DPH
						   if($row_pr['sleva'] > 0 &&  ($row_pr['sleva_datum_od'] < time() && $row_pr['sleva_datum_do'] > time() ) )
						   {
								$xml_feed .= '<PRICE_VAT>'.round((($row_pr['cena_A'] - ($row_pr['cena_A'] / 100 * $row_pr['sleva'])) ) * ($row_pr['dph']/100+1)).'</PRICE_VAT>';
						   }
						   else
						   {
								$xml_feed .= '<PRICE_VAT>'.round($row_pr['cena_A'] * ($row_pr['dph']/100+1)).'</PRICE_VAT>';
						   }
						  
						  
						}
						else
						{
						  // ceny jsou s DPH
						   if($row_pr['sleva'] > 0 &&  ($row_pr['sleva_datum_od'] < time() && $row_pr['sleva_datum_do'] > time() ) )
						   {
								$xml_feed .= '<PRICE_VAT>'.round((($row_pr['cena_A'] - ($row_pr['cena_A'] / 100 * $row_pr['sleva'])))).'</PRICE_VAT>';
						   }
						   else
						   {
								$xml_feed .= '<PRICE_VAT>'.round(($row_pr['cena_A'])).'</PRICE_VAT>';
						   }
					    
					    }
					   
					   
					   
					   //$xml_feed .= '<VAT>'.$row_pr['dph'].'%</VAT>';
					   $xml_feed .= '<MANUFACTURER><![CDATA['.$row_pr['vyrobce'].']]></MANUFACTURER>';
					   
					    
					   if($row_pr['ean_var']) 
					   {
							$xml_feed .= '<EAN>'.$row_pr['ean_var'].'</EAN>';
					   }
	
					   $xml_feed .= '<PRODUCTNO>'.$row_pr['kat_cislo_var'].'</PRODUCTNO>';
					   // parametry
					   $data_par= Db::queryAll('SELECT nazev,hodnota FROM produkty_tech_par WHERE id_produkt=? ', array($row_pr['IDP']));
					   if($data_par !== false)
					   {	
							foreach ($data_par as $row_par) 
							{
								$xml_feed .= '<PARAM>';
								$xml_feed .= '<PARAM_NAME>'.stripslashes($row_par['nazev']).'</PARAM_NAME>';
								$xml_feed .= '<VAL>'.stripslashes($row_par['hodnota']).'</VAL>';
								$xml_feed .= '</PARAM>';
							}
							
						}
	 
					    $xml_feed .= '<DELIVERY_DATE>'.$row_pr['dostupnost_porovnavace'].'</DELIVERY_DATE>';
					    
					    // doprava
					    $data_dop= Db::queryAll('SELECT D.*, DP.dph FROM doprava D 
					    LEFT JOIN dph DP ON DP.id=D.id_dph
					    WHERE D.aktivni_porovnavace=? ', array(1));
					    if($data_dop !== false)
					    {	
							foreach ($data_dop as $row_dop) 
							{
								$xml_feed .= '<DELIVERY>
								<DELIVERY_ID>'.$row_dop['dopravce_heureka'].'</DELIVERY_ID>';
								
 
								// dle nastavení zadávání cen
								if(__CENY_ADM__==1)
								{
								  // ceny jsou bez DPH
								  $xml_feed .= '<DELIVERY_PRICE>'.round($row_dop['cena'] * ($row_pr['dph']/100+1)).'</DELIVERY_PRICE>';
								}
								else
								{
								  // ceny jsou s DPH
								  $xml_feed .= '<DELIVERY_PRICE>'.round($row_dop['cena']).'</DELIVERY_PRICE>';
								}
								
								$xml_feed .= '</DELIVERY>';
							}
						 }
	
					    $xml_feed .= '<ITEMGROUP_ID>P_'.$row_pr['IDP'].'</ITEMGROUP_ID>';
					    
					    // speciální parametry
					    if($row_pr['extramessage'])
						{
							$xml_feed .=  "<EXTRA_MESSAGE>".strip_tags($row_pr['extramessage'])."</EXTRA_MESSAGE>\n";	
						}
						
						if($row_pr['extramessage2'])
						{
							$xml_feed .=  "<EXTRA_MESSAGE>".strip_tags($row_pr['extramessage2'])."</EXTRA_MESSAGE>\n";	
						}
						
						if($row_pr['extramessage3'])
						{
							$xml_feed .=  "<EXTRA_MESSAGE>".strip_tags($row_pr['extramessage3'])."</EXTRA_MESSAGE>\n";	
						}
						
						// dárek
						if($row_pr['gift_heureka'])
						{
							$xml_feed .=  "<EXTRA_MESSAGE>free_gift</EXTRA_MESSAGE>";
							$xml_feed .= "<FREE_GIFT_TEXT>".$row_pr['gift_heureka']."</FREE_GIFT_TEXT>";
					    }
						
						if($row_pr['brand'])
						{
							$xml_feed .=  "<BRAND><![CDATA[".strip_tags($row_pr['brand'])."]]></BRAND>\n";
					    }
						
						if($row_pr['vydejnimisto'])
						{
							$xml_feed .=  "<SHOP_DEPOTS>".$row_pr['vydejnimisto']."</SHOP_DEPOTS>\n";
						}
						if($row_pr['vydejnimisto2'])
						{
							$xml_feed .=  "<SHOP_DEPOTS>".$row_pr['vydejnimisto2']."</SHOP_DEPOTS>\n";
						}
						if($row_pr['vydejnimisto3'])
						{
							$xml_feed .=  "<SHOP_DEPOTS>".$row_pr['vydejnimisto3']."</SHOP_DEPOTS>\n";
						}
						
						if($row_pr['productline'])
						{
							$xml_feed .=  "<PRODUCT_LINE>".$row_pr['productline']."</PRODUCT_LINE>\n";
						}
						
						if($row_pr['maxcpc'])
						{
							$xml_feed .=  "<MAX_CPC>".$row_pr['maxcpc']."</MAX_CPC>\n";
						}
						
						if($row_pr['maxcpc_search'])
						{
							$xml_feed .=  "<MAX_CPC_SEARCH>".$row_pr['maxcpc_search']."</MAX_CPC_SEARCH>\n";
						}
						
						if($row_pr['itemgroupid'])
						{
							$xml_feed .=  "<ITEMGROUP_ID>".$row_pr['itemgroupid']."</ITEMGROUP_ID>\n";
						}
						
						if($row_pr['product'])
						{
							$xml_feed .=  "<PRODUCT>".$row_pr['product']."</PRODUCT>\n";
						}
						
						if($row_pr['CUSTOM_LABEL_0'])
						{
							$xml_feed .=  "<CUSTOM_LABEL_0>".$row_pr['CUSTOM_LABEL_0']."</CUSTOM_LABEL_0>\n";
						}
						
						if($row_pr['CUSTOM_LABEL_1'])
						{
							$xml_feed .=  "<CUSTOM_LABEL_1>".$row_pr['CUSTOM_LABEL_1']."</CUSTOM_LABEL_1>\n";
						}
						
						if($row_pr['CUSTOM_LABEL_2'])
						{
							$xml_feed .=  "<CUSTOM_LABEL_2>".$row_pr['CUSTOM_LABEL_2']."</CUSTOM_LABEL_2>\n";
						}
						
						if($row_pr['CUSTOM_LABEL_3'])
						{
							$xml_feed .=  "<CUSTOM_LABEL_3>".$row_pr['CUSTOM_LABEL_3']."</CUSTOM_LABEL_3>\n";
						}
						
						$xml_feed .=  "<ITEM_TYPE>new</ITEM_TYPE>\n";
						$xml_feed .=  "<VISIBILITY>".$row_pr['visibility']."</VISIBILITY>\n";	
						
						if($row_pr['id_category_text_zbozi'])
						{
							$data_ct1 = Db::queryRow('SELECT categorytext FROM kategorie_zbozi WHERE id=? ', array($row_pr['id_category_text_zbozi']));
							$xml_feed .=  "<CATEGORYTEXT>".$data_ct1['categorytext']."</CATEGORYTEXT>\n";
						}
						elseif($row_pr['id_kat_arr'])
						{
					       // pokud není vyplněno tak zkusíme dohledat přes párovací modul
					       $kat_arr = unserialize($row_pr['id_kat_arr']);
					       // vezmeme poslední kategorii
					       $kat_arr_p = array_reverse($kat_arr);
					       $kat = $kat_arr_p[0];
					       
					       $data_k = Db::queryRow('SELECT id_zbozi_cz FROM kategorie WHERE id=? ', array($kat));
					       if($data_k['id_zbozi_cz'])
					       {
						       $data_k_ct = Db::queryRow('SELECT categorytext FROM kategorie_zbozi WHERE id=? ', array($data_k['id_zbozi_cz']));
						       if($data_k_ct['categorytext'])
						       {
								  $xml_feed .=  "<CATEGORYTEXT>".$data_k_ct['categorytext']."</CATEGORYTEXT>\n";
							   }
					       }
					       
					       
					    }
						
						if($row_pr['id_category_text_zbozi2'])
						{
							$data_ct1 = Db::queryRow('SELECT categorytext FROM kategorie_zbozi WHERE id=? ', array($row_pr['id_category_text_zbozi2']));
							$xml_feed .=  "<CATEGORYTEXT>".$data_ct1['categorytext']."</CATEGORYTEXT>\n";
						}
						elseif($row_pr['id_kat_arr'])
						{
					       // pokud není vyplněno tak zkusíme dohledat přes párovací modul
					       $kat_arr = unserialize($row_pr['id_kat_arr']);
					       // vezmeme poslední kategorii
					       $kat_arr_p = array_reverse($kat_arr);
					       $kat = $kat_arr_p[1];
					       
					       $data_k = Db::queryRow('SELECT id_zbozi_cz FROM kategorie WHERE id=? ', array($kat));
					       if($data_k['id_zbozi_cz'])
					       {
						       $data_k_ct = Db::queryRow('SELECT categorytext FROM kategorie_zbozi WHERE id=? ', array($data_k['id_zbozi_cz']));
						       if($data_k_ct['categorytext'])
						       {
								  $xml_feed .=  "<CATEGORYTEXT>".$data_k_ct['categorytext']."</CATEGORYTEXT>\n";
							   }
					       }
					       
					       
					    }
						
			
					    $xml_feed .= '</SHOPITEM>';
				}
				
				$xml_feed .= '</SHOP>';
				file_put_contents($xml_soubor, $xml_feed);
				return 'XML feed vygenerován'; 
				
		}
		else
		{
		    return 'Chyba při generování XML '.$_POST['t'];
	    }
	
	}
	
	
	
	public static function generujXMLGoogle($xml_soubor)
    {
	
			$xml_feed = '<?xml version="1.0" encoding="utf-8"?>
			<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">
			<title>'.__TITLE__.'</title>
			<link rel="self" href="'.__URL__.'/xml/google.xml"/>
		    <updated>'.date('Y').'-'.date('m').'-'.date('d').'T03:00:00</updated>';
		
			$data_pr = Db::queryAll('SELECT V.*, P.nazev, P.popis, P.id as IDP, P.str, P.id_kat_arr, P.specialni_doprava,  
			D.dph, VYR.vyrobce, DOST.dostupnost_porovnavace 
			FROM produkty_varianty V 
			LEFT JOIN produkty P ON P.id = V.id_produkt
			LEFT JOIN dph D ON D.id = P.id_dph
			LEFT JOIN produkty_vyrobci VYR ON VYR.id = P.id_vyrobce
			LEFT JOIN produkty_dostupnost DOST ON DOST.id = V.id_dostupnost
			WHERE P.aktivni=? AND V.aktivni_var=? ', array(1,1));
			if($data_pr !== false ) 
			{
				   foreach ($data_pr as $row_pr) 
					{
						   $xml_feed .= '<entry>';
						   $xml_feed .= '<g:id>'.$row_pr['IDP'].'_'.$row_pr['id'].'</g:id>';
						   $xml_feed .= '<title><![CDATA['.stripslashes($row_pr['nazev']).' '.stripslashes($row_pr['nazev_var']).']]></title>';
		
						    
						   $xml_feed .= '<description><![CDATA['.stripslashes($row_pr['popis']).']]></description>';
						   $xml_feed .= '<link>'.__URL__.'/produkty/'.$row_pr['str'].'-'.$row_pr['IDP'].'</link>';
						   $xml_feed .= '<g:condition>new</g:condition>';
						   if($row_pr['foto_var'])
						   {
						     $xml_feed .= '<g:image_link>'.__URL__.'/fotky/produkty/velke/'.$row_pr['foto_var'].'</g:image_link>';
					       }
					       else
					       {
							   $data_f = Db::queryRow('SELECT foto FROM produkty_foto WHERE id_produkt=? AND typ=? ', array($row_pr['IDP'],1));
							   if($data_f !== false)
							   {
							     $xml_feed .= '<g:image_link>'.__URL__.'/fotky/produkty/velke/'.$data_f['foto'].'</g:image_link>';
						       }
						   }
							
							
							// dle nastavení zadávání cen
							if(__CENY_ADM__==1)
							{
							  // ceny jsou bez DPH
							   if($row_pr['sleva'] > 0 &&  ($row_pr['sleva_datum_od'] < time() && $row_pr['sleva_datum_do'] > time() ) )
							   {
									$xml_feed .= '<g:price>'.round((($row_pr['cena_A'] - ($row_pr['cena_A'] / 100 * $row_pr['sleva'])) ) * ($row_pr['dph']/100+1)).' CZK</g:price>';
							   }
							   else
							   {
									$xml_feed .= '<g:price>'.round($row_pr['cena_A'] * ($row_pr['dph']/100+1)).' CZK</g:price>';
							   }
							  
							  
							}
							else
							{
							   // ceny jsou s DPH
	  						   // cena s DPH
							   if($row_pr['sleva'] > 0 &&  ($row_pr['sleva_datum_od'] < time() && $row_pr['sleva_datum_do'] > time() ) )
							   {
									$xml_feed .= '<g:price>'.round((($row_pr['cena_A'] - ($row_pr['cena_A'] / 100 * $row_pr['sleva'])) )).' CZK</g:price>';
							   }
							   else
							   {
									$xml_feed .= '<g:price>'.round(($row_pr['cena_A'])).' CZK</g:price>';
							   }
						    }
						   	
		
						   
						   $xml_feed .= '<g:brand><![CDATA['.$row_pr['vyrobce'].']]></g:brand>';
						   
						    
						   if($row_pr['ean_var']) 
						   {
								$xml_feed .= '<g:gtin>'.$row_pr['ean_var'].'</g:gtin>';
						   }
		
						   $xml_feed .= '<g:mpn>'.$row_pr['kat_cislo_var'].'</g:mpn>';
		
							
							if($row_pr['dostupnost_porovnavace']==0)
							{
								$xml_feed .= '<g:availability>in stock</g:availability>';
							}
							else
							{
								$utc = time() + ($row_pr['dostupnost_porovnavace'] * 3600 * 24);
								$xml_feed .= '<g:availability>preorder</g:availability>';
								$xml_feed .= '<g:availability_date>'.date('Y-m-d\TH:i+0100',$utc).'</g:availability_date>'; // RRRR-MM-DDThh:mmZ
							}
							
							// úprava z 20.4.2023
							$data_d = Db::queryAll('SELECT nazev, cena FROM doprava WHERE aktivni=? AND google_xml=? AND specialni_doprava=?', array(1,1,$row_pr['specialni_doprava']));
							if($data_d !== false ) 
							{
								 foreach ($data_d as $row_d) 
								 {
									if(__CENY_ADM__==1)
									{
									  // ceny bez DPH
									  $cena_doprava = round($row_d['cena'] * ($row_d['dph']/100+1));
									}
									else
									{
									  // ceny s DPH
									  $cena_doprava = $row_d['cena'];
									}
											 
									$xml_feed .= '<g:shipping>
									  <g:country>CZ</g:country>
									  <g:service>'.$row_d['nazev'].'</g:service>
									  <g:price>'.$cena_doprava.' CZK</g:price>
									  <g:min_handling_time>'.__MIN_HANDLING_TIME__.'</g:min_handling_time>
									  <g:max_handling_time>'.__MAX_HANDLING_TIME__.'</g:max_handling_time>
									  <g:min_transit_time>'.__MIN_TRANSIT_TIME__.'</g:min_transit_time>
									  <g:max_transit_time>'.__MAX_TRANSIT_TIME__.'</g:max_transit_time>
									</g:shipping>';
								}
							}
		
						    $xml_feed .= '</entry>';
					}
					
					$xml_feed .= '</feed>';
					file_put_contents($xml_soubor, $xml_feed);
					return 'XML feed vygenerován'; 
					
			}
			else
			{
			    return 'Chyba při generování XML '.$_POST['t'];
		    }
    
	}
	
}
?>
