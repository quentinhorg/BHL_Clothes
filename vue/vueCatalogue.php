

<hr>
<div class="d-flex" id="wrapper">
    
<div class="bg-light border-right" id="navCatalogue">

      <div class="list-group list-group-flush">
       
<form action="" method="GET" id="formFiltrage">



    <?php

    if( $genreActive != null){

        echo "<h3>".$genreActive->libelle()."</h3>" ;
        echo "<ul>";
        foreach ($genreActive->listeCateg() as $categ) {
            echo "<li> <a href='catalogue/".$genreActive->code()."/".$categ->id()."'>".$categ->nom()."</a> </li>" ;
        }
        echo "</ul>" ;

    }
    else{
        echo "<h3> Tous le catalogue </h3>" ;

     
        echo "<ul>";
        foreach ($listeGenre as $genre) {
            
            echo "<li> <a href='catalogue/".$genre->code()."'>".$genre->libelle()." </a> </li>" ;
        }
        echo "</ul>" ;
        


    }
    ?>
        <?php 
            if($listeTaille != null){

               

                echo "<hr>";
                echo "<h3> Taille </h3>";
                

              
                echo "<div class='tailleList'>" ;
                foreach ($listeTaille as $typeTaille => $Taille) {
                    echo "<ul>";
                
                    foreach ($listeTaille[$typeTaille] as $Taille ){ 
                        $checked = "";
                        //garder les champs
                        if(isset($_GET["taille"]) && in_array($Taille->libelle(), $_GET["taille"])){
                            $checked = "checked";
                        }

                        echo "<li> " ;

                        echo "<label for='taille_".$Taille->libelle()."' class='container'>".$Taille->libelle() ;
                        echo "<input $checked name='taille[]' value='".$Taille->libelle()."' id='taille_".$Taille->libelle()."' type='checkbox' >" ;
                        echo "<span class='checkmark'> </span>" ;
                        echo "</label>" ;

                    //echo     "<input name='taille[]' id='taille_".$Taille->libelle()."' type='checkbox' value='".$Taille->libelle()."'>  <label for='taille_".$Taille->libelle()."'> ".$Taille->libelle()."</label>" ;
                        echo "</li>";
                        
                        $checked = null;
                    }
                echo "</ul>" ;
             }
             echo "</div>" ;
               

            }
                
        ?>
    <hr>
    <h3>Couleur</h3>

    <ul>

    <?php 
    $checked = null;
    foreach ($listClrPrincipale as $couleur) {
        
        //garder les champs
        if(isset($_GET["couleur"]) && in_array($couleur, $_GET["couleur"])){
            $checked = "checked";
        }


       echo "<li> " ;
       echo "<label for='clr_$couleur' class='container'>$couleur" ;
       echo "<input $checked name='couleur[]' value='$couleur' id='clr_$couleur' type='checkbox' >" ;
       echo "<span class='checkmark'> </span>" ;
       echo "</label>" ;

      
       echo "</li>";
       $checked = null;
    }
    ?>

    </ul>

    <hr>
    <h3>Budget Max</h3>
    <?php 
    $budget = null;
    if(isset($_GET["budget"])){
        $budget = $_GET["budget"];
    }
    ?>
    <input placeholder="Aucun" type="number" name="budget" value="<?php echo $budget ?>">€

     <input type="submit" id="filtrer" value="Trier le catalogue" name='trier'>           
    </form>
<!-- test -->

</div>
     
     </div>


<section id="catalogue">
<p style='text-align:left;'> <button class="btn btn-primary" id="menu-toggle">&#9776; Menu</button> </p>
<div id="listVetement">


<p id="currentPage"> <?php if($genreActive != null){ echo "<a href='catalogue/".$genreActive->code()."'>".$genreActive->libelle()."</a>" ;} else{ echo "Tous le catalogue" ;} if($categActive != null) {echo " > ".$categActive->nom(); } ?> </p>


<hr>

<?php if($listeVetement != null){?>

<?php 

if( $vuePagination != null){
    echo $vuePagination ; 
}

?>

<?php foreach ($listeVetement as $vetement) { ?>
    <div class="cadreVet">
    <a href="vetement/<?php echo $vetement->id() ?>"><img class="imgArticle" src="public/media/vetement/id<?php echo $vetement->id() ?>.jpg" alt="">  </a>
        <p>
        <p class="titre"> <?php echo $vetement->nom() ?>   <span class="taille">(<?php foreach ($vetement->listeTailleDispo() as $ind => $taille) {
              if ($ind >= 1) echo ", " ;   echo $taille->libelle() ;
            } ?>) </span> </p> 

            <span class="prix"> <?php echo $vetement->prix()."€" ?> </span>

            
     
        </p>
        <ul class="listeCouleur">
            <?php 
                //Affichage des listes de couleurs disponible
        
                foreach ($vetement->listeCouleurDispo() as $couleur) {
                    $idInput = "vet".$vetement->id()."_couleur".$couleur->num();
                    echo "<li > <div class='color' style='background-image:url(public/media/vetement/id".$vetement->id().".jpg); filter: ".$couleur->filterCssCode().";".$vetement->motifPosition()."'></div> <input name='"."vet".$vetement->id()."' id='$idInput' type='radio'> <label for='$idInput' style='filter: ".$couleur->filterCssCode().";' title='".$couleur->nom()."'>  </label> </li>";
                }
               
               
            ?>
            

        </ul>

    </div>

<?php } 


    if( $vuePagination != null){
        echo $vuePagination ; 
    }

}else{
    echo "Aucun résultat...";
}

?>
</div>

<?php 




?>


</section>
</div>


<script>
Catalogue = new Catalogue();
Catalogue.changeColor();

 
$("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
});



</script>