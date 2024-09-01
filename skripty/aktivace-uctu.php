<?php
// přihlášení

if($_GET['eml_ver'])
{
   // rozparsujeme parametr
   $eml_ver = str_replace(' ','+',$_GET['eml_ver']);
   $eml_ver = openssl_decrypt($eml_ver, __IV_CIPHERING__, __HESLO_ENCRYPT__, 0, __IV__);	
   list($id_zak,$cas) = explode('|',$eml_ver);

   if($cas < (time() - 86400 ))
   {
        // link je starší jak 24 hodin
        $obsah =  'Váš účet NEBYL aktivován, protože lhůta 24 hodin od registrace už uběhla.';
   }
   else
   {
		$data_update['aktivni'] = 1;
				
		$where_update = array('id' => intval($id_zak));
		$query_update = Db::update('zakaznici', $data_update, $where_update);
		if($query_update)		
		{
		   $obsah =  'Váš účet byl právě aktivován. Přihlásit se můžete vpravo nahoře.';
	    }
	    else
	    {
		   $obsah =  'Váš účet se nepodařilo aktivovat.';
		}
   }
   
   
  
}
else
{
   $obsah = 'Chybí parametry';
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
$sablonka->pridejDoSablonky('{nadpis}','Aktivace účtu','html');
$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');
$sablonka->pridejDoSablonky('{recenze_pata}','','html');

// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('txt'); 

 
?>
