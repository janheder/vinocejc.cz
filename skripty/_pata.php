<?php
// pata pro všechny stránky stejná

$skript_raw = explode('/',$_SERVER['REDIRECT_SCRIPT_URL']);
$skript = ltrim($skript_raw[1],"/");

	  
echo '<section class="contact"> 
        <div class="container"> 
          <div class="contact__content">
            <h1 class="contact__title">Máte dotazy nebo potřebujete poradit s výběrem?</h1>
            <div class="contact__wrap"> 
              <div class="contact__item"> <img src="/ikony/envelope.svg" alt="'.__FORM_EMAIL__.'" width="25" height="20">
                <div class="contact__text">'.__FORM_EMAIL__.'</div>
              </div>
              <div class="contact__item"> <img src="/ikony/phone-big.svg" alt="'.__TELEFON__.'" width="24" height="24">
                <div class="contact__text">'.__TELEFON__.' <small>PO - PÁ 8:00 – 14:30</small></div>
              </div>
            </div>
            <div class="contact__img"><img src="/img/contact-img.png" alt="" height="310" width="320"></div>
          </div>
        </div>
      </section>
      	
       <section class="social">
        <div class="container">
          <h1>Z našeho Instagramu</h1>
          <div class="instagram-wrap">
            <div class="row">';
            
            // fotky z fotogalerie
             $data_f = Db::queryAll('SELECT * FROM fotogalerie_fotky WHERE id_fotogalerie=1 ORDER BY razeni ASC ', array(1,7));
			 if($data_f)
			   {
				   
				   foreach($data_f as $row_f)
				   { 
					   echo '<div class="col-4 col-md-2"><a class="instagram-single" href="https://www.instagram.com/"><img class="lazyload" src="/img/load-symbol.svg" 
					   data-src="/fotky/galerie/male/'.$row_f['foto'].'" alt="'.$row_f['nazev'].'" ></a></div>';
				   }
			   }

 
              
            echo '</div>
          </div>
          <div class="row"> 
            <div class="col-12 col-md-4">
              <div class="social-single"><a class="social-single__link" href="https://facebook.com/">
                  <div class="social-single__img"><img class="lazyload" src="/img/load-symbol.svg" data-src="/ikony/facebook-big.svg" alt="Facebook" width="40" height="40"></div>
                  <div class="social-single__text">Jsme také na Facebooku</div></a></div>
            </div>
            <div class="col-12 col-md-4">
              <div class="social-single"><a class="social-single__link" href="https://youtube.com">
                  <div class="social-single__img"><img class="lazyload" src="/img/load-symbol.svg" data-src="/ikony/youtube-big.svg" alt="Youtube" width="40" height="40"></div>
                  <div class="social-single__text">Sledujte nás na Youtube</div></a></div>
            </div>
            <div class="col-12 col-md-4">
              <div class="social-single"><a class="social-single__link" href="https://www.instagram.com">
                  <div class="social-single__img"><img class="lazyload" src="/img/load-symbol.svg" data-src="/ikony/instagram-big.svg" alt="Instagram" width="40" height="40"></div>
                  <div class="social-single__text">Sledujte nás na Instagramu</div></a></div>
            </div>
          </div>
        </div>
      </section>
    </main>
    
    
    <footer>
      <div class="container">
        <div class="row">
          <div class="col-6 col-sm-6 col-lg-3">
            <div class="footer__title">O nákupu</div>
            <a class="footer__link" href="/obchodni-podminky">Obchodní podmínky</a>
            <a class="footer__link" href="/reklamacni-rad">Reklamační řád</a>
            <a class="footer__link" href="/doprava-a-platba">Doprava a platba</a>
            <a class="footer__link" href="/vyhody-nakupu">Výhody nákupu</a>
          </div>
          <div class="col-6 col-sm-6 col-lg-3">
            <div class="footer__title">O nás</div>
            <a class="footer__link" href="/o-nas">O společnosti</a>
            <a class="footer__link" href="/aktuality">Aktuality</a>
            <a class="footer__link" href="/kontakty">Kontakty</a>
          </div>
          <div class="col-6 col-sm-6 col-lg-3">
            <div class="footer__title">Další odkazy</div>
            <a class="footer__link" href="/ochrana-osobnich-udaju">Ochrana osobních údajů</a>
            <a class="footer__link" href="/cookies">Zásady používání cookie</a>
          </div>
          <div class="col-6 col-sm-6 col-lg-3">
            <div class="footer__title">Kontaktní informace</div>
            <p class="footer__text">Firma s.r.o.</p>
            <p class="footer__text">Osobní odběr zboží na adrese:</p>
            <p class="footer__text">Adresa 123</p>
            <p class="footer__text">123 45 Praha</p>
            <p class="footer__text"><a class="footer__link" href="/kontakty">Více kontaktů</a>
          </div>
        </div>
      </div>
      <section class="copyright">
        <div class="container">
          <div class="copyright__wrap">
            <div class="copyright__title">Copyright © 2021 <a class="copyright__link" href="https://eline.cz">eline.cz</a></div>
            <div class="copyright__author">Vytvořeno v <a class="copyright__link" href="http://eline.cz"><img class="lazyload" src="/img/load-symbol.svg" data-src="/img/eline-logo.svg" alt="eline.cz" aria-label="eline.cz"></a></div>
          </div>
        </div>
      </section>
    </footer>';
    
    // retargeting Seznam
    if(__SEZNAM_RETARGETING__)
    {
	   echo '<script  type="text/plain" data-cookiecategory="marketing" src="https://c.seznam.cz/js/rc.js"></script>
			<script  type="text/plain" data-cookiecategory="marketing">
			  window.sznIVA.IS.updateIdentities({
			    eid: null
			  });
			
			  var retargetingConf = {
			    rtgId: '.__SEZNAM_RETARGETING__.',
			    consent: 1
			  };
			  window.rc.retargetingHit(retargetingConf);
			</script>';

	}
 
 ?>    
