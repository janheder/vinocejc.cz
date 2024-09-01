<?php
// smazat-ucet

if($_SESSION['uzivatel'])
{
    if($_SESSION['uzivatel']['id'])
		{
			
			// kontrolujeme jestli nemá objednávky
			$pocet_objednavek = Db::queryCount2('objednavky','id','','id_zakaznik='.intval($_SESSION['uzivatel']['id']));
			
			if($pocet_objednavek>0)
			{
				// má objednávky
				// pouze ho zneaktivníme
				$data_update['aktivni'] = 0;
				$where_update = array('id' => intval($_SESSION['uzivatel']['id']));
				$query_update = Db::update('zakaznici', $data_update, $where_update);
				
				unset($_SESSION['uzivatel']);
				$obsah = '<div class="alert-success">Váš účet byl nenávratně smazán.</div>';
			}
			else
			{
				$where_delete = array('id' => intval($_SESSION['uzivatel']['id']));
				$query_delete = Db::delete('zakaznici', $where_delete); 
				if($query_delete)
				{
					unset($_SESSION['uzivatel']);
					$obsah = '<div class="alert-success">Váš účet byl nenávratně smazán.</div>';
				}
			}
			
			
		}
  
}
else
{
   $obsah = 'Pro smazání účtu se musíte nejdříve přihlásit.';
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
$sablonka->pridejDoSablonky('{nadpis}','Smazání účtu','html');
$sablonka->pridejDoSablonky('{obsah}',$obsah,'html');


// info okno
$Infookno = new InfoOkno($this->skript);
$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

echo $sablonka->generujSablonku('txt'); 

 
?>
