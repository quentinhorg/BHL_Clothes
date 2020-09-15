<form action="" method="POST">
        <p> Nom :           <input type="text" name="nom">      </p>
        <p> Prénom :        <input type="text" name="prenom">   </p>
        <p> Email :         <input type="email" name="email">   </p>
        <p> Mot de passe    <input type="password" name="mdp">  </p>
        <p> Adresse :       <input type="text" name="adresse">  </p>
        <p> Téléphone :     <input type="text" name="tel">      </p>
        <p> Envoyez :       <input type="submit" name="submit"> </p>
    </form>
<?php
    if ($message==null) {
        
    }else{
    echo $message;
    }
?>
