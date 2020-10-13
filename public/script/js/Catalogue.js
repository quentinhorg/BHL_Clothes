class Catalogue {


   //Change la couleur des vêtements
   changeColor(){
      $(".listeCouleur li div.color").click(function(){
         var cadre = $(this).parent().parent().parent();
         var filter = $(this).css( "filter" );
         cadre.find("img.imgArticle").css("filter", filter); 
      });
      

   }

  

}

