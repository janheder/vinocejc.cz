<?php 
// generuje HTML části do adminu
// trida nacte zadany blok a v nem nahradi text uvozeny do {} promennou z pole s klicem se stejnym nazvem
// promenne zadavame v parametru - klic / hodnota
// v bloku muzeme pouzivat PHP kod
Class Html
{
	public $promenne_arr; // promenne k nahrazeni
	public $vygenerovany_kod;  //vygenerovany kod
	private $key; // klice k pridani nebo odstraneni
	private $value; // hodnoty k pridani
	
	function __construct()
	{
	 $this->promenne_arr = array();
	 $this->vygenerovany_kod = '';
	}
	
	public function pridejDoKodu($key,$value)
	{

		if($key)
		{
			  if(!$value)
			  {
				  $value = ' ';
			  }

			  $this->promenne_arr[$key] = $value;
			  
			  
		   
		}
		

	
	}
	
	public function odstranZKodu($key)
	{
		if($key)
		{
		  foreach($this->promenne_arr as $k=>$v)
		  {
			  if($k==$key)
			  {
			   unset($this->promenne_arr[$k]);
			  }
		  }
		}
		else
		{
		 die('chybne predane parametry');	
		}

		
		
	}

	public function generujKod($nazev_bloku)
	{

		 if(file_exists(__WEB_DIR__.'/admin/'.$nazev_bloku.'.php'))
		 {
		  $obsah = file_get_contents(__WEB_DIR__.'/admin/'.$nazev_bloku.'.php'); 
		  
			   if(is_array($this->promenne_arr))
			   {
				  $a = array_keys($this->promenne_arr);
				  $b = array_values($this->promenne_arr);
				  $this->vygenerovany_kod =  str_replace($a, $b, $obsah);
 
			   }
			   else
			   {
				//die('predane parametry nejsou pole');   
				$this->vygenerovany_kod = $obsah;
			   }

		  
		 }
		 else
		 { 
			   echo 'Blok s názvem '.$nazev_bloku.' se v adresáři admin nenachází';  
			   exit();   
	      }
		  
	 return eval_html($this->vygenerovany_kod);	
	}
	
}

?>
