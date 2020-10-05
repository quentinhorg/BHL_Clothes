<section id="panier"> 

<div class="panier">
  <!-- Title -->
  <div class="title">
    Mon panier
  </div>
 
  <?php  foreach ($maCommande->panier() as $article) { ?>


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

     
      <button class="minus-btn" type="button" name="button">
      <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-dash-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/>
      </svg>
      </button>
      <input type="text" name="qte" value="<?php echo $article->qte() ?>" max="20">
      <button class="plus-btn" type="button" name="button">
      <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-plus-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
         <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
      </svg>
      </button>
    </div>
 
    <div class="total-price"> <?php echo $article->prix() ?> </div>
    <div class="buttons">
      <span class="delete-btn"></span>
      <span class="like-btn"></span>
    </div>
  </div>

  
  <?php } ?>



</div>

<div id="infoCommande"> 
    <div class="prixCmd">
      180 €
      
      <a href="panier/paiement"> <button> Passer au paiement </button> </a>
    </div>

</div>
</section>

<script>

//Ajout qte
$('.quantity button').on('click', function() {
   var nameClassBut = $(this).attr("class");
   var parent = $(this).parent();
   var valQte = parseInt(parent.find("input").val());

   if(nameClassBut == "minus-btn" && valQte > 1){
      parent.find("input").val(valQte-1);
   }
   else if(nameClassBut == "plus-btn" && valQte < 20){
      parent.find("input").val(valQte+1);
   }
   
});

   HtmlPanier = new HtmlPanier("listeAricle");
   HtmlPanier.setListeHtmlArticle() ;

</script>


