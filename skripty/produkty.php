<?php
// produkty

// vložení do košíku
if($_POST['produkt'] && $_POST['varianta'] && $_POST['pocet'])
{
    if(kontrola_ref())
	{
	  die(kontrola_ref());
	}
	
	$K = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
	$K->vlozDoKosiku($_POST['produkt'],$_POST['varianta'],$_POST['pocet']);
	
	$okno = $K->okno($_POST['produkt'],$_POST['varianta'],$_POST['pocet']);
}
else
{
	$okno = '';
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

$produkty = new Produkty($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__);
$obsah = $produkty->zobrazProduktDetail();

$sablonka->pridejDoSablonky('{obsah}',$obsah.$okno,'html');

$Recenze_pata = new Recenze($this->skript,$this->parametry,$this->get_parametry); 
$sablonka->pridejDoSablonky('{recenze_pata}',$Recenze_pata->recenzePata(3),'html');

// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('kategorie'); 
?>
