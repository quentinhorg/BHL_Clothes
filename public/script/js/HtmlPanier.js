
class HtmlPanier {

   constructor(strId) {
      this.elementId = $("#"+strId);
      this.listeHtmlArticle = {};

   }

   setListeHtmlArticle(){
      var i = 1;

      this.elementId.find("tr").each(function(){
         var tr = $(this) ;
       
         var strId = "articleNb"+i;
         tr.prop("id",strId);
         i++;
      });

      
   }

   suppArticle(strIdHtmlArticle){
      
      delete this.listeHtmlArticle[strIdHtmlArticle] ;
      
   }




  

}