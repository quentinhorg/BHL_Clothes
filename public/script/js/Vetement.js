class Vetement {

   //Change la couleur des vêtements
   changeColor(){
      $(".motifVet label").click(function(){
         var filter = $(this).css( "filter" );
         $("div.img img").css("filter", filter); 
      });
      

   }

  

}

