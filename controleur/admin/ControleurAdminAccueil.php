<?php 

require_once('vue/admin/VueAdmin.php');

class ControleurAdminAccueil{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404);
      }
      else{
         $this->vue = new VueAdmin('AdminAccueil') ;
         $this->vue->genererVue(array(
           
         )) ;
      }
   }



}

?>