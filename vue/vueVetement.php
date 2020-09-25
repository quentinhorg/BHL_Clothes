

<?php  if($infoVetement != null){ ?>

<!-- <div class="photoVet" style="background-image: url(public/vetement/id2.jpg)"></div> -->
<div class="cadre">
    <div class="img" style="background-image: url(public/media/vetement/id<?php echo $infoVetement->id() ?>.jpg)" ></div>


    <div class="infoVet">
    
        

        <h1> <?php echo $infoVetement->nom() ; ?> </h1>
        
        <p> <?php echo $infoVetement->description() ; ?></p>

        <hr>

        
        <form action="" method="POST" id="vetementChoisi">
            <label for="">Couleur: </label>
            <ul name="couleur" id="couleur">
                <?php foreach($infoVetement->listeCouleurDispo() as $couleur ) { ?>
                    <li value="<?php echo $couleur->num(); ?>"> <?php echo $couleur->nom(); ?> </li>
                <?php } ?>
            </ul>
            <br>
        

            <label for="">Choississez votre taille: </label>
            <ul name="taille" id="taille"> 
                <?php foreach ($infoVetement->listeTailleDispo() as $taille) { ?>
                    <!-- <option value="<?php //echo $taille->libelle() ?>"> <?php// echo $taille->libelle() ; ?> </option> -->
                    <li value="<?php echo $taille->libelle() ?>"> <div class="divTaille"> <?php echo $taille->libelle() ; ?> </div> </li>
                <?php } ?>
            </ul>

            
            <br>

            <label for="">Quantité: </label> <input type="number" name="qte" max="10" value="1" id="quantite">
            <br>

            

           

            <hr style="margin-top: 15px;">

            <h3> <?php echo $infoVetement->prix()."€" ; ?> </h3>

                    <button type="button" value="Ajouter au panier" name="ajouterPanier" id="ajouterPanier" > 
                

                    <svg style="position: absolute; left: 10px; top: 25%; " width="1.4em" height="1.4em" viewBox="0 0 16 16" class="bi bi-bag-plus-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M5.5 3.5a2.5 2.5 0 0 1 5 0V4h-5v-.5zm6 0V4H15v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4h3.5v-.5a3.5 3.5 0 1 1 7 0zM8.5 8a.5.5 0 0 0-1 0v1.5H6a.5.5 0 0 0 0 1h1.5V12a.5.5 0 0 0 1 0v-1.5H10a.5.5 0 0 0 0-1H8.5V8z"/>
                    
                    </svg>
                    
                        <span style=" float: right; margin-right: 15px;"> Ajouter au panier </span>
                
                    </button>
         
        </form>
    </div>
</div>  
<hr style="margin-top: 50px; margin-bottom: 50px;">
<div class="commentaire">

    <h2>Donner votre avis</h2>

    <form action="" method="POST">
        <input type="text" name="pseudo" placeholder="Votre pseudo">
        <input type="text" name="commentaire" placeholder="Votre commentaire">

        <input type="submit" value="Envoyer">
    </form>
</div>

<script>
    FormAjax = new FormAjax();
    $("#ajouterPanier").click(function(){
        FormAjax.envoyerFormulairePOST("vetementChoisi", <?php echo "'idVet=".$infoVetement->id()."'" ?> ,"ajouterArticle" , "panier") ;
    });
    

</script>

<?php }
    else{
        echo "Ce produit n'existe pas";
    } 
?>