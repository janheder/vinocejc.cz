<?php
// trida Aktuality
// do tridy predavame 2 typy parametru v polich

class Aktuality
{

public $parametry; // parametry za lomitky
public $get_parametry;  // klasicke GET parametry
public $skript; // nazev stranky
	
	
	function __construct($skript,$parametry,$get_parametry)
	{
	 $this->skript = $skript;
	 $this->parametry = $parametry;
	 $this->get_parametry = $get_parametry;
	 
	     if($_GET['page'])
		 {
		     $this->limit = ((intval($_GET['page']) * __POCET_NAHLEDU_PRODUKTY__) - __POCET_NAHLEDU_PRODUKTY__); // pro stránkovací rutinu
	     }
	     else
	     {
			 $this->limit = 0;
		 }

	}
	
	
	
	public function aktualityUvod($x)
	{ 
		$akt = '';
		
		$data_a = Db::queryAll('SELECT id, str, nadpis, foto, datum FROM aktuality WHERE aktivni=? AND na_uvod=? ORDER BY id DESC LIMIT 2 ', array(1,1));
		   if($data_a)
		   {
				   
				   foreach($data_a as $row_a)
				   {
					   
					   if($row_a['foto'])
					   {
						  $foto = $row_a['foto'];
					   }
					   else
					   {
						 $foto = 'news-thumb-medium.jpg';
					   }
					   
				        $akt .= '<div class="col-12 col-sm-12 col-md-6"><a class="news-single" href="/aktuality/'.$row_a['str'].'-'.$row_a['id'].'">
                <div class="news-single__top">
                  <div class="news-single__dateWrap"><span class="news-single__date">'.date('d.m.Y',$row_a['datum']).'</span></div><img class="lazyload news-single__img" src="/img/load-symbol.svg" data-src="/fotky/aktuality/male/'.$foto.'" alt="'.$row_a['nadpis'].'" title="'.$row_a['nadpis'].'">
                </div>
                <div class="news-single__bottom">
                  <p class="news-single__title">'.$row_a['nadpis'].'</p>
                </div></a></div>';
				   }
		    }
		
		return $akt;
		
	}
	
	
	public function aktualityVypis()
	{ 
		$akt = '';
		
		$data_a = Db::queryAll('SELECT id, str, nadpis, foto, datum FROM aktuality WHERE aktivni=? ORDER BY id DESC LIMIT '.$this->limit.','.__POCET_NAHLEDU_PRODUKTY__.' ', array(1));
		$pocet = Db::queryAffected('SELECT id FROM aktuality WHERE aktivni=? ORDER BY id DESC', array(1));
		
		if($data_a)
		{
				   
				   foreach($data_a as $row_a)
				   {
					   
					   if($row_a['foto'])
					   {
						  $foto = $row_a['foto'];
					   }
					   else
					   {
						 $foto = 'news-thumb-large.jpg';
					   }
					   
				        $akt .= '<a class="news-single --full" href="/aktuality/'.$row_a['str'].'-'.$row_a['id'].'">
                  <div class="news-single__top">
                    <div class="news-single__dateWrap"><span class="news-single__date">'.date('d.m.Y',$row_a['datum']).'</span></div><img class="lazyload news-single__img" src="/img/load-symbol.svg" data-src="/fotky/aktuality/velke/'.$foto.'" alt="'.$row_a['nadpis'].'" alt="'.$row_a['nadpis'].'" title="'.$row_a['nadpis'].'">
                  </div>
                  <div class="news-single__bottom">
                    <p class="news-single__title">'.$row_a['nadpis'].'</p>
                    <p class="news-single__text">'.$row_a['perex'].'</p>
                  </div></a>';
 
				   }
				   
				   
				   $akt .= $this->strankovani($pocet);
		    }
		
		return $akt;
		
	}
	
	

