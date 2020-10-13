<form action='' class="form-signin" method="POST">
      <div class="text-center mb-4">
        <span id="logo" style="font-size"> BHL Clothes </span>
        <h1 class="h3 mb-3 font-weight-normal">Se connecter</h1>
        <p>Veuilliez vous connecter. Si vous êtes nouveau sur le site,  <a href="authentification/inscription">inscrivez-vous </a></p>
      </div>

      <div class="form-label-group">
        <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
        <label for="inputEmail">Email address</label>
      </div>

      <div class="form-label-group">
        <input type="password" name="mdp" id="inputPassword" class="form-control" placeholder="Password" required>
        <label for="inputPassword">Password</label>
      </div>

      <div class="checkbox mb-3">
        <label>
          <input type="checkbox" value="remember-me"> Remember me
        </label>
      </div>
      <button class="btn btn-lg btn-primary btn-block" name="submit" type="submit">Se connecter</button>
   
      <p class="mt-5 mb-3 text-muted text-center">&copy; 2019-201</p>
    </form>




 
<?php
  if ($message==null) {
      
  }else{
  echo $message;
  }
?>


