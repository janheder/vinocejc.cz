<?php
// reklamace

  
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
$sablonka->pridejDoSablonky('{nadpis}','','html');

$reklamace = new Reklamace($this->skript,$this->parametry,$this->get_parametry); 
$reklamace_obsah = $reklamace->zobrazForm();
$sablonka->pridejDoSablonky('{obsah}',$reklamace_obsah,'html');

/*
$Aktuality_pata = new Aktuality($this->skript,$this->parametry,$this->get_parametry); 
$sablonka->pridejDoSablonky('{aktuality_pata}',$Aktuality_pata->aktualityUvod(3),'html');
  */
  // info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');


echo $sablonka->generujSablonku('registrace'); 






?>
