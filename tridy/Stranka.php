<?php
// dle podminek a parametru z URL nacte danou stranku
class Stranka
{
	public $skript; // nazev skriptu
	public $trida; // nazev tridy
	public $parametry; // parametry z URL za lomítky
	public $get_parametry; // get parametry v poli
	public $menu;
	public $url;
	public $cela_cesta;
	public $admin_sekce = false;
	
	
	public function zpracujParam($parametry)
    {   
		$naparsovanaURL = $this->parsujURL($parametry);
		$get_par_arr = $this->parsujGET();

		if(empty($naparsovanaURL[0]))
		{
            $naparsovanaURL[0] = __HLAVNI_STRANKA__;
		}
		$trida = $this->nazevTridy($naparsovanaURL[0]);
		$skript = $naparsovanaURL[0];
		$this->cela_cesta = sanitize($naparsovanaURL);
		if($naparsovanaURL[0] == 'admin'){$admin_sekce = true;}
		unset($naparsovanaURL[0]); // odstranime  prvni parametr = nazev modulu

		
		// Nastavení skriptu
		$this->skript = sanitize($skript);
	    $this->trida = sanitize($trida);
	    $this->parametry = sanitize($naparsovanaURL);
	    $this->get_parametry = sanitize($get_par_arr);
	    $this->admin_sekce = $admin_sekce;
	    if($_SESSION['staticke_stranky']){$this->menu = sanitize($_SESSION['staticke_stranky']);}
		
 
    }
	
	
	private function parsujURL($url)
	{
		
        $naparsovanaURL = parse_url($url);
		$naparsovanaURL["path"] = ltrim($naparsovanaURL["path"], "/");
		$naparsovanaURL["path"] = trim($naparsovanaURL["path"]);
		$rozdelenaCesta = explode("/", $naparsovanaURL["path"]);
		
		return $rozdelenaCesta;
	    
	}
	
	
	private function parsujGET()
	{
		 
        if($_GET)
		{
			// pokud mame GET parametry tak přidáme do pole
			foreach($_GET as $gk=>$gv)
			{
				$get_par[$gk] = sanitize($gv);
			}
			
			return $get_par;
		}
		
		return false;		
		
	}
    
	
	
	private function nazevTridy($text)
	{
		$nazev = str_replace('-', ' ', $text);
		$nazev = ucwords($nazev);
		$nazev = str_replace(' ', '', $nazev);
		
		return $nazev;
		
	}
	
	
	public function presmeruj($url)
	{
		header("Location: $url");
		header("Connection: close");
        exit();
	}
	

	public function nactiStranku()
	{	 
  
	  if($this->skript && $this->admin_sekce==false)
	  {   

		  
		 if(file_exists(__WEB_DIR__.'/skripty/'.$this->skript.'.php'))
		 {
		     include_once(__WEB_DIR__.'/skripty/'.$this->skript.'.php');
		  
		 }
		 elseif(array_key_exists($this->skript,$this->menu))
         {  
			 // volame sablonku pro staticke stranky
             $data_str = Db::queryRow("SELECT id, nadpis, obsah, id_fotogalerie FROM stranky WHERE str = ? AND aktivni=1  ", array($this->skript));
             $fotogalerie = '';
             
             if($data_str['id_fotogalerie'])
             {
			   // vygenerujeme fotogalerii
                $data_fg = Db::queryAll('SELECT * FROM fotogalerie_fotky WHERE id_fotogalerie=? AND aktivni=? ORDER BY razeni ASC ', array($data_str['id_fotogalerie'],1));
				if($data_fg)
				{	
				   $fotogalerie .= '<div class="fotogalerie-wrap">';
					
				   foreach($data_fg as $row_fg)
				   {
					 $fotogalerie .= '<a href="/fotky/galerie/velke/'.$row_fg['foto'].'" data-lightbox="image-1" data-title="'.$row_fg['nazev'].'"><img src="/fotky/galerie/male/'.$row_fg['foto'].'" title="'.$row_fg['nazev'].'" class="fotogalerie"></a>';
				   }
				   $fotogalerie .= '</div>';
			    }
			 }
										
          
             // generujeme pomoci tridy SEO parametry do headeru (keywords,title,descriptiom,og,noindex/index/nofollow/follow,canonical atd...)
             // pak predavame do sablonky


		    $sablonka = new Sablonka();	
			// generujeme SEO meta
			$Seo = new Seo($this->skript,$_SESSION['staticke_stranky'],$this->parametry,$this->get_parametry);
			$sablonka->pridejDoSablonky('{seometa}',$Seo->generujSeo(),'html');
 
			
			$Topmenu = new TopMenu($this->parametry);
			$sablonka->pridejDoSablonky('{top_menu}',$Topmenu->zobrazTopMenu(),'html'); 
			$sablonka->pridejDoSablonky('{menu_kategorii}',$Topmenu->menuKategorieUvod(),'html'); 
			
			$Kosik = new Kosik($this->skript,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
			$sablonka->pridejDoSablonky('{kosik}',$Kosik->kosikTop(),'html');

            $sablonka->pridejDoSablonky('{drobinka}',$Seo->generujDrobinku(),'html');
			$sablonka->pridejDoSablonky('{nadpis}',$data_str['nadpis'],'html');
			$sablonka->pridejDoSablonky('{obsah}',stripslashes($data_str['obsah']).$fotogalerie,'html');
			
			$Recenze_pata = new Recenze($this->skript,$this->parametry,$this->get_parametry); 
			$sablonka->pridejDoSablonky('{recenze_pata}',$Recenze_pata->recenzePata(3),'html');
			
			// info okno
			$Infookno = new InfoOkno($this->skript);
			$sablonka->pridejDoSablonky('{info_okno}',$Infookno->generujOkno(),'html');

			 echo $sablonka->generujSablonku('txt');
		  
		  
           // navyseni poctu zobrazeni
		   @$result = Db::updateS('UPDATE stranky SET precteno=precteno+1 WHERE str= "'.$this->skript.'"  ');
           

	     }
    	 else
	     {
			// stranka neexistuje
		    // header("HTTP/1.0 404 Not Found");
			// header("Location: /404");
			include('./skripty/404.php');
			exit();  

				 
	     }  
	   }	
	   else
	   {
		   // admin sekce
		    var_dump($this->skript);
		    var_dump($this->parametry);
		    var_dump($_SERVER['REQUEST_URI']);

 
	   }
	 }

}
?>
