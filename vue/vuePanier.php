<?php //var_dump($cmdActif->panier()); ?>

<section id="panier"> 


<?php if( $cmdActif->panier() != null){ ?>


  <div class="container pb-5 mt-n2 mt-md-n3">
    <div class="row">
        <div class="col-xl-9 col-md-8">
            <h2 class="h6 d-flex flex-wrap justify-content-between align-items-center px-4 py-3 bg-secondary"><span>Mes articles</span> <form method="POST" id="formViderPanier" action=""> <button onclick=" return confirm('Etes vous sûre de vouloir supprimez tous vos articles ?')"  type="submit" name="viderPanier" value="Vider mon panier"> Vider mon panier </button> </form>  </a></h2>
            <!-- Item-->
            <?php foreach ($cmdActif->panier() as $article) { ?>

            <?php  $valueArticle = "idVet=".$article->id()."&taille=".$article->Taille()->libelle()."&numClr=".$article->Couleur()->num() ?>
            <div class="d-sm-flex justify-content-between my-4 pb-4 border-bottom">
                <div class="media d-block d-sm-flex text-center text-sm-left">
                    <a class="cart-item-thumb mx-auto mr-sm-4" href="vetement/<?php echo $article->id()  ?>"> <img class="img" style="<?php echo $article->Couleur()->filterCssCode() ?>" src="<?php echo "public/media/vetement/id".$article->id() ?>" alt="Product"></a>
                    <div class="media-body pt-3">
                        <h3 class="product-card-title font-weight-semibold border-0 pb-0"><a href="#"> <?php echo $article->nom() ?></a></h3>
                        <div class="font-size-sm"><span class="text-muted mr-2">Taille:</span><?php echo $article->Taille()->libelle() ?></div>
                        <div class="font-size-sm"><span class="text-muted mr-2">Couleur:</span> <?php echo $article->Couleur()->nom()  ?></div>
                        <div class="font-size-lg text-primary pt-2 total-price-article"><span><?php echo $article->prixTotalArt() ?></span>€ </div>
                    </div>
                </div>
                <div class="pt-2 pt-sm-0 pl-sm-3 mx-auto mx-sm-0 text-center text-sm-left" style="max-width: 10rem;">
                    
                
                    <button value='<?php echo $valueArticle ?>' class="deleteArticle btn btn-outline-danger btn-sm btn-block mb-2" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 mr-1">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                        Supprimer
                    </button>


                        <div class="form-group mb-2">

                      <div class="quantity">
                        <?php 
                        $disabled="";
                        $pasDispo="";
                       
                        
                        if($article->dispo() == false) {
                          
                            $disabled= "disabled";
                            $pasDispo= "Cet article n'est plus disponible.";
                        }


                        ?>

                        <button value='<?php echo $valueArticle ?>' class="minus-btn" type="button" name="button" <?php echo $disabled ?>>
                          <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-dash-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/>
                          </svg>
                        </button>

                        <input type="text" name="qte" <?php echo $disabled ?> value="<?php echo $article->qte() ?>" max="20">
                        <button value='<?php echo $valueArticle ?>' class="plus-btn" type="button" name="button" <?php echo $disabled ?>>
                          <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-plus-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
                          </svg>
                        </button>
                      </div>
                      <div>
                      <?php echo $pasDispo; ?>

                      </div>
                    </div>
                </div>
            </div>

            <?php  }  ?>
           
        </div>
        <!-- Sidebar-->
        <div class="col-xl-3 col-md-4 pt-3 pt-md-0">
            <h2 class="h6 px-4 py-3 bg-secondary text-center">Total</h2>
            <div id="prixCmdHT" class="h3 font-weight-semibold text-center py-3">Prix HT: <span><?php echo $cmdActif->prixHT() ?></span>€</div>
         
            <a class="btn btn-primary btn-block" href="paiement/panier">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card mr-2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>Procéder au paiement</a>
            
        </div>
    </div>
</div>

    
    
 
  <?php } else{ echo "<p class='panierVide'> Votre panier semble être vide. <br> <a href='catalogue'> Continuer vos achats </a> </p>" ;}?>

</section>

<script>

//Ajout qte
$('.quantity button').on('click', function() {
   var nameClassBut = $(this).attr("class");
   var parent = $(this).parent();
   var valQte = parseInt(parent.find("input").val());

   if(nameClassBut == "minus-btn" && valQte > 1){
      parent.find("input").val(valQte-1);
      var submitName = "diminuerQte";
   }
   
   else if(nameClassBut == "plus-btn" ){

    if(valQte < 10){
      parent.find("input").val(valQte+1);
      var submitName = "ajouterArticle";
    }
    else{
     alert("Vous êtes limité à 10 articles de même couleur, taille et de type.");
    }
     
   }

   var postVal = $(this).val()+"&qte=1";
   
   console.log(submitName);
   $.ajax({
		url : "panier",
		data : submitName+"=Ok"+"&"+postVal,
    type : 'POST',
    dataType : 'json',
		success : function (result) {
      
      $("#qtePanierNav span .nbQte").text(result['totalQtePanier']) ; 
      $("#prixCmdHT span").text(result["prixCmdHT"]) ;
     
      parent.parent().parent().parent().find(".total-price-article span").text(result['newPrixArt']);
    }
	});



 
 
   
});

   HtmlPanier = new HtmlPanier("listeAricle");
   HtmlPanier.setListeHtmlArticle() ;



   $('button.deleteArticle').on('click', function() {
    var ligneArticle =  $(this).parent().parent();
   var postVal = $(this).val();
   var submitName = "deleteArticle";

  $.ajax({
		url : "panier",
		data : submitName+"=Ok"+"&"+postVal,
    type : 'POST',
    dataType : 'json',
		success : function (result) {
      $("#qtePanierNav span .nbQte").text(result['totalQtePanier']) ; 
      $("#prixCmdHT span").text(result["prixCmdHT"]) ;
      ligneArticle.addClass("article_hide");
    }
  });
  

   
  });
  

</script>


