<?php 



class ControleurAdminContact{
   private $vue;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{
         /*---------VUE---------*/
         $this->vue = new VueAdmin('AdminAuthentification') ;
         $this->vue->Popup->setMessage($this->message);
         $this->vue->genererVue(array()) ;
      }
   }

   //Se connecter 
   private function seConnecter($pwd){
      if($pwd == "admin"){         
         $_SESSION["admin"] = true ;
         header("Location: ".URL_SITE."admin/commande");
      }
      else{
         $this->message = "Ididentifiants incorrecte." ;
      }
   }
   
   //Fermer la session admin (se déconnecter)
   private function closeAdminSession(){
      $_SESSION["admin"] = null;
      unset($_SESSION["admin"]);
   }



}

?>