<!-- přihlášení-->
	<div class="modal micromodal-slide" id="modal-login">
      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-login-title">
          <div class="modal__content" id="modal-login-content">
            <div class="modal__header">
              <h2 class="modal__title" id="modal-login-title">Přihlášení</h2>
              <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </div>
            <form method="post" id="login-form" action="/prihlaseni">
              <div class="form-group"> 
                <label for="login-email">Email</label>
                <input type="email" id="login-email" name="login_form_email" required>
              </div>
              <div class="form-group"> 
                <label for="login-password">Heslo</label>
                <input type="password" id="login-password" name="login_form_pass" required><span class="show-password" id="showLoginPassword" aria-label="Zobrazit heslo"></span> 
              </div><a class="forgot-password" href="/zapomenute-heslo">Zapomenuté heslo?</a>
              <div class="form-group"> 
                <button class="btn --fullWidth" name="login_form_submit" id="login-submit" value="<?php echo md5(time());?>">Přihlásit <img src="/ikony/cheveron-white.svg" alt="Přihlásit"></button>
              </div><span class="register">Nemáte u nás účet? <a href="/registrace">Zaregistrujte se</a></span>
            </form>
          </div>
          <div class="modal__side" id="modal-login-side"><img class="lazyload" src="/img/load-symbol.svg" data-src="/img/login-modal.png" width="288" height="544"></div>
        </div>
      </div>
    </div>
    
 
