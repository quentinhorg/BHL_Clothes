class Catalogue {


   //Change la couleur des vêtements
   changeColor(){

      $(document).on('click', '.motifVet label', function () {  
         var cadre = $(this).parent().parent().parent();
         var filter = $(this).css( "filter" );
         cadre.find("img.imgArticle").css("filter", filter); 
      });

   }

  

} 
   