	public function strankovani($pocet)
	{
		$ret = '';

		if(__POCET_NAHLEDU_PRODUKTY__ < $pocet)
		{
			// pokud je méně výsledků než je výpis na stránku tak stránkování nezobrazujeme

			$url = $_SERVER['REDIRECT_SCRIPT_URI'];

			if($_GET)
			{
				$get_parametry = '?';

				foreach($_GET as $get_key=>$get_val)
				{
					if($get_key!='page')
					{
					   // pro případ, kdy máme v GETu pole
					   if(is_array($get_val))
					   {
						    foreach($get_val as $get_key2=>$get_val2)
							{
							    $get_parametry .= $get_key.'[]='.strip_tags($get_val2).'&';
							}
					   }
					   else
					   {
						   $get_parametry .= $get_key.'='.strip_tags($get_val).'&';
					   }


					}

				}
			}
			else
			{
				$get_parametry = '?';
			}




			$ps = ceil($pocet / __POCET_NAHLEDU_PRODUKTY__);
			if(!$_GET['page'])
			{
			$ps2 = 1;
			}
			else
			{
			$ps2 = intval($_GET['page']);
			}

			$leva = intval(max(1,$ps2-5));
			$prava = intval(min($ps,$ps2+5));
			$leva_pocet = $ps2 - $leva;
			$prava_pocet = $prava - $ps2;

			if ( $leva_pocet + $prava_pocet != 5 )
			{
				if ( $leva_pocet < 5 )
					$prava = min($ps, $prava + ( 5 - $leva_pocet ));

				if ( $prava_pocet < 5 )
					$leva = max(1, $leva - ( 5 - $prava_pocet ));
			}

			$ret .= '<div class="pagination" aria-label="Stránkování produktů">
              <ul class="pagination__list">';

			if($leva>1)
			{
				$ret .= '<li class="pagination__item"><a class="pagination__link" href="'.$url.$get_parametry.'page=1">1</a></li>';

			}

			for ($px=$leva;$px<=$prava;$px++)
			{

				if($px==$ps2)
				{
					$ret .= '<li class="pagination__item --active"><a class="pagination__link" href="'.$url.$get_parametry.'page='.$px.'">'.$px.'</a></li>';


				}
				else
				{
					$ret .= '<li class="pagination__item"><a class="pagination__link" href="'.$url.$get_parametry.'page='.$px.'">'.$px.'</a></li>';
				}

			}

			if($prava<$ps)
			{
				$ret .= '<li class="pagination__item"><a class="pagination__link" href="'.$url.$get_parametry.'page='.$ps.'">'.$ps.'</a></li>';
			}

              $ret .= '</ul>
            </div>';
		}


		return $ret;
	}
	
	
	
	public function aktualityDetail()
	{ 
		$akt = '';
		if($this->parametry[1])
	    {
		     // získáme ID aktuality
		     $id_akt_arr_e = explode('-',$this->parametry[1]);
		     $id_akt_arr = array_reverse($id_akt_arr_e);
		     $id_akt = intval($id_akt_arr[0]);

			$data_a = Db::queryRow('SELECT * FROM aktuality WHERE aktivni=? AND id=?', array(1,$id_akt));
			if($data_a)
			{
				
				   if($data_a['foto'])
				   {
					  $foto = $data_a['foto'];
				   }
				   else
				   {
					 $foto = 'news-thumb-large.jpg';
				   }
					   
					   
			      $akt .= '<article> 
				          <div class="article-header">
				            <h1>'.$data_a['nadpis'].'</h1>
				            <div class="article-info">
				              <div class="article-info__date">Zveřejněno '.date('d.m.Y',$data_a['datum']).'</div><a class="article-info__social" href="https://www.facebook.com/sharer/sharer.php?u='.__URL__.$_SERVER['REQUEST_URI'].'" target="_blank"><img src="/img/icons/share.svg"> Sdílet</a>
				            </div>
				          </div>
				          <div class="article-thumb"><img src="/fotky/aktuality/velke/'.$foto.'" alt="'.$data_a['nadpis'].'" title="'.$data_a['nadpis'].'"></div>
				          <div class="article-content"> 
				            <p>'.$data_a['obsah'].'</p>
				          </div>
				        </article>';
        
			}
		
	    }
	    else
	    {
		   $akt = 'Chybí ID aktuality.';
		}
		
		return $akt;
	}
	


}
