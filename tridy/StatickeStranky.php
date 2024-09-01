<?php

// stranky z DB 

class StatickeStranky
{
	public function generujStrSess()
    {

         $data = Db::queryAll('SELECT str,nadpis_menu FROM stranky ORDER BY id', array());
         $staticke_stranky = array();
		

        if($data !== FALSE) 
        {
			foreach ($data as $row) 
			{
			   $staticke_stranky[$row['str']] = $row['nadpis_menu'];
			
			}
			
			if(count($staticke_stranky)>0)
			{
				 $_SESSION['staticke_stranky'] = $staticke_stranky; 
				
				
			}
			
			
		}
		
		
        
	}
	
}
?>
