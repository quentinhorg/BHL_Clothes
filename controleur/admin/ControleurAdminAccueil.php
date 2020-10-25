<?php 

require_once('vue/admin/VueAdmin.php');

class ControleurAdminAccueil{
   private $vue;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404);
      }
      else{
         $this->vue = new VueAdmin('AdminAccueil') ;
         $this->message = "Bienvenue sur l'espace admin, vous pouvez gérer vos données ici." ;
         $this->vue->Popup->setMessage($this->message);

         $this->vue->genererVue(array()) ;
      }
   }



}

?>