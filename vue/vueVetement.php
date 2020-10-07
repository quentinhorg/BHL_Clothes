

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
            <!-- <ul name="couleur" id="couleur"> -->
            <select name="couleur">
                <?php foreach($infoVetement->listeCouleurDispo() as $couleur ) { ?>
                    <option value="<?php echo $couleur->num(); ?>"> <?php echo  $couleur->nom(); ?>  </option>
                    <!-- <li value="<?php //echo $couleur->num(); ?>"> <?php // echo  $couleur->nom(); ?> </li> -->
                <?php } ?>
            </select>
            <!-- </ul> -->
            <br>
        

            <label for="">Choississez votre taille: </label>
            <!-- <ul name="taille" id="taille">  -->
                <?php //foreach ($infoVetement->listeTailleDispo() as $taille) { ?>
                    <!-- <option value="<?php //echo $taille->libelle() ?>"> <?php// echo $taille->libelle() ; ?> </option> -->
                    <!-- <li value="<?php //echo $taille->libelle() ?>"> <div class="divTaille"> <?php //echo $taille->libelle() ; ?> </div> </li> -->
                <?php //} ?>
            <!-- </ul> -->

            <select name="taille" id="taille">
            <?php foreach ($infoVetement->listeTailleDispo() as $taille) { ?>
                <option value="<?php echo $taille->libelle() ?>"><?php echo $taille->libelle() ; ?></option>
            <?php } ?>
            </select>

            
            <br>

            <label for="">Quantité: </label> <input type="number" name="qte" max="10" value="1" id="quantite">
            <br>

            

           

            <hr style="margin-top: 15px;">

            <h3> <?php echo $infoVetement->prix()."€" ; ?> </h3>

                    <button type="button" value="Ajouter au panier" name="ajouterPanier" id="ajouterPanier" onclick="ajoutArticle()"> 
                

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

    <h2>Avis des clients (<?php echo $infoVetement->nbCommentaire() ; ?>)</h2>

    <?php
        
        foreach ($listeCommentaire as $commentaire) {
            $date= new DateTime($commentaire->date()); ?>
            <div class="blocCommentaire">
                <div class="note"><?php echo $commentaire->note(); ?></div>
                <div class="commentaire"> <?php echo $commentaire->commentaire(); ?></div>
                <div class="date"><?php echo "Le ".date_format($date, 'd/m/Y à H\hi') ; ?></div>
            </div>
           <br>
            
    <?php    } ?>
   
    <?php
        if($client != null) { ?>
            
            <h2>Donnez votre avis</h2>

            <form action="" method="POST">
                <textarea type="text" name="commentaire" placeholder="Votre commentaire"></textarea>  <!-- avis -->
                <input type="number" id="noteVet" name="note" value="0" style="visibility: hidden; display:none;">

                <!-- note -->
                <span  onclick="starmark(this)" id="1one" style="font-size:40px;cursor:pointer;" class="checked" name="note">★</span> <!-- si pb mettre class="fa fa-star checked" -->
                <span  onclick="starmark(this)" id="2one" style="font-size:40px;cursor:pointer;" name="note">★</span>
                <span  onclick="starmark(this)" id="3one" style="font-size:40px;cursor:pointer;" name="note">★</span>
                <span  onclick="starmark(this)" id="4one" style="font-size:40px;cursor:pointer;" name="note">★</span>
                <span  onclick="starmark(this)" id="5one" style="font-size:40px;cursor:pointer;" name="note">★</span> <!-- si pb mettre class="fa fa-star" pour 4 dernieres lignes-->
                <br/>

                <input type="submit" value="Envoyer" name="envoyerCommentaire" class="btn btn-lg btn-success">
            </form>

    <?php } 
        else{
            echo "<h2>Donnez votre avis</h2>";
            echo "Veuillez vous connecter pour poster un avis.";
    }?>

    

 
    

</div>




<?php }
    else{
        echo "Ce produit n'existe pas";
    } 
?>





<script>
    var message = <?php echo json_encode($msg); ?>;
    
    if(message != null){
        alert(message); 
    }
    
    function ajoutArticle() {
        alert("Votre article a bien été ajouté au panier.");
    }

    
    FormAjax = new FormAjax();
    $("#ajouterPanier").click(function(){
        FormAjax.envoyerFormulairePOST("vetementChoisi", <?php echo "'idVet=".$infoVetement->id()."'" ?> ,"ajouterArticle" , "panier") ;
    });




    var count;

    function starmark(item){
        count=item.id[0];
        sessionStorage.starRating = count;
        var subid= item.id.substring(1);
        // alert(count);
        $("#noteVet").prop("value", count) ;
        $("#noteVet").attr("value", count) ;



        for(var i=0;i<5;i++) {
            
            if(i<count){
                document.getElementById((i+1)+subid).style.color="orange";
            }
            else{
                document.getElementById((i+1)+subid).style.color="black";
            }
        }
    }

    // function result(){
    //     //Rating : Count
    //     //Review : Comment(id)
    //     alert("Rating : "+count+"\nReview : "+document.getElementById("comment").value);
    // }

     
   

</script>