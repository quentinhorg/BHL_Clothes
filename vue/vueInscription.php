<form action='' class="form-signin" method="POST" autocomplete="chrome-off">
      <div class="text-center mb-4">
       <a href="accueil" id="logo" ><span style="font-size: 3.4rem">  BHL Clothes    </span> </a>
        <h1 class="h3 mb-3 font-weight-normal">Veuillez vous inscrire</h1>
        <p>Si vous avez déjà un compte, <a href="authentification/connexion">connectez-vous </a></p>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
            <label for="inputNom">Nom</label>
            <input type="text" autofill="off" name="nom" id="inputNom" class="form-control" placeholder="Nom" required autofocus>
        
        </div>

        <div class="form-group col-md-6">
            <label for="inputPrenom">Prénom</label>
            <input type="text" autofill="off" name="prenom" id="inputPrenom" class="form-control" placeholder="Prénom" required autofocus>
            
        </div>


      </div>


      <div class="form-row">

        <div class="form-group col-md-6">
            <label for="inputMdp">Code postal</label>
                <select class="form-control" name="cp" id="">
                    <?php
                    
                    foreach ($listCp as $CodePostal) {
                        echo "<option value='".$CodePostal->cp()."'> ".$CodePostal->cp()." - ".$CodePostal->libelle()." </option>";
                    }
                    
                    ?>
            
                </select>
        </div>

        
        <div class="form-group col-md-6">
        <label for="inputRue">Rue</label> 
            <input type="text" autofill="off" name="rue" id="inputRue" class="form-control" placeholder="Rue" required autofocus>
    
        </div>
    </div>

    <div class="form-group">
      <label for="inputTel">Téléphone</label>
        <input type="text" autofill="off" name="tel" id="inputTel" class="form-control" placeholder="Téléphone" required autofocus>
       
      </div>
      <br>
      <h5> Informations de connexion :</h5>
      <div class="form-row">
      

      <div class="form-group col-md-6">
      <label for="inputEmail">Adresse email</label>
        <input type="email" autofill="off" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
       
      </div> 


      <div class="form-group col-md-6">
      <label for="inputMdp">Mot de passe</label>
        <input type="password" autofill="off" name="mdp" id="inputMdp" class="form-control" placeholder="Mot de passe" required autofocus>
       
      </div>

      </div>

 


   
     



      <button class="btn btn-lg btn-primary btn-block" name="submit" type="submit">S'inscrire</button>
   
      <p class="mt-5 mb-3 text-muted text-center">&copy; 2020-2021</p>
    </form>




<?php


    if ($message==null) {
        
    }else{
    echo $message;
    }
?>
