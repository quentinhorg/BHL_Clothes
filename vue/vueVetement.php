

<?php  if($infoVetement != null){ ?>

<!-- <div class="photoVet" style="background-image: url(public/vetement/id2.jpg)"></div> -->
<div class="cadre">
    <div class="img" style="background-image: url(public/media/vetement/id<?php echo $infoVetement->id() ?>.jpg)" ></div>


    <div class="infoVet">
    
        

        <h1> <?php echo $infoVetement->nom() ; ?> </h1>
        
        <p> <?php echo $infoVetement->description() ; ?></p>

        <h3> <?php echo $infoVetement->prix()."€" ; ?> </h3>
        <br>

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

            

            

            

            <input type="button" value="Ajouter au panier" name="ajouterPanier" id="ajouterPanier">
        </form>
    </div>
</div>  

<div class="commentaire">
    <form action="" method="POST">
        <input type="text" name="pseudo" placeholder="Votre pseudo">
        <input type="text" name="commentaire" placeholder="Votre commentaire">

        <input type="submit" value="EnvoyerComm">
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