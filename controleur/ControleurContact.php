<?php 
require_once('vue/Vue.php');

class ControleurContact{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
   
      if( isset($url) && count($url) > 1 ){
         throw new Exception('Page introuvable');
      }
      else{
         
         $this->vue = new Vue('Contact') ;
         $this->vue->genererVue(array( 
 
         )) ;
         
      }

      if (isset($_POST['Envoyer']) ) {
         
         if(!empty($_POST['nom'])){
            
            if(!empty($_POST['email'])){
         
               if(!empty($_POST['tel'])){
         
                  if(!empty($_POST['sujet'])){
         
                     if(!empty($_POST['message'])){
         
                        $this->insertBDDContact();
                     }
                     else{
                        echo "Veuillez entrer un message.";
                     }
                  }
                  else{
                     echo "Veuillez entrer un sujet.";
                  }
               }
               else{
                  echo "Veuillez entrer un numéro.";
               }
            }
            else{
               echo "Veuillez entrer un email.";
            }
         
         }
         else{
            echo "Veuillez entrer un nom.";
         }
         
      }  
   }

   //retourne les 3 derniers vetements
   private function insertBDDContact(){
      
      $ContactManager = new ContactManager();
    
      $insertBDDContact= $ContactManager->insertBDDContact();

      return $insertBDDContact;
     
   }


}

?>