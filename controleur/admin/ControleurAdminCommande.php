<?php 



class ControleurAdminCommande{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404);
      }
      else{
         $this->vue = new VueAdmin('AdminCommande') ;
         $this->vue->genererVue(array(
           
         )) ;
      }
   }



}

?>