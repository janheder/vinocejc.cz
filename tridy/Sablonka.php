<?php
// trida sablonky nacte zadanou sablonku a v ni nahradi text uvozeny do {} promennou z pole s klicem se stejnym nazvem
// promenne zadavame v parametru - klic / hodnota
// v sablonce muzeme pouzivat PHP kod
// typ = html nebo txt (u txt volame nl2br)
class Sablonka
{

public $promenne_arr; // promenne k nahrazeni
public $vygenerovany_kod;  //vygenerovany kod
public $key; // klice k pridani nebo odstraneni
public $value; // hodnoty k pridani
public $nazev_sablonky; // nazev sablonky, pokud neni nalezena, pouzije se default a je vypsana chybova hlaska
public $typ;

	function __construct()
	{
	 $this->promenne_arr = array();
	 $this->vygenerovany_kod = '';
	}

	public function pridejDoSablonky($key,$value,$typ)
	{

		if($key)
		{
		  if($typ=='txt')
		   {
			  $this->promenne_arr[$key] = nl2br($value);
		   }
		   else
		   {
			  $this->promenne_arr[$key] = $value;
		   }
		}
		else
		{
		   $sablonka = new Sablonka();
		   $sablonka->PridejDoSablonky('{kod}','Chybí klíč '.$key.' ','html');
		   echo $sablonka->GenerujSablonku('404'); 
		   exit(); 
		}

		
	}
	
	
	
	public function odstranZeSablonky($key)
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
	
	
	
	public function generujSablonku($nazev_sablonky)
	{

		 if(file_exists(__WEB_DIR__.'/sablonky/'.$nazev_sablonky.'.php'))
		 {
		       $obsah = file_get_contents(__WEB_DIR__.'/sablonky/'.$nazev_sablonky.'.php'); 
 
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
			   $sablonka = new Sablonka();
			   $sablonka->pridejDoSablonky('{kod}','Šablonka s názvem '.$nazev_sablonky.' se v adresáři '.__WEB_DIR__.' nenachází','html');  
		       echo $sablonka->generujSablonku('404'); 
			   exit();   
	      }
		  
	 return eval_html($this->vygenerovany_kod);	
	 
	}


}
 
?> 
