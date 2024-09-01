<?php
// trida banner
// do tridy predavame 2 typy parametru v polich

class Banner
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
	
	
	
	public function zobrazBannery()
	{ 
		    $b = '';
		    $data_b = Db::queryAll('SELECT * FROM bannery WHERE aktivni=? ORDER BY id DESC', array(1));
			if($data_b)
			{
			   
			   foreach($data_b as $row_b)
			   {
				   $b .= '<div class="item">
                <picture> 
                  <source media="(max-width:575px)" width="575" height="540" srcset="/prilohy/b/'.$row_b['banner'].'">
                  <source media="(max-width:991px)" width="991" height="540" srcset="/prilohy/b/'.$row_b['banner'].'"><img src="/prilohy/b/'.$row_b['banner'].'" width="1420" height="540" 
                  alt="'.$row_b['nazev'].'" title="'.$row_b['nazev'].'">
                </picture><a href="'.$row_b['url'].'" aria-label="'.$row_b['nazev'].'"></a>
              </div>';
			   }
		    
		    }
		    
		    return $b;
		
	}
	
	

	


}
