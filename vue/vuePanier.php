<?php //var_dump($maCommande); ?>
<div class="panier">
   <table id="listeAricle">
      <tbody>

      
      <?php 
      foreach ($maCommande->panier() as $article) {
         echo "<tr id='articleNb".$article->id()."'>";
         echo  "<td style='background-image: url(public/media/vetement/id".$article->id().".jpg)' class='img'> </td>" ;
         echo  "<td class='nom'>".$article->nom()."</td>" ;
         echo  "<td class='qte'> <input type='number' value='".$article->qte()."' ></td>" ;
         echo "</tr>";
      }
      
      
      ?>
         
        
  

      </tbody>
   </table>
</div>

<script>

   HtmlPanier = new HtmlPanier("listeAricle");
   HtmlPanier.setListeHtmlArticle() ;

</script>
