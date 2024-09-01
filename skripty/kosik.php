<?php
// kosik
if($_GET['delete']=='all')
{
  // smazat celý košík
  $K = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
  $K->smazatVseZKosiku();
  $K->prepocetDopravaPlatba();
  unset($_SESSION['doprava']);
  unset($_SESSION['platba']);
}
elseif($_GET['delete'])
{
  // smazat produkt z košíku
  list($id_p,$id_var) = explode('|',strip_tags($_GET['delete']));
  $K = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
  $K->smazatZKosiku($id_p,$id_var);
  $K->prepocetDopravaPlatba();
  
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

$obsah = $Kosik->kroky();

$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');


// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('kosik'); 
?>
