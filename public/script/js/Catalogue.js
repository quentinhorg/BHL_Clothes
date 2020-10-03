class Catalogue {

   //Change la couleur des vêtements
   changeColor(){
      $(".listeCouleur li div.color").click(function(){
         var cadre = $(this).parent().parent().parent();
         var filter = $(this).css( "filter" );
         cadre.find("div.img").css("filter", filter); 
      });
      

   }

  

}

