class Vetement {

   //Change la couleur des vêtements
   changeColor(){
      $(".listeCouleur li label").click(function(){
         var filter = $(this).css( "filter" );
         $("div.img img").css("filter", filter); 
      });
      

   }

  

}

