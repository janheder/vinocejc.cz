<?php

class Posta {

    private $table = 'posta';

    public function search($mesto, $psc) {

        if ($psc != '') 
        {
            $psc = intval(str_replace(' ', '', $psc));
            
            
            $data_r = Db::queryAll('SELECT * FROM posta WHERE PSC=? ', array($psc));
			
				   
        } 
        else 
        {
            if ($mesto != '') 
            {

                 $data_r = Db::queryAll("SELECT * FROM posta WHERE NAZ_PROV LIKE '".sanitize($mesto)."%' ", array());
            }
            else
                return array();
        }

        $out = array();
        

        
        if($data_r)
			{
			   
			   foreach($data_r as $row_r)
			   {
				  $out[] = $row_r;
			   }
		   }

        return $out;
    }

    public function getData($id) {

        
        $data_gd = Db::queryRow('SELECT * FROM posta WHERE ID=?  ', array(intval($id)));
	    if($data_gd['ID'])
		{
		   return $data_gd;
		}
				  
				  
    }

    public function getTags() {

        $data_r = Db::queryAll('SELECT NAZ_PROV FROM posta ', array());
        $out = "";

        
        if($data_r)
			{
			   
			   foreach($data_r as $row_r)
			   {
				  $out .= '"' . $row_r['NAZ_PROV'] . '",';
			   }
		   }
        
        $out = substr($out, 0, -1);
        return $out;
    }

}
