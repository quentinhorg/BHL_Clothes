class HtmlArticle {

   constructor(strId) {
      this.elementId = $("#aaa"+strId);
      this.nom = this.elementId.find(".nom").text() ;
      this.qte = this.elementId.find(".qte input").val() ;
   }

   getNom(){
      return this.nom ;
   }

   getQte(){
      return this.qte ;
   }

   setNom(value){
      this.nom = value;
      this.elementId.find(".nom").text(this.nom) ;
   }

   setPrix(value){
      this.prix = value;
      this.elementId.find(".prix input").val(this.prix) ;
   }

   setQte(value){
      this.qte = value;
      this.elementId.find(".qte input").val(this.qte) ;
   }

   changeQte(){
      
   }


}