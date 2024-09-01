<?
// třida seo generuje seo parametry do stránek pro title, keywords, description, drobinku, OG, robots...

class Seo
{
private $p; // aktualni stranka
public $stranky; // pole stránek z db
public $parametry; // pole parametrů z URL
public $get_parametry; // pole parametrů z URL

	function __construct($p,$stranky,$parametry,$get_parametry)
	{
	 $this->p = sanitize($p);
	 $this->stranky = $stranky;
	 $this->parametry = $parametry;
	 $this->get_parametry = $get_parametry;
 
	}
	
	public function generujDrobinku()
	{
		$ret = '<a class="breadcrumbs__item" href="/">Domů</a>';
 
		 if($this->p=='produkty' && $this->parametry[1])
		 {
			 // detail produktu
			 $id_pr_arr_e = explode('-',$this->parametry[1]);
	         $id_pr_arr = array_reverse($id_pr_arr_e);
	         $id_pr = intval($id_pr_arr[0]);
	         
			 $data_p = Db::queryRow('SELECT nazev, id_kat_arr FROM produkty WHERE id = ?', array($id_pr));
			 if($data_p)
			 {
				  // kategorie
				  if($data_p['id_kat_arr'])
				  {

					
					$id_kat_arr = array_reverse(unserialize($data_p['id_kat_arr']));

					if($_SESSION['last_category'])
					{
						// úprava z 24.9.2021
						// pokud jsem v detailu a dostal jsem se tam z nějaké jiné stránky než z kategorií kam produkt patří
						// tak musíme prověřit jestli ID kat. v sešně u daného produktu existuje
						if(in_array($_SESSION['last_category'], $id_kat_arr))
						{
							$id_kat = $_SESSION['last_category'];
						}
						else
						{
							$id_kat = $id_kat_arr[0];
						}
						
						
					}
					else
					{
						$id_kat = $id_kat_arr[0];
					}
					
					$data_k = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($id_kat));
					 if($data_k)
					 {
						  if($data_k['vnor']==3)
						  {
							 $data_k3 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k['id_nadrazeneho']));
							 if($data_k3)
							 {	  	
								 $data_k2 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k3['id_nadrazeneho']));
								 if($data_k2)
								 {
							        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
							        <a class="breadcrumbs__item" href="/kategorie/'.$data_k2['str'].'-'.$data_k2['id'].'">'.$data_k2['nazev'].'</a>';
							        
							        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
							        <a class="breadcrumbs__item" href="/kategorie/'.$data_k3['str'].'-'.$data_k3['id'].'">'.$data_k3['nazev'].'</a>';
							        
							        
							        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
							        <a class="breadcrumbs__item" href="/kategorie/'.$data_k['str'].'-'.$data_k['id'].'">'.$data_k['nazev'].'</a>';
							     }
							 }
							 
						  }
						  elseif($data_k['vnor']==2)
						  {
							 $data_k2 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k['id_nadrazeneho']));
							 if($data_k2)
							 {
						        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
						        <a class="breadcrumbs__item" href="/kategorie/'.$data_k2['str'].'-'.$data_k2['id'].'">'.$data_k2['nazev'].'</a>';
						        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
						        <a class="breadcrumbs__item" href="/kategorie/'.$data_k['str'].'-'.$data_k['id'].'">'.$data_k['nazev'].'</a>';
						     }
						  }
						  elseif($data_k['vnor']==1)
						  {
						     $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
						      <a class="breadcrumbs__item" href="/kategorie/'.$data_k['str'].'-'.$data_k['id'].'">'.$data_k['nazev'].'</a>';
						  }  
						  			
					 }
					
					
				    
			      }
			      $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">';
				  
				  $ret .= '<span class="breadcrumbs__item --active">'.$data_p['nazev'].'</span>';
				  			
			 }

		 }
		 elseif($this->p=='kategorie' && $this->parametry[1])
		 {
			 // musíme zjistit jakou má kategorie úroveň
			 $id_kat_arr_e = explode('-',$this->parametry[1]);
			 $id_kat_arr = array_reverse($id_kat_arr_e);
			 $id_kat = intval($id_kat_arr[0]);
			 $data_k = Db::queryRow('SELECT id, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($id_kat));
			 if($data_k)
			 {
				  if($data_k['vnor']==3)
				  {
					 $data_k3 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k['id_nadrazeneho']));
					 if($data_k3)
					 {	  	
						 $data_k2 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k3['id_nadrazeneho']));
						 if($data_k2)
						 {
					        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
					        <a class="breadcrumbs__item" href="/kategorie/'.$data_k2['str'].'-'.$data_k2['id'].'">'.$data_k2['nazev'].'</a>';
					        
					        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
					        <a class="breadcrumbs__item" href="/kategorie/'.$data_k3['str'].'-'.$data_k3['id'].'">'.$data_k3['nazev'].'</a>';
					        
					        
					        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;"><span class="breadcrumbs__item --active">'.$data_k['nazev'].'</span>';
					     }
					 }
					 
				  }
				  elseif($data_k['vnor']==2)
				  {
					 $data_k2 = Db::queryRow('SELECT id, str, vnor, id_nadrazeneho, nazev FROM kategorie WHERE id = ?', array($data_k['id_nadrazeneho']));
					 if($data_k2)
					 {
				        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
				        <a class="breadcrumbs__item" href="/kategorie/'.$data_k2['str'].'-'.$data_k2['id'].'">'.$data_k2['nazev'].'</a>';
				        $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;"><span class="breadcrumbs__item --active">'.$data_k['nazev'].'</span>';
				     }
				  }
				  elseif($data_k['vnor']==1)
				  {
				     $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;"><span class="breadcrumbs__item --active">'.$data_k['nazev'].'</span>';
				  }  
				  			
			 }
		 
		 }
		 elseif($this->p=='aktuality' && $this->parametry[1])
		 {
			 // aktuality detail článku
			 $id_akt_arr_e = explode('-',$this->parametry[1]);
			 $id_akt_arr = array_reverse($id_akt_arr_e);
			 $id_akt = intval($id_akt_arr[0]);
			 
			 $data_a = Db::queryRow('SELECT nadpis FROM aktuality WHERE id = ?', array($id_akt));
			 if($data_a)
			 {
				  $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;"><a class="breadcrumbs__item" href="/aktuality">Aktuality</a>';
				  $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;"><span class="breadcrumbs__item --active">'.$data_a['nadpis'].'</span>';
				  			
			 }

		 }
		 elseif($this->p=='aktuality' && !$this->parametry[1])
		 {
			 // aktuality - přehled
			 $ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;"><span class="breadcrumbs__item --active">Aktuality</span>';
		 }
		 elseif(array_key_exists($this->p,$this->stranky))
			{ 
				$data_stranky = Db::queryRow('SELECT nadpis FROM stranky WHERE str = ?', array(sanitize($this->p)));
				if($data_stranky)
				{
					$ret .= '<img class="breadcrumbs__separator" src="/img/icons/cheveron.svg" alt="&gt;">
					<span class="breadcrumbs__item --active">'.$data_stranky['nadpis'].'</span>';
				}
			}
		 
		 
		  return $ret;
		
		
	}
	

	
	public function generujSeo()
	{
 
		// generujeme všechny hlavní metatagy
		$seoreturn = '';
		$title_dop = '';
		if($this->p=='produkty')
		{
			
			if($this->parametry[1])
			{
				$id_p_arr_e = explode('-',$this->parametry[1]);
			    $id_p_arr = array_reverse($id_p_arr_e);
			    $id_p = intval($id_p_arr[0]);
	     
				// zjistíme jestli se jedná o detail produktu
				$data_p = Db::queryRow('SELECT P.nazev, P.popis_kratky, P.title, P.keywords, P.description, P.og_title, P.og_description, P.og_site_name, P.og_url, P.og_image, P.indexovani
				FROM produkty P 
				WHERE P.id = ? ', array($id_p));
				if($data_p)
				{
				     if($data_p['title'])
					  {
						  $title = $data_p['title'];
					  }
					  else
					  {
						  $title = $data_p['nazev'].' | '.__TITLE__;
					  }
					  // keywords
					  if($data_p['keywords'])
					  {
						  $keywords = $data_p['keywords'];
					  }
					  else
					  {
						  $keywords = $data_p['nazev'].', ';
						  $keywords .= __KEYWORDS__;
					  }
					  
					  // description
					  if($data_p['description'])
					  {
						  $description = $data_p['description'];
					  }
					  else
					  {
						  //$description = 'Detail produktu '.$data_p['nazev'].', '.__DESCRIPTION__;
						  $description = cut_text($data_p['popis_kratky'],160).', '.__DESCRIPTION__;
					  }
					  
					  // robots
					  if($data_p['indexovani']==1){$robots = 'index,follow,snippet,archive';}
				      else{$robots = 'noindex,nofollow';}
				      
				      // og:image
					  $data_foto = Db::queryRow('SELECT foto FROM produkty_foto WHERE id_produkt = ?', array($id_p));
					  if($data_foto){$og_image = __URL__.'/fotky/produkty/male/'.$data_o['foto'];}
					  else{$og_image = __URL__.'/img/logo.svg';}
					  
					  $canonical = $_SERVER['REDIRECT_URL'];
					  			
				}


		    }
		    else
		    {
				

				// bez parametru
				$title = __TITLE__;
				$robots = 'index,follow,snippet,archive';
				$og_image = __URL__.'/img/logo.svg';
				$canonical = $_SERVER['REQUEST_URI'];
				
			}
			
		}
		elseif($this->p=='kategorie')
		{
			if($this->parametry[1])
			{
				 $id_kat_arr_e = explode('-',$this->parametry[1]);
				 $id_kat_arr = array_reverse($id_kat_arr_e);
				 $id_kat = intval($id_kat_arr[0]);
				 $data_k = Db::queryRow('SELECT * FROM kategorie WHERE id = ?', array($id_kat));
				 if($data_k)
				 {
	
					  if($data_k['title'])
					  {
						  $title = $data_k['title'];
					  }
					  else
					  {
						  $title = $data_k['nazev'].' | '.__TITLE__; 
					  }
					  // keywords
					  if($data_k['keywords'])
					  {
						  $keywords = $data_k['keywords'];
					  }
					  else
					  {
						  $keywords = $data_k['nazev'].', ';
						  $keywords .= __KEYWORDS__;
						  
					  }
					  
					  // description
					  if($data_k['description'])
					  {
						  $description = $data_k['description'];
					  }
					  else
					  {
						  $description = cut_text(strip_tags($data_k['popis']),160).', '.__DESCRIPTION__;
					  }
					  
					  // robots 
					  // úprava z 5.8.2022
					  
					  if($data_k['indexovani']==1)
					  {
					       if($_GET['page'] && $_GET['page'] > 1)
			               {
							  $robots = 'noindex,follow';
						   }
						   else
						   {
							  $robots = 'index,follow,snippet,archive';
						   }
					  }
					  else{$robots = 'noindex,nofollow';}
					  
					  $canonical = $_SERVER['REDIRECT_URL'];
					  
					  // og:image
					  $data_foto = Db::queryRow('SELECT foto FROM kategorie WHERE id = ?', array($id_kat));
					  if($data_foto){$og_image = __URL__.'/fotky/kategorie/male/'.$data_foto['foto'];}
					  else{$og_image = __URL__.'/img/logo.svg';}
						  			
				 }
			}

		}
		elseif($this->p=='aktuality')
		{
			if($this->parametry[1])
			{
				$id_akt_arr_e = explode('-',$this->parametry[1]);
				$id_akt_arr = array_reverse($id_akt_arr_e);
				$id_akt = intval($id_akt_arr[0]);
				 
				$data_d = Db::queryRow('SELECT id, nadpis, obsah, title, keywords, description, perex, indexovani 
				FROM aktuality
				WHERE aktivni= ? AND id = ?', array(1,$id_akt));
				if($data_d)
				{
				  // detail
				  // title
				  if($data_d['title'])
				  {
					  $title = $data_d['title'];
				  }
				  else
				  {
					  $title = $data_d['nadpis'].' | '.__TITLE__; 
				  }
				  // keywords
				  if($data_d['keywords'])
				  {
					  $keywords = $data_d['keywords'];
				  }
				  else
				  {
					  $keywords = 'aktuality, '.$data_d['nadpis'].', ';
					  $keywords .= __KEYWORDS__;

				  }
				  
				  // description
				  if($data_d['description'])
				  {
					  $description = $data_d['description'];
				  }
				  else
				  {
					  $description = cut_text(strip_tags($data_d['obsah']),200).', '.__DESCRIPTION__;
				  }
				  
				  // robots
				  if($data_d['indexovani']==1){$robots = 'index,follow,snippet,archive';}
				  else{$robots = 'noindex,nofollow';}
				  
				  $canonical = $_SERVER['REDIRECT_URL'];
				  
				  // og:image
				  $data_foto = Db::queryRow('SELECT foto FROM aktuality WHERE id = ?', array($id_akt));
				  if($data_foto){$og_image = __URL__.'/fotky/aktuality/male/'.$data_foto['foto'];}
				  else{$og_image = __URL__.'/img/logo.svg';}
				  
				  
				
			
				}
 
			}
			else
			{
			// bez parametru
			// výpis aktualit

			$title = 'Aktuality | '.__TITLE__;
			$keywords = 'aktuality, '.__KEYWORDS__;
			$description = __DESCRIPTION__;
			$robots = 'index,follow,snippet,archive';
			$og_image = __URL__.'/img/logo.svg';
			$canonical = $_SERVER['REDIRECT_URL'];
			}
			
			
			
		}
		/*elseif($this->p=='uvod')
		{

			$title = __TITLE__;
			$keywords = __KEYWORDS__;
			$description = __DESCRIPTION__;
			
			
	    }*/
	    elseif($this->p=='404')
		{

			$title = '404 Nenalezeno';
			$keywords = __KEYWORDS__;
			$description = __DESCRIPTION__;
			
	    }
		else
		{   
			// statické stránky
			if(array_key_exists($this->p,$this->stranky))
			{ 
				$data_stranky = Db::queryRow('SELECT nadpis, obsah, title, keywords, description, indexovani FROM stranky WHERE str = ?', array(sanitize($this->p)));
				if($data_stranky)
				{
				   //title
				   if($data_stranky['title'])
				   {
					   $title = $data_stranky['title'];
				   }
				   else
				   {
					   $title = $data_stranky['nadpis'].' | '.__TITLE__; 
				   }
				   
				   // keywords
				   if($data_stranky['keywords'])
				   {
					  $keywords = $data_stranky['keywords'];
				   }
				   else
				   {
					  $keywords = $data_stranky['nadpis'].', ';
					  $keywords .= __KEYWORDS__;
				   }
				   
				   // description
				   if($data_stranky['description'])
				   {
					  $description = $data_stranky['description'];
				   }
				   else
				   {
					   $description = cut_text(strip_tags($data_stranky['obsah']),200).', '.__DESCRIPTION__;
				   }
				   
				   // robots
				   if($data_stranky['indexovani']==1){$robots = 'index,follow,snippet,archive';}
				   else{$robots = 'noindex,nofollow';}
				   $og_image = __URL__.'/img/logo.svg';
				   
				   $canonical = $_SERVER['REDIRECT_URL'];
						   
				}
			}
		}
		
		

		$default_title = __TITLE__;
		$seo_lang = 'cs';
		$seo_og_lang = 'cs_CZ';
		$seo_og_sitename = __TITLE__;

		
		$seo_og_url = __URL__.$_SERVER['REQUEST_URI'];
		
		$seoreturn .= '<title>'.trim($title).'</title>'."\n";
		$seoreturn .= '<meta name="description" content="'.$description.'" >'."\n";
		$seoreturn .= '<meta name="keywords" content="'.$keywords.'" lang="'.$seo_lang.'" >'."\n";
		$seoreturn .= '<meta name="robots" content="'.$robots.'" >'."\n"; 
		$seoreturn .= '<meta property="og:url" content="'.$seo_og_url.'" >'."\n";
		$seoreturn .= '<meta property="og:site_name" content="'.$seo_og_sitename.'" >'."\n";
		$seoreturn .= '<meta property="og:type" content="website" >'."\n";
		$seoreturn .= '<meta property="og:title" content="'.$title.'" >'."\n";
		$seoreturn .= '<meta property="og:description" content="'.$description.'" >'."\n";
		$seoreturn .= '<meta property="og:image" content="'.$og_image.'" >'."\n";
		$seoreturn .= '<meta property="og:image:secure_url" content="'.$og_image.'" >'."\n";
		$seoreturn .= '<meta property="og:locale" content="'.$seo_og_lang.'" >'."\n";  
		$seoreturn .= '<meta name="twitter:card" content="summary">'."\n";  
		if(__TWITTER_SITE__)
		{
			$seoreturn .= '<meta name="twitter:site" content="@'.__TWITTER_SITE__.'">'."\n";  
		}
		$seoreturn .= '<meta name="twitter:title" content="'.$title.'">'."\n";  
		$seoreturn .= '<meta name="twitter:description" content="'.$description.'">'."\n";   
		$seoreturn .= '<meta name="twitter:image" content="'.$og_image.'">'."\n";
		
		// ověření facebook
		if(__FB_OVERENI__)
		{
		$seoreturn .= '<meta name="facebook-domain-verification" content="'.__FB_OVERENI__.'">'."\n";
		}
		
		$seoreturn .= '<link rel="alternate" hreflang="cs-cs" href="'.__URL__.''.$_SERVER['REQUEST_URI'].'">'."\n"; 
		
		// úprava z 24.2.2021 - pro úvod nám REQUEST_URI nic nevrací
		if($canonical || $this->p=='uvod')
		{
		  $seoreturn .= '<link rel="canonical" href="'.__URL__.$canonical.'">';
		}
		
		// úprava z 5.8.2022
		if($this->p=='kategorie')
		{
			$kat_pocet = new Produkty($this->p,$this->parametry,$this->get_parametry,__TYP_CENY__);
			$pocet_kat_seo = $kat_pocet->pocetKat();
			
			$predchozi = ($_GET['page'] - 1);
			$dalsi = ($_GET['page'] + 1);
			$aktualni = intval($_GET['page']);
			
		    if(!$_GET['page']  && $pocet_kat_seo > 1)
		    {
				$seoreturn .= '<link rel="next" href="'.$seo_og_url.'?page=2" />';
			}
			elseif($_GET['page'] == 1 && $pocet_kat_seo > 1)
		    {
				$url_b_d = str_replace('page=1','page=2',$seo_og_url);
				$seoreturn .= '<link rel="next" href="'.$url_b_d.'" />';
			}
			elseif($_GET['page'] > 1 && $_GET['page'] < $pocet_kat_seo)
			{
				
				$url_b_p = str_replace('page='.$aktualni,'page='.$predchozi,$seo_og_url);
				$url_b_d = str_replace('page='.$aktualni,'page='.$dalsi,$seo_og_url);
				
				$seoreturn .= '<link rel="prev" href="'.$url_b_p.'" />';
				$seoreturn .= '<link rel="next" href="'.$url_b_d.'" />';
			}
			elseif($_GET['page'] > 1 && $_GET['page'] == $pocet_kat_seo)
			{
			   $url_b_p = str_replace('page='.$aktualni,'page='.$predchozi,$seo_og_url);
			   
			   $seoreturn .= '<link rel="prev" href="'.$url_b_p.'" />';
			}
		}
	
		
		$seoreturn .= __GOOGLE_GTM_SCRIPT__."\n";
		$seoreturn .= __GA_KOD__."\n";
		
		// heureka
		if(__HEUREKA_OVERENO__==1)
	    {
		   $seoreturn .= "<script type=\"text/javascript\">
			//<![CDATA[
			var _hwq = _hwq || [];
			    _hwq.push(['setKey', '".__HEUREKA_KEY__."']);_hwq.push(['setTopPos', '60']);_hwq.push(['showWidget', '".__HEUREKA_OVERENO_UMISTENI__."']);(function() {
			    var ho = document.createElement('script'); ho.type = 'text/javascript'; ho.async = true;
			    ho.src = 'https://www.heureka.cz/direct/i/gjs.php?n=wdgt&sak=".__HEUREKA_KEY__."';
			    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ho, s);
			})();
			//]]>
			</script>";
		}
		
		// fb pixel
		if(__FB_PIXEL__)
		{
			$seoreturn .= "<!-- Meta Pixel Code -->
			<script type=\"text/plain\" data-cookiecategory=\"marketing\">
			  !function(f,b,e,v,n,t,s)
			  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
			  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
			  n.queue=[];t=b.createElement(e);t.async=!0;
			  t.src=v;s=b.getElementsByTagName(e)[0];
			  s.parentNode.insertBefore(t,s)}(window, document,'script',
			  'https://connect.facebook.net/en_US/fbevents.js');
			  fbq('init', '".__FB_PIXEL__."');
			  fbq('track', 'PageView');";
			  
			  // další události FB Pixel
			  
			  // zobrazení detailu produktu
			  if($this->p=='produkty' && $this->parametry)
			  {
	  		     // získáme ID 
			     $id_pr_arr_e = explode('-',$this->parametry[1]);
			     $id_pr_arr = array_reverse($id_pr_arr_e);
			     $id_pr= intval($id_pr_arr[0]);
				  
				  $produkty_seo = new Produkty($this->p,$this->parametry,$this->get_parametry,__TYP_CENY__);
				  $produkty_seo_arr = $produkty_seo->infoProduktDetail($id_pr);

				$seoreturn .= "fbq('track', 'ViewContent', {
				  content_ids: ['".$produkty_seo_arr['id']."'],
				  content_type: 'product',
				  content_name: '".$produkty_seo_arr['nazev']."',
				  value: '".$produkty_seo_arr['cena']."',
				  currency: 'CZK'
				});";
			  }
			  
			  // vložení do košíku
			  if($this->p=='produkty' && $_POST['produkt'] && $_POST['varianta'] && $_POST['pocet'])
			  {
				$produkty_seo = new Produkty($this->p,$this->parametry,$this->get_parametry,__TYP_CENY__);
				$produkty_seo_arr = $produkty_seo->infoProduktDetail($_POST['produkt']);
				  
				  $seoreturn .= "fbq('track', 'AddToCart', {
				  content_ids: ['".intval($_POST['produkt'])."_".intval($_POST['varianta'])."'],
				  content_type: 'product',
				  content_name: '".$produkty_seo_arr['nazev']."',
				  value: '".$produkty_seo_arr['cena']."',
				  currency: 'CZK'
				});";
			  }
			  
			  // dokončení nákupu - krok 4
			  if($this->p=='kosik' && $_GET['krok']==4)
			  {
				
				$kosik_seo = new Kosik($this->p,$this->parametry,$this->get_parametry,__TYP_CENY__,__SLEVOVA_SKUPINA__);
				$cena_celkem_fbp = $kosik_seo->kosikCenaCelkem();
				
				$seoreturn .= "fbq('track', 'Purchase', {
				'value':'".$cena_celkem_fbp."',
				'currency':'CZK',
				'contents':[";
				$seoreturn .= $kosik_seo->infoKosikFB();
				$seoreturn .= "],
				'content_type':'product'});";
			  }
			  
			  
			$seoreturn .= "</script>
			<noscript><img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=".__FB_PIXEL__."&ev=PageView&noscript=1' /></noscript>
			<!-- End Meta Pixel Code -->";
		}
		

		return $seoreturn;
		
	}
	
	
	


}
?> 
