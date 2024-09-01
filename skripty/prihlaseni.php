<?php
// přihlášení

if($_POST['login_form_submit'] && !$_SESSION['uzivatel'])
{
   $obsah = Uzivatel::zalogujUzivatele();
  
}
elseif($_SESSION['uzivatel'])
{
   $obsah = 'Již jste přihlášen/a';	
}
else
{
   $obsah = 'Pro přihlášení použijte odkaz PŘIHLÁŠENÍ v záhlaví stránky';
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
$sablonka->pridejDoSablonky('{nadpis}','Přihlášení','html');
$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');


// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('txt'); 

 
?>
