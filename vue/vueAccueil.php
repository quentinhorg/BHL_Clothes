<div class="genre">

    <?php foreach ($listeGenre as $genre) { ?>
        
            <div class="blocGenre imgGenre">
                <a href="catalogue/<?php echo $genre->code(); ?>"> <img src="public/media/accueil/modele/<?php echo $genre->code(); ?>.jpg" alt=""> </a>

                    <div class="textGenre"> <?php echo "<a href='catalogue/".$genre->code()."'>".$genre->libelle()."</a>"; ?> </div>
               
            </div>
        
    <?php }  ?>
    
</div>


















<div class="nouveaute">
<h1 style ="color:red">Test</h1>

<?php //var_dump($nouvVetement) ; 

    foreach($nouvVetement as $vetement){
        echo $vetement->nom();
    }

?>

</div>