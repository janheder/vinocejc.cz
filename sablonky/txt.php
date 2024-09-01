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
    <link href="https://janheder.github.io/vinocejc.cz/dist/css/style.min.css" rel="stylesheet">
    <link href="/css/v.css?ver=<?php echo __JS_VERZE__;?>" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.1/css/lightbox.min.css">
<style>
  .lightbox{
    opacity: 1; 
    visibility: visible;
    transition: all 0s ease;
  }
  .fotogalerie-wrap{
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  gap: 10px;
  margin: 60px 0 0 0;
}
.fotogalerie-wrap img{
  aspect-ratio: 4/3;
}
@media screen and (max-width: 575px){
  .fotogalerie-wrap{
  grid-template-columns: 1fr 1fr;
}
}
</style>
    <script>
    <?php  
    // zamezíme opakovanému odeslání formuláře
    if($_POST['r_u'])
    {
	  echo 'if ( window.history.replaceState ) {
			window.history.replaceState( null, null, window.location.href );
				}
			';
	}
	?>
    </script>
  </head>
  <body class="txt-page">
	<?php
      include('./skripty/_prihlaseni.php');
     ?>  
    
    <header>
		 {infolista}
      <div class="header-top">
        <div class="container"> 
		  
          <div class="header-top__tel"> <small>Nákup po telefonu</small><img src="/ikony/phone-white.svg" alt="+420 605 337"><span><?php echo __TELEFON__;?> </span><small>(<?php echo __OTV_DOBA__;?>)</small></div>
          <div class="header-top__links">
			  {top_menu}
			 </div>
        </div>
      </div>
      <div class="header-main"> 
        <div class="container"> 
          <div id="navToggle"><span></span></div><a class="logo" href="/"><img src="/img/logo.svg" alt="<?php echo __TITLE_LOGA__;?>" title="<?php echo __TITLE_LOGA__;?>" width="150" height="40"></a>
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
	<nav>
      <div class="container">
		  {menu_kategorii}
		  

        <div class="nav-item-backdrop"></div>
      </div>
    </nav>
    
    <main>
      <div class="breadcrumbs">
        <div class="container">
			{drobinka}
			</div>
      </div>
      
      <div class="page">
        <section class="page-heading">
          <div class="container">
            <h1>{nadpis}</h1>
          </div>
        </section>
        <section class="page-content">
          <div class="container"> 
           
			  
            {obsah}
            
            
             </div>
        </section>
      </div>
          
          
    {recenze_pata}      

    {info_okno}
    
     <?php
      include('./skripty/_pata.php');
     ?>

    
    <!-- Custom js plugins and scripts-->
    <script src="/js/micromodal.min.js"></script>
   
    <script src="/js/lazysizes.min.js"></script>
    <script src="/js/instant.page.min.js"></script>
    <script src="/js/siema.min.js"></script>
    <script src="/js/scripts.js?ver=<?php echo __JS_VERZE__;?>"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <!-- PWA init-->
    <!--<script src="/upup.min.js"></script>-->
    <script>
      /*
      UpUp.start({
      'content-url': 'offline.html',
      'assets': ['/img/logo.svg']
      });*/
      
      document.getElementById('searchbox').addEventListener('keyup', function (e) 
      {
          if(this.value.length >=1)
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
    </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js" integrity="sha512-k2GFCTbp9rQU412BStrcD/rlwv1PYec9SNrkbQlo6RZCf75l6KcC3UwDY8H5n5hl4v77IDtIPwOk9Dqjs/mMBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  </body>
</html>
