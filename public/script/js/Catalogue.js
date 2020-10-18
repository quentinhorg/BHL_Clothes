class Catalogue {


   //Change la couleur des vêtements
   changeColor(){

      $(document).on('click', '.listeCouleur li div.color', function () {  
         var cadre = $(this).parent().parent().parent();
         var filter = $(this).css( "filter" );
         cadre.find("img.imgArticle").css("filter", filter); 
      });

   }

  

}

