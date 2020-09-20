<div id="navCatalogue">
<form action="" method="POST">
    <h3> Homme </h3>
    <ul>
        <li> T-shirt </li>
        <li> Pantalon </li>
        <li> Vestes </li>
    </ul>

    <h3> Taille </h3>
    <ul>
        <?php foreach ($listeTaille as $libelle ){ ?> 
        <li> 
            <input <?php echo "name='taille[]'"; ?> type ="checkbox" <?php echo "value='".$libelle->id()."'" ?> > 
            <?php echo $libelle->libelle() ; ?> 
        </li> 
           <?php }
         ?>
        
    </ul>
    <h3>Couleur</h3>

    <ul>

    <?php 
    
    foreach ($listClrPrincipale as $couleur) {
       echo "<li> <input name='couleur[]' id='clr_$couleur' type='checkbox' value='$couleur'>  <label for='clr_$couleur'>  $couleur </li>";
    }
    
    
    ?>

    </ul>

     <input type="submit" name="recherche">           
    </form>


</div>

<section id="catalogue">
<?php

if( $vuePagination != null){
    echo $vuePagination ; 
}



?>
<?php foreach ($listeVetement as $vetement) { ?>
    <div class="cadreVet">
        <a href="vetement/<?php echo $vetement->id() ?>"><div class="img" style="background-image: url(public/media/vetement/id<?php echo $vetement->id() ?>.jpg)" ></div></a>
        <p>
            <span class="titre"> <?php echo $vetement->nom() ?> </span>
            <span class="prix"> <?php echo $vetement->prix()."€" ?> </span>
        </p>
        <ul class="listeCouleur">
            <?php 
                //Affichage des listes de couleurs disponible
                if($vetement->listeCouleurDispo() != null){
                    foreach ($vetement->listeCouleurDispo() as $couleur) {
                        $idInput = "vet".$vetement->id()."_couleur".$couleur->num();
                        echo "<li > <input name='"."vet".$vetement->id()."' id='$idInput' type='radio'> <label for='$idInput' style='filter: ".$couleur->filterCssCode()."; background-color:".$vetement->codeRgbOriginal()."' title='".$couleur->nom()."'>  </label> </li>";
                    }
                }
                else{
                    
                    echo "<li > <span title=''> 0C </span> </li>";
                    
                }
            ?>
            

        </ul>

    </div>

<?php } ?>

<?php 

if( $vuePagination != null){
    echo $vuePagination ; 
}

?>


</section>