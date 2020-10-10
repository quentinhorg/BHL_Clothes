

<section id="panier"> 
<?php if( $maCommande->panier() != null){ ?>

<div class="panier">
  <!-- Title -->
  <div class="title">
    Mon panier
    <a href="panier/paiement"> <button> Passer au paiement </button> </a>
  </div>
 
  <?php  foreach ($maCommande->panier() as $article) {
    
    $valueArticle = "idVet=".$article->id()."&taille=".$article->Taille()->libelle()."&numClr=".$article->Couleur()->num()
    
    ?>


  <!-- Product #1 -->
  <div class="article" id="<?php echo "articleNb".$article->id() ?>">

    
    <div class="image">
    <img class="img" style="<?php echo $article->Couleur()->filterCssCode() ?>" src="<?php echo "public/media/vetement/id".$article->id() ?>" alt="" />
    </div>
 
    <div class="description">
      <span><?php echo $article->nom() ?></span>
      <span class='categ' ><?php echo $article->categ()->nom() ?></span>
      <span class='tailleClr' ><?php echo" <div class='color' style='background-image:url(public/media/vetement/id".$article->id().".jpg); ".$article->Couleur()->filterCssCode()."'></div>Taille: ".$article->Taille()->libelle() ?></span>
    </div>
 
    <div class="quantity">

     
      <button value='<?php echo $valueArticle ?>' class="minus-btn" type="button" name="button">
      <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-dash-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/>
      </svg>
      </button>
      <input type="text" name="qte" value="<?php echo $article->qte() ?>" max="20">
      <button value='<?php echo $valueArticle ?>' class="plus-btn" type="button" name="button">
      <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-plus-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
         <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
      </svg>
      </button>
    </div>
 
    <div class="total-price"> <?php echo $article->prix() ?> </div>
    <div class="buttons">
      <?php  ?>
     <button value='<?php echo $valueArticle ?>' type='button' class='deleteArticle'>Supprimer</button>
     
    </div>
  </div>

  
  <?php } ?>



</div>

<!-- <div id="infoCommande"> 
    <div class="prixCmd">
    
      <a href="panier/paiement"> <button> Passer au paiement </button> </a>
    </div>

</div> -->

  <?php } else{ echo "<p class='panierVide'> Votre panier semble être vide. <br> <a href='catalogue'> Continuer vos achats</a> </p>" ;}?>

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
   else if(nameClassBut == "plus-btn" && valQte < 10){
      parent.find("input").val(valQte+1);
      var submitName = "ajouterArticle";
      
   }

   var postVal = $(this).val()+"&qte=1";
   
   FormAjax.envoyerPOST(submitName, postVal, "panier");
   $("#qtePanierNav").load("accueil #qtePanierNav >");
   
});

   HtmlPanier = new HtmlPanier("listeAricle");
   HtmlPanier.setListeHtmlArticle() ;





   FormAjax = new FormAjax;

   $('button.deleteArticle').on('click', function() {
   var postVal = $(this).val();
   var submitName = "deleteArticle";
   $(this).parent().parent().addClass("article_hide");

    FormAjax.envoyerPOST(submitName, postVal, "panier");
    $("#qtePanierNav").load("accueil #qtePanierNav >");
  });
  

</script>


