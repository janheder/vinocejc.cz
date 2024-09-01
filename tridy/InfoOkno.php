<?
// třida modální infookno pokud je povoleno

class InfoOkno
{
private $p; // aktualni stranka


	function __construct($p)
	{
	  
	  $this->p = sanitize($p);
	  $this->nazev = 'nazev';
	  $this->obsah = 'obsah';

	}
	
	public function generujOkno()
	{
		
		$data_o = Db::queryRow('SELECT * FROM reklamni_okno WHERE id=1 AND (datum_od <='.time().' AND datum_do >='.time().' ) ', array());
		$ret = '';
		if($data_o['aktivni']==1)
		{
		   // okno je povoleno
		   if($_COOKIE['infookno'])
		   {
		     // okno se již zobrazilo, znovu nezobrazujeme 
		   }
		   else
		   {
		     $ret .= '
			 
			
			<div class="modal micromodal-slide" id="modal-info" aria-hidden="true">
			<div class="modal__overlay" tabindex="-1" data-micromodal-close="">
			  <div class="modal__container" role="dialog" aria-labelledby="modal-info-title">

			  <div class="modal__header-img">';

			  	// fotka
			  if($data_o['foto'])
			  {
				$ret .= '<img src="/fotky/infookno/'.$data_o['foto'].'" alt="'.$data_o[$this->nazev].'">';
			  }

			 $ret .= '</div>
			  
				<div class="modal__content" id="modal-info-content">
				  <div class="modal__header">
					<h2 class="modal__title" id="modal-info-title">'.$data_o[$this->nazev].'</h2>
					<button class="modal__close" aria-label="Close modal" data-micromodal-close=""></button>
				  </div>
				  <p>'.$data_o[$this->obsah].'</p>
				</div>
			  </div>
			</div>
		  </div>

			';
			$ret .= '<script type="text/javascript">document.addEventListener("DOMContentLoaded", () => {
				MicroModal.show("modal-info",{
					openClass: "is-open", 
					disableScroll: true, 
					disableFocus: true, 
					awaitOpenAnimation: true,
					awaitCloseAnimation: true, 
					debugMode: false 
				}); 
			});</script>';

		    // pokud došlo k zobrazení tak uložíme cookies aby se okno nezobrazovalo při každém reloadu
		    $ret .= '<script type="text/javascript">';
			$ret .= 'document.cookie="infookno='.time().'; path=/; max-age=86400"';
			$ret .= '</script>';
			
		   }
		  
		}

		
		return $ret;
		
	}
	
}
