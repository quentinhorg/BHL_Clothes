<form action="" method="POST">
      
      <p> Email :         <input type="text" name="email">       </p>
      <p> Mot de passe :  <input type="password" name="mdp">    </p>
      <p> Connexion       <input type="submit" name="submit">     </p>
  </form>

  <p> Nouveau sur sur ce site ? <a style="color:blue;text-decoration:underline" href="authentification/inscription">cliquez ici</a> pour vous inscrire</p>
<?php
  if ($message==null) {
      
  }else{
  echo $message;
  }
?>
