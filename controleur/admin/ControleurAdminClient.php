<?php 

class ControleurAdminClient{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404);
      }
      else{
         $this->vue = new VueAdmin('AdminClient') ;
         $this->vue->genererVue(array(
           
         )) ;
      }
   }



}

?>