<?php
// 404
header("HTTP/1.0 404 Not Found");

$kod_404 = ' <div class="hero__404">
          <h1 class="hero__title">Kód 404 - stránka nenalezena</h1>
          <p class="hero__text">Požadovanou stránku se nepodařilo nalézt. Zkuste přejít na <a href="/">úvodní stránku</a>.</p>
        </div>';

$sablonka = new Sablonka();	

// generujeme SEO meta
$Seo = new Seo('404',$_SESSION['staticke_stranky'],$this->parametry,$this->get_parametry);
$sablonka->pridejDoSablonky('{seometa}',$Seo->generujSeo(),'html');

$Topmenu = new TopMenu($this->skript,$this->parametry);
$sablonka->pridejDoSablonky('{infolista}',$Topmenu->infoLista(2),'html'); 
$sablonka->pridejDoSablonky('{top_menu}',$Topmenu->zobrazTopMenu(),'html'); 
$sablonka->pridejDoSablonky('{menu_kategorii}',$Topmenu->menuKategorieUvod(),'html'); 
 
$Kosik = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
$sablonka->pridejDoSablonky('{kosik}',$Kosik->kosikTop(),'html');
 
$sablonka->pridejDoSablonky('{drobinka}',$Seo->generujDrobinku(),'html');
$sablonka->pridejDoSablonky('{nadpis}','Nenalezeno','html');
$sablonka->pridejDoSablonky('{obsah}',$kod_404,'html');

$sablonka->pridejDoSablonky('{recenze_pata}','','html');

// info okno
$Infookno = new InfoOkno('404');
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('txt'); 
?>

