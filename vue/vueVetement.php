

<?php  if($infoVetement != null){ ?>

<!-- <div class="photoVet" style="background-image: url(public/vetement/id2.jpg)"></div> -->
<div class="cadre">
    <div class="img"> <img src="public/media/vetement/id<?php echo $infoVetement->id() ?>.jpg" alt="Image du vêtement" class="img-responsive"> </div>


    <div class="infoVet">
        
        <h1> <?php echo $infoVetement->nom() ; ?> </h1>

        <p> <?php echo $infoVetement->description() ; ?></p>

        <hr>

        
        <form action="" method="POST" id="vetementChoisi">
            <label for="">Couleur: </label>
            <ul class='listeCouleur' id="couleur">
            <!-- <select name="numClr"> -->
                <?php foreach($infoVetement->listeCouleurDispo() as $indice => $couleur ) { 
                    $idColor = "numClr".$couleur->num();
                    ?>
                    <li>  
                        <label style="background-image:url(public/media/vetement/id<?php echo $infoVetement->id() ?>.jpg); <?php echo $couleur->filterCssCode()  ?>" for="<?php  echo $idColor ?>"> </label> 
                        <input <?php if($indice == 0){ echo "checked" ;} ?> name="numClr" style="display:none" id='<?php echo $idColor ?>' value="<?php echo $couleur->num(); ?>" type="radio" > 
                        
                    
                    </li>
                <?php } ?>
            </select>
            </ul>
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



<div class="avis">


    <?php
            if($client != null) { ?>
                
                <h2>Donnez votre avis</h2>

                <div class="donnerAvis">

                    <form action="" method="POST">
                        <textarea type="text" name="avis" placeholder="Votre avis"></textarea>  <!-- avis -->
                        <input type="number" id="noteVet" name="note" value="0" style="visibility: hidden; display:none;">

                        <!-- note -->
                        <div class="donnerNote" style="display:flex">
                            Note:
                            <span style="font-size:40px;cursor:pointer;color:black" class="checked" name="note">★</span> <!-- si pb mettre class="fa fa-star checked" -->
                            <span style="font-size:40px;cursor:pointer;color:black" name="note">★</span>
                            <span style="font-size:40px;cursor:pointer;color:black" name="note">★</span>
                            <span style="font-size:40px;cursor:pointer;color:black" name="note">★</span>
                            <span style="font-size:40px;cursor:pointer;color:black" name="note">★</span> <!-- si pb mettre class="fa fa-star" pour 4 dernieres lignes-->
                        </div>
                        <br/>

                        <input type="submit" value="Envoyer" name="envoyerAvis" class="btn btn-lg btn-success">
                    </form>
                </div>

        <?php } 
            else{
                echo "<h2>Donnez votre avis</h2>";
                echo "Veuillez vous <a href='authentification/connexion' style='text-decoration: underline;'>connecter</a> pour poster un avis.";
        }?>


    <h2>Avis des clients (<?php echo $infoVetement->nbAvis() ; ?>)</h2>
    
    <ul class="listeAvis">
    <?php
        
        foreach ($listeAvis as $avis) {
            $date= new DateTime($avis->date()); ?>
            <li class="blocAvis">
                
                <div class="contenuBloc">
                    <div class="info">
                            <p><?php echo $avis->Client()->getNom()." ".$avis->Client()->getPrenom(); ?></p>
                            <div class="note">
                    
                                <?php 
                                
                                    $couleur="orange";
                                    for ($i=1; $i <=5 ; $i++) { 
                                        echo "<span style='font-size:22px;color: $couleur;'>★</span>";

                                        if($i == $avis->note()){
                                            $couleur= "black";
                                        }
                                    }
                                    
                                
                                
                                ?>
                        
                        
                            </div>
 
                        <span class="date"><?php echo "Le ".date_format($date, 'd/m/Y à H\hi') ; ?></span> 
                    </div>
            
                    
                    <p class="contenuComm"><?php echo $avis->commentaire(); ?></p> 
                </div>
                
            </li>
           <br>
            
    <?php    } ?>
    </ul>
   
    
    

 
    

</div>




<?php }
    else{
        echo "Ce produit n'existe pas";
    } 
?>





<script>

    //Hover étoiles
        var spanClick = null;

        //Clique
        $( ".donnerNote span" ).click(function(){
            spanClick = $(this);
            var note = $(this).index()+1;

            $("#noteVet").attr("value",note);
            $("#noteVet").prop("value",note);

        //Survolage interieur
        }).hover(
            function(){
                var spanHover = $(this);

                spanHover.parent().find("span").each(
                    function(){
                        if( $(this).index() <= spanHover.index() ){

                            $(this).css("color","orange");
                            
                        }
                        else{
                            $(this).css("color","black");
                        }
                    }
                )
            }, 
            //Survolage exterieur
            function() {
                $(".donnerNote span").each(function(event){
                    
                    
                    if(  spanClick == null || ( spanClick != null && $(this).index() > spanClick.index() ) ){
                         $(this).css("color",'black');
                    }
                    else{
                        $(this).css("color",'orange');
                    }
                   
                   
                }  );
            }
          
        );

        






    var message = <?php echo json_encode($msg); ?>;
    
    if(message != null){
        alert(message); 
    }
    
    function ajoutArticle() {
        alert("Votre article a bien été ajouté au panier.");
    }

    $("#ajouterPanier").click(function(){

    var form = $("#vetementChoisi");
    var serializedData = form.serialize();
    $.ajax({
        url : "panier",
        data : "ajouterArticle=Ok&"+serializedData+"&idVet=<?php echo $infoVetement->id() ?>",
        type : 'POST',
        dataType : 'json',
        success : function(result) {
            $("#qtePanierNav span .nbQte").text(result['totalQtePanier']) ; 
        }
    });

    }) ;


    var count;




    Vetement = new Vetement;
    Vetement.changeColor();

    // function result(){
    //     //Rating : Count
    //     //Review : Comment(id)
    //     alert("Rating : "+count+"\nReview : "+document.getElementById("comment").value);
    // }

     
   

</script>