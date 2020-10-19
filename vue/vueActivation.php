<form action='' class="form-signin" method="POST">
      <div class="text-center mb-4">
        <span id="logo" style="font-size"> Activer votre compte </span>
        <h1 class="h3 mb-3 font-weight-normal">Se connecter</h1>
        <p>Veuilliez vous connecter. Si vous êtes nouveau sur le site,  <a href="authentification/inscription">inscrivez-vous </a></p>
      </div>

      <div class="form-label-group">
        aaa
      </div>

      <div class="form-label-group">
        aa
      </div>

      <button class="btn btn-lg btn-primary btn-block" name="submit" type="submit">Se connecter</button>
   
      <p class="mt-5 mb-3 text-muted text-center">&copy; 2020-2021</p>
    </form>
<?php
  if ($message==null) {
      
  }else{
  echo $message;
  }
?>


