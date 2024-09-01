<?php
// generujeme obsah pro úvodku		
unset($_SESSION['last_category']);
$produkty = new Produkty($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__);
$obsah = $produkty->zobrazProduktyNovinky();

  
$sablonka = new Sablonka();	
// generujeme SEO meta
$Seo = new Seo($this->skript,$_SESSION['staticke_stranky'],$this->parametry,$this->get_parametry);
$sablonka->pridejDoSablonky('{seometa}',$Seo->generujSeo(),'html');

$Topmenu = new TopMenu($this->skript,$this->parametry);
$sablonka->pridejDoSablonky('{infolista}',$Topmenu->infoLista(1),'html'); 
$sablonka->pridejDoSablonky('{top_menu}',$Topmenu->zobrazTopMenu(),'html'); 
$sablonka->pridejDoSablonky('{menu_kategorii}',$Topmenu->menuKategorieUvod(),'html'); 

$Kosik = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
$sablonka->pridejDoSablonky('{kosik}',$Kosik->kosikTop(),'html');

$Bannery = new Banner($this->skript,$this->parametry,$this->get_parametry);
$sablonka->pridejDoSablonky('{banner}',$Bannery->zobrazBannery(),'html');

$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');

$Kategorie_uvod = new Uvod($this->skript,$this->parametry,$this->get_parametry);
$sablonka->pridejDoSablonky('{kategorie_uvod}',$Kategorie_uvod->zobrazKategorieUvod(),'html');

$Aktuality_pata = new Aktuality($this->skript,$this->parametry,$this->get_parametry); 
$sablonka->pridejDoSablonky('{aktuality_pata}',$Aktuality_pata->aktualityUvod(3),'html');

$Recenze_pata = new Recenze($this->skript,$this->parametry,$this->get_parametry); 
$sablonka->pridejDoSablonky('{recenze_pata}',$Recenze_pata->recenzePata(3),'html');

// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('uvod'); 
?>
