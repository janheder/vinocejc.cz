<?php

// superglobalni promenne z DB 

class GlobalniPromenne
{
	public function generujGP()
    {

        
         $data = Db::queryAll('SELECT str,obsah FROM obecne_nastaveni ORDER BY id', array());
		

        if($data !== FALSE) 
        {
			foreach ($data as $row) 
			{
			
			  define("__".$row['str']."__",$row['obsah']); 

			
			}
		}
        
    }
	
}
?>
