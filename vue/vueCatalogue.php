<?php //var_dump($listeVetement[0]) ; //public/media/vetement/id2.jpg
  echo "<img src='".$listeVetement[0]->getTextureDefaut()."'>";
?>

<div id="navCatalog">
<h5> Homme </h5>
<u>
<li> T-shirt </li>
<li> Pantalon </li>
<li> Vestes </li>
</u>


</div>

<section id="catalogue">
<?php echo $vuePagination ; ?>
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

<?php echo $vuePagination ; ?>


</section>