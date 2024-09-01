<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5" name="viewport">
    <link rel="stylesheet" href="/css/cookieconsent.css">
    <script type="text/javascript" src="/js/cookieconsent.js" defer></script>
    <script type="text/javascript" src="/js/cookieconsent-init.js" defer></script>
     {seometa}



    <meta content="Eline.cz" name="author">
    <link rel="preload" href="/fonts/CerebriSans-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/CerebriSans-Medium.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="apple-touch-icon" sizes="57x57" href="/img/favicons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/favicons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/favicons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/favicons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicons/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#24272D">
    <meta name="msapplication-TileImage" content="/img/favicons/ms-icon-144x144.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff">
    <!-- Plugins CSS-->
    <!-- Custom CSS-->
    <link href="/css/style.min.css?ver=<?php echo __JS_VERZE__;?>" rel="stylesheet">

    <script>
      <?php  
    // zamezíme opakovanému odeslání formuláře
    if($_POST['r_u'])
    {
	  echo 'if ( window.history.replaceState ) {
			window.history.replaceState( null, null, window.location.href );
				}';
	}
	?>
    </script>
    <script src="https://widget.packeta.com/www/js/library.js"></script>
    <link rel="stylesheet" href="https://www.ppl.cz/sources/map/main.css">
  </head>
  <body class="cart-page">
	<?php
	  echo __GOOGLE_GTM_NOSCRIPT__;
      include('./skripty/_prihlaseni.php');
     ?>  
    
    <header>
		 {infolista}
      <div class="header-top">
        <div class="container"> 
		  
          <div class="header-top__tel"> <small>Nákup po telefonu</small><img src="/ikony/phone-white.svg" alt="tel"><span><?php echo __TELEFON__;?> </span><small>(<?php echo __OTV_DOBA__;?>)</small></div>
          <div class="header-top__links">
			  {top_menu}
			 </div>
        </div>
      </div>
      <div class="header-main"> 
        <div class="container"> 
          <a class="logo" href="/"><img src="/img/logo.svg" alt="<?php echo __TITLE_LOGA__;?>" title="<?php echo __TITLE_LOGA__;?>" width="150" height="40"></a>
          <div class="search"> 
            <div class="search-wrapper">    
              <form class="search-form" method="get" action="/vyhledavani"> 
                <input class="search__input" type="text" id="searchbox"  name="searchbox" autocomplete="off" placeholder="Hledejte název produktu, značku nebo typ">
                <button class="search__button"><img src="/ikony/search.svg" alt="Vyhledávání" width="16" height="16"></button>
                <div class="searchAutocomplete" id="searchAutocomplete">
                </div>
              </form>
            </div>
          </div>
          <div id="searchToggle"><img src="/ikony/search-big.svg" alt="Vyhledávání"></div>
          <div class="header-nav">
			  
			  
			  <?php 
				if($_SESSION['uzivatel'])
				{
					
				
					echo '<div class="user --active" id="userToggle">
	              <div class="user__icon"><img src="/ikony/user.svg" alt="Přihlášení"></div>
	              <div class="user__wrap">
	                <div class="user__text">Přihlášen jako</div>
	                <div class="user__title">'.strip_tags($_SESSION['uzivatel']['jmeno']).' '.strip_tags($_SESSION['uzivatel']['prijmeni']).'</div>
	              </div>
	              <div class="user__dropdown" id="userDropdown">';
				   if($_SESSION['uzivatel']['typ']==1)
				   {
					  echo '<a href="/uzivatelsky-ucet">Uživatelský účet</a>';
				   }	
	
	               echo '<a href="/odhlaseni" data-no-instant >Odhlásit</a></div></div>';
            
            
 
				}
				else
				{
				  echo '<div class="user" data-micromodal-trigger="modal-login">
				<div class="user__icon"><img src="/ikony/user.svg" alt="Přihlášení"></div>
				<div class="user__title">Přihlášení</div>
				</div>';	
				}
				?>
            
             {kosik}
                    

             
          </div>
        </div>
      </div>
    </header>
    <div id="darkBackdrop"></div>

    <main>

       {obsah}
       

       {info_okno}
    
     <?php
      include('./skripty/_pata.php');
     
     
    // jen pro krok 2 
 
   if($_GET['krok']==2)
   {
	echo '<div class="modal micromodal-slide" id="modal-delivery">
      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-labelledby="modal-delivery-title">
          <div class="modal__content" id="modal-delivery-content">
            <div class="modal__header">
              <h2 class="modal__title" id="modal-delivery-title">Balík na poštu</h2>
              <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </div>
            <iframe src="" width="100%" height="500" id="ifr_posta"></iframe>
          </div>
        </div>
      </div>
    </div>';

    echo '<div class="modal micromodal-slide" id="modal-delivery2">
      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-labelledby="modal-delivery-title">
          <div class="modal__content" id="modal-delivery-content">
            <div class="modal__header">
              <h2 class="modal__title" id="modal-delivery-title">Balík do Balíkovny</h2>
              <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </div>
            <iframe src="" width="100%" height="500" id="ifr_posta2"></iframe>
          </div>
        </div>
      </div>
    </div>';
    
    
    echo '<div class="modal micromodal-slide" id="modal-ppl">
      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-labelledby="modal-delivery-title">
          <div class="modal__content" id="modal-delivery-content">
            <div class="modal__header">
              <h2 class="modal__title" id="modal-delivery-title">PPL Parcel</h2>
              <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </div>
            <div id="ppl-parcelshop-map"  ></div>
          </div>
        </div>
      </div>
    </div>';
    
    
    echo '<div class="modal micromodal-slide" id="modal-dpd">
      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-labelledby="modal-delivery-title">
          <div class="modal__content" id="modal-delivery-content">
            <div class="modal__header">
              <h2 class="modal__title" id="modal-delivery-title">DPD Pickup</h2>
              <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </div>
              <iframe src="https://api.dpd.cz/widget/latest/index.html?crossboarder=0&dpd_box=0" id="ifr_dpd" title="Výběr místa pro vyzvednutí zásilky" width="100%" height="100%" ></iframe>
          </div>
        </div>
      </div>
    </div>
    <style>

/*
        #modal-dpd
        {
        max-width: 100vw;
        }
        
        #modal-dpd .modal__content
        {
	    max-width: 100vw;
	    max-height: 100vh;
		}
		
		
		#ifr_dpd
		{
	    width: 100%;
	    height: 80vh;
	    border: solid 1px #dedede;
	    }
*/


    </style>';
    
    }
    
    ?>
    
    
    <!-- Custom js plugins and scripts-->
    <script src="/js/micromodal.min.js"></script>
   
    <script src="/js/lazysizes.min.js"></script>
    <script src="/js/instant.page.min.js"></script>
    <script src="/js/siema.min.js"></script>
    <script src="/js/scripts.js?ver=<?php echo __JS_VERZE__;?>"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <!-- PWA init-->
    <script>
	
	<?php
	// v druhém kroku košíku musíme volat funkci na přepočet dopravy - pro případ, že se vrátí z prvního kroku, kde poníží počty kusů tak, že už nemá dopravu zdarma
	if($_GET['krok']==2)
	{
	  //echo '$(window).on(\'load\', function(){PrepocetKrok2();});';
	  
	  echo 'PrepocetKrok2();';
	}
	?>	
	
	function PrepocetKrok2()
	{
		if($('input[name=doprava]').is(':checked'))
		{
			var platba_cena = 0;
 
			radioButtonText = $('input[name=doprava]:checked');
			var doprava_cena = radioButtonText.closest('label').find('span').html();
			 
			if($('input[name=platba]').is(':checked'))
		    {
				radioButtonText2 = $('input[name=platba]:checked');
				var platba_cena = radioButtonText2.closest('label').find('span').html();
			}

			var cena_d_p = (parseInt(doprava_cena) + parseInt(platba_cena));
			var cena_zbozi = $('#goodsprice').html();
			var cena_celkem = (parseInt(cena_zbozi) + parseInt(cena_d_p));
			$('#delprice').html(cena_d_p);
			$('#sumprice').html(cena_celkem);
			
			
		}

	}
    
	
	 $(document).ready(function(){
		 
		$('#email').keyup(function(){
		var email = $('#email').val().trim();
 
		if(!email)
		{
            
	    }
	    else
	    {
		    if($('#cart-register').is(':checked'))
			{

	         $.ajax({
	            url: '/skripty/_ajax.php?typ=kontrola_email',
	            type: 'post',
	            data: {uname:email},
	            success: function(response)
	            {
	
	                if(response > 0)
	                {
	                   alert("Vámi zadaná email je již používán některým z registrovaných zákazníků.\nZvolte jiný email nebo si nechejte vygenerovat nové heslo.");
					   $('#tl_krok_3').prop('disabled', true );
	                    
	                }
	                else
	                {
	                   $('#tl_krok_3').prop('disabled', false );
	                }
	
	             }
	          });
	          
		    }
		    else
		    {
			   $('#tl_krok_3').prop('disabled', false );
			}
		  }
	      
	       
		
		});
	
		
	});
	
	
	 
	 
  $(document).ready(function(){
    $('#cart-register').on('click', function ()
    {
       
       if($('#cart-register').is(':checked'))
       {
		  // kontrolujeme email
		  var email = $('#email').val().trim();
		  if(!email)
		  {

		     $('#tl_krok_3').prop('disabled', true );
		  }
		  else
		  {
		      $.ajax({
		            url: '/skripty/_ajax.php?typ=kontrola_email',
		            type: 'post',
		            data: {uname:email},
		            success: function(response)
		            {
						if(response>0)
						{
						   alert("Vámi zadaná email je již používán některým z registrovaných zákazníků.\nZvolte jiný email nebo si nechejte vygenerovat nové heslo.");
						   $('#tl_krok_3').prop('disabled', true );

						}
						else
						{
						  $('#tl_krok_3').prop('disabled', false );
						}
						
		            }
		            
		          });
		  }
		   
	   }
	   else
	   {
	      $('#tl_krok_3').prop('disabled', false ); 
	   }


    });
  
   });
	 
	 
      function ZmenPocetKos(id_inp,id_p,id_v)
      {
	     if(id_inp && id_p && id_v)
	     {
		   var pocet = $('#qty-stepper-input-'+id_inp).val();
			
			$.ajax({
		            url: '/skripty/_ajax.php?typ=kosik_zmena_poctu',
		            type: 'post',
		            data: {idp:id_p,idv:id_v,pocet:pocet},
		            success: function(response)
		            {
						if(response)
						{
						   //alert('počet změněn');

						}
						
		            }
		            
		          });
		 
		 }
		 
	  }
	  
	  
	  function Zasilkovna(id)
	  {
	    var ZasAPI = '<?php echo __ZASILKOVNA_API_KEY__;?>';
	    $('#_p').val(3);
	    Packeta.Widget.pick(ZasAPI, showSelectedPickupPoint);
	  }
	  
	  
	  function showSelectedPickupPoint(point)
	  {
			// nastavíme získané údaje
			
			if(point) {
				
				var jsonString = JSON.stringify(point);
				var idd = $('input[name="doprava"]:checked').val();
				$('#s_pobocka3').html('Vybraná pobočka: '+point.name);
				if (document.getElementById('s_pobocka')){$('#s_pobocka').hide();}
				if (document.getElementById('s_pobocka2')){$('#s_pobocka2').hide();}
				if (document.getElementById('s_pobocka4')){$('#s_pobocka4').hide();}
				if (document.getElementById('s_pobocka5')){$('#s_pobocka5').hide();}
				$('#s_pobocka3').show();
						
				$.ajax({
		            url: '/skripty/_ajax.php?typ=nastav_zasilkovnu',
		            type: 'post',
		            data: {points:jsonString,idd:idd},
		            success: function(response)
		            {
						if(response==1)
						{
						    //alert(response);
						    //$('#s_pobocka3').html('Vybraná pobočka: '+point.name);

						}
						else
						{
							alert('Nepodařilo se nastavit údaje Zásilkovny');
						}	
		            }
		            
		          });

			}

	  }
	  
	  
	  function PPLparcel(id)
	  {
	    
	      //MicroModal.close('modal-delivery2'); 
	      $('#_p').val(4);
          MicroModal.show('modal-ppl',{
              openClass: 'is-open', 
              disableScroll: true, 
              disableFocus: true, 
              awaitOpenAnimation: true,
              awaitCloseAnimation: true, 
              debugMode: false 
			}); 
			
			
			// po zobrazení modalu natáhneme js s mapou aby se správně vygenerovala
		   $.ajax({
			  type: "GET",
			  url: "https://www.ppl.cz/sources/map/main.js",
			  dataType: "script"
			});
	
	  }
	  
	
	// PPL parcel
	document.addEventListener(
	'ppl-parcelshop-map',
	(event) => {
	// vygenerujeme input prvky
	//alert(JSON.stringify(event.detail, null, 4));
	
	var ppl_parcel_id = JSON.stringify(event.detail.id);
	var ppl_parcel_code = JSON.stringify(event.detail.code);
	var ppl_parcel_shopname = JSON.stringify(event.detail.parcelshopName);
	var ppl_parcel_name = JSON.stringify(event.detail.name);
	var ppl_parcel_street = JSON.stringify(event.detail.street);
	var ppl_parcel_city = JSON.stringify(event.detail.city);
	//var ppl_parcel_zip = JSON.stringify(event.detail.zipCode);
	//var ppl_parcel_country = JSON.stringify(event.detail.country);
	
	
	if(ppl_parcel_id) 
	{
		
		var idd = $('input[name="doprava"]:checked').val();
		$('#s_pobocka4').html('Vybraná pobočka: '+ppl_parcel_name.replace(/['"]+/g, '')+', '+ppl_parcel_street.replace(/['"]+/g, '')+', '+ppl_parcel_city.replace(/['"]+/g, ''));
		if (document.getElementById('s_pobocka')){$('#s_pobocka').hide();}
		if (document.getElementById('s_pobocka2')){$('#s_pobocka2').hide();}
		if (document.getElementById('s_pobocka3')){$('#s_pobocka3').hide();}
		if (document.getElementById('s_pobocka5')){$('#s_pobocka5').hide();}
		$('#s_pobocka4').show();
		
		var jsonString = JSON.stringify(event.detail);
				
		$.ajax({
            url: '/skripty/_ajax.php?typ=nastav_ppl_parcel',
            type: 'post',
            data: {points:jsonString,idd:idd},
            success: function(response)
            {
				if(response==1)
				{
				    //alert(response);
				    //$('#s_pobocka4').html('Vybraná pobočka: '+ppl_parcel_name);

				}
				else
				{
					alert('Nepodařilo se nastavit údaje PPL Parcel');
					//alert(response);
				}	
            }
            
          });

    }


	MicroModal.close('modal-ppl'); 
	
	}
	);
	
	
	  function DPDpickup(id)
	  {
	    
	      //MicroModal.close('modal-delivery2'); 
	      $('#_p').val(5);
          MicroModal.show('modal-dpd',{
              openClass: 'is-open', 
              disableScroll: true, 
              disableFocus: true, 
              awaitOpenAnimation: true,
              awaitCloseAnimation: true, 
              debugMode: false 
			}); 
			
	
	  }
	  
	  // DPD Pickup
	  window.addEventListener("message", (event) => 
      {
	    if(event.data.dpdWidget) {
	      
	    //alert(JSON.stringify(event.data.dpdWidget, null, 4));

		var dpd_pickup_id = JSON.stringify(event.data.dpdWidget.id);
		var dpd_pickup_name = JSON.stringify(event.data.dpdWidget.contactInfo.name);
		var dpd_pickup_street = JSON.stringify(event.data.dpdWidget.location.address.street);
		var dpd_pickup_city = JSON.stringify(event.data.dpdWidget.location.address.city);
		//var dpd_pickup_zip = JSON.stringify(event.data.dpdWidget.location.address.zip);
		//var dpd_pickup_country = JSON.stringify(event.data.dpdWidget.location.address.country);
		
		if(dpd_pickup_id) 
		{
			
			var idd = $('input[name="doprava"]:checked').val();
			$('#s_pobocka5').html('Vybraná pobočka: '+dpd_pickup_name.replace(/['"]+/g, '')+', '+dpd_pickup_street.replace(/['"]+/g, '')+', '+dpd_pickup_city.replace(/['"]+/g, ''));
			if (document.getElementById('s_pobocka')){$('#s_pobocka').hide();}
			if (document.getElementById('s_pobocka2')){$('#s_pobocka2').hide();}
			if (document.getElementById('s_pobocka3')){$('#s_pobocka3').hide();}
			if (document.getElementById('s_pobocka4')){$('#s_pobocka4').hide();}
			$('#s_pobocka5').show();
			
			var jsonString = JSON.stringify(event.data.dpdWidget);
					
			$.ajax({
	            url: '/skripty/_ajax.php?typ=nastav_dpd_pickup',
	            type: 'post',
	            data: {points:jsonString,idd:idd},
	            success: function(response)
	            {
					if(response==1)
					{
					    //alert(response);
					    //$('#s_pobocka5').html('Vybraná pobočka: '+dpd_pickup_name);
	
					}
					else
					{
						alert('Nepodařilo se nastavit údaje DPD Pickup');
						//alert(response);
					}	
	            }
	            
	          });
	
	    }
	
	
		MicroModal.close('modal-dpd'); 
		

	
	     }
       }, false);
	  
	  
	  
	  function BalikNaPostu(id)
	  {

		$('#ifr_posta').attr('src', '/skripty/_posta.php?idd='+id); 
	    $('#_p').val(1);
	    
	    if (document.getElementById('s_pobocka')){$('#s_pobocka').html('');}
	    if (document.getElementById('s_pobocka2')){$('#s_pobocka2').hide();}
	    if (document.getElementById('s_pobocka3')){$('#s_pobocka3').hide();}
	    if (document.getElementById('s_pobocka4')){$('#s_pobocka4').hide();}
	    if (document.getElementById('s_pobocka5')){$('#s_pobocka5').hide();}
	    $('#s_pobocka').show();
	    
	      //MicroModal.close('modal-delivery2'); 
          MicroModal.show('modal-delivery',{
              openClass: 'is-open', 
              disableScroll: true, 
              disableFocus: true, 
              awaitOpenAnimation: true,
              awaitCloseAnimation: true, 
              debugMode: false 
			}); 

      
	  }
	  
	  
	  function BalikDoBalikovny(id)
	  {
			
		  $('#ifr_posta2').attr('src', '/skripty/_posta2.php?idd='+id); 
		  $('#_p').val(2);
		  if (document.getElementById('s_pobocka')){$('#s_pobocka').html('');}
		  if (document.getElementById('s_pobocka')){$('#s_pobocka').hide();}
		  if (document.getElementById('s_pobocka3')){$('#s_pobocka3').hide();}
		  if (document.getElementById('s_pobocka4')){$('#s_pobocka4').hide();}
		  if (document.getElementById('s_pobocka5')){$('#s_pobocka5').hide();}
		  $('#s_pobocka2').show();
		  
		   //MicroModal.close('modal-delivery'); 
           MicroModal.show('modal-delivery2',{
              openClass: 'is-open', 
              disableScroll: true, 
              disableFocus: true, 
              awaitOpenAnimation: true,
              awaitCloseAnimation: true, 
              debugMode: false 
			});
	
	  }
	  
	  
	  function SkryjBaliky(id)
	  {
          //MicroModal.close('modal-delivery'); 
          //MicroModal.close('modal-delivery2'); 
          if (document.getElementById('s_pobocka')){$('#s_pobocka').hide();}
          if (document.getElementById('s_pobocka2')){$('#s_pobocka2').hide();}
          $('#_p').val(0);
          
          $.ajax({
		            url: '/skripty/_ajax.php?typ=smazat_postu',
		            type: 'post',
		            data: {p:id},
		            success: function(response)
		            {
						if(response==1)
						{
						    //alert('Smazáno');

						}
						else
						{
							alert('Nepodařilo se smazat výběr pobočky');
						}	
		            }
		            
		          });
          
		
	  }
	  
	  
	  function SkryjZasilkovnu(id)
	  { 
          if (document.getElementById('s_pobocka3')){$('#s_pobocka3').hide();}
          $('#_p').val(0);
          
          $.ajax({
		            url: '/skripty/_ajax.php?typ=smazat_zasilkovnu',
		            type: 'post',
		            data: {p:id},
		            success: function(response)
		            {
						if(response==1)
						{
						    //alert('Smazáno');

						}
						else
						{
							alert('Nepodařilo se smazat výběr Zásilkovny');
						}	
		            }
		            
		          }); 
		
	  }
	  
	  
	  function SkryjPPLparcel(id)
	  {
          if (document.getElementById('s_pobocka4')){$('#s_pobocka4').hide();}
          $('#_p').val(0);
          
          $.ajax({
		            url: '/skripty/_ajax.php?typ=smazat_ppl_parcel',
		            type: 'post',
		            data: {p:id},
		            success: function(response)
		            {
						if(response==1)
						{
						    //alert('Smazáno');

						}
						else
						{
							alert('Nepodařilo se smazat výběr PPL Parcel');
						}	
		            }
		            
		          }); 
		
	  }
	  
	  
	  function SkryjDPDpickup(id)
	  {
          if (document.getElementById('s_pobocka5')){$('#s_pobocka5').hide();}
          $('#_p').val(0);
          
          $.ajax({
		            url: '/skripty/_ajax.php?typ=smazat_dpd_pickup',
		            type: 'post',
		            data: {p:id},
		            success: function(response)
		            {
						if(response==1)
						{
						    //alert('Smazáno');

						}
						else
						{
							alert('Nepodařilo se smazat výběr DPD Pickup');
						}	
		            }
		            
		          }); 
		
	  }
	  


	function KontrolaPosta()
    {
       
       var _p = $('#_p').val();
       
       if ($('input[name="doprava"]:checked').length == 0) 
       {
	     alert('Nevybrali jste způsob dopravy');
	   }
	   else if($('input[name="platba"]:checked').length == 0) 
       {
	     alert('Nevybrali jste způsob platby');
	   }
	   else if(_p>0)
       {
		  // balík na poštu + balíkovna + zásilkovna + ppl_parcel
		  $.ajax({
		            url: '/skripty/_ajax.php?typ=kontrola_baliky',
		            type: 'post',
		            async: false,
		            data: {_p:_p},
		            success: function(response)
		            {
						if(response)
						{
						    alert(response);

						}
						else
						{
							$('form#cart-form').submit();
						}	
		            }
		            
		          });
		  
	   }
	   else
	   {
	     $('form#cart-form').submit();
	   }
	   
      
   }
 
	



	  
 $(document).ready(function(){
    $('#tl_sk').on('click', function ()
    {
       
       var kod = $('#code').val();
       
       if(kod)
       {
	   
			$.ajax({
		            url: '/skripty/_ajax.php?typ=slevovy_kod',
		            type: 'post',
		            data: {kod:kod},
		            success: function(response)
		            {
						if(response==1)
						{
						    alert('Kód byl uplatněn');
						    UplatniKod();
						    recountCart();

						}
						else
						{
							alert('kód není validní');
						}	
		            }
		            
		          });
	   
	   }
	   else
	   {
	      alert('Zadejte slevový kód');
	   }
 
	
    });
});
	   
	  function UplatniKod()
	  {
	      var kod = $('#code').val();
	      
	      $.ajax({
		            url: '/skripty/_ajax.php?typ=uplatni_slevovy_kod',
		            type: 'post',
		            data: {kod:kod},
		            success: function(response)
		            {
						if(response)
						{
							$('#code').prop('disabled', true );
							$('#tl_sk').prop('disabled', true );
							$('.cart-aside__code').prop('disabled', true );
							$('.cart-table-items:last').after(response);
							
						}

		            }
		            
		          });
	  }
	  
	  
	  
	  function VygenerujPlatbu(idd)
	  {

	      if(idd>0)
	      {
		      $.ajax({
			            url: '/skripty/_ajax.php?typ=vygeneruj_platbu',
			            type: 'post',
			            data: {idd:idd},
			            success: function(response)
			            {
							if(response)
							{
								$('#platby').html(response);
								recountDeliveryCart();
								
							}
	
			            }
			            
			          });
		  }
	  }
	  
	  
      document.getElementById('searchbox').addEventListener('keyup', function (e) 
      {
          if(this.value.length >=3)
          {
              document.getElementById('searchAutocomplete').classList.add('--active');
              var kw = $('#searchbox').val();
		      	// generujeme obsah ajaxem
		      	$.ajax({
				            url: '/skripty/_ajax.php?typ=naseptavac',
				            type: 'post',
				            data: {kw:kw},
				            success: function(response)
				            {
								if(response)
								{
								   $('#searchAutocomplete').html(response);
		
								}
								else
								{
									alert('chyba naseptavac');
								}	
				            }
				            
				          });
          }
          else
          {
              document.getElementById('searchAutocomplete').classList.remove('--active');
          }
      
      });
    
    
if($('#pk').length > 0)
{
   $('#pk').delay(8000).submit();
}		
	 
	 
     
      
    </script>
    <script src="/js/cart.js"></script>
  </body>
</html>
