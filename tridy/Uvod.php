<?php
// trida úvod
// do tridy predavame 2 typy parametru v polich

class Uvod
{

public $parametry; // parametry za lomitky
public $get_parametry;  // klasicke GET parametry
public $skript; // nazev stranky
	
	
	function __construct($skript,$parametry,$get_parametry)
	{
	 $this->skript = $skript;
	 $this->parametry = $parametry;
	 $this->get_parametry = $get_parametry;

	}
	
	
	
	public function zobrazKategorieUvod()
	{ 
		// kategorie pro úvodku
	   $kat = '';
	   $data_k = Db::queryAll('SELECT id, str, nazev, popis, foto FROM kategorie WHERE aktivni=? AND na_uvod=? ORDER BY razeni ', array(1,1));
	   if($data_k)
	   {
			   
			   foreach($data_k as $row_k)
			   {
				   
				   if($row_k['foto'])
				   {
					  $foto = $row_k['foto'];
				   }
				   else
				   {
					 $foto = 'category-thumb-medium.png';
				   }
				   
			        $kat .= ' <div class="col-12 col-md-3">   
		              <div class="categories-single"><img class="lazyload" src="/img/load-symbol.svg" data-src="/fotky/kategorie/male/'.$foto.'" alt="'.$row_k['nazev'].'" title="'.$row_k['nazev'].'" ><a class="categories-single__link" href="/kategorie/'.$row_k['str'].'-'.$row_k['id'].'">'.$row_k['nazev'].'</a>
		                <p class="categories-single__text">'.$row_k['popis'].'</p>
		              </div>
		            </div>';
			   }
	    }
	    
	    return $kat;
	    
	}
	
	

	


}
