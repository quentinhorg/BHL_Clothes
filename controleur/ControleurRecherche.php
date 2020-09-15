<?php 
require_once('vue/Vue.php');

class ControleurVetement{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      

      if( isset($url) && count($url) > 2 ){
         throw new Exception('Page introuvable');
      }
      else{

         $id= $url[1] ;

         $this->vue = new Vue('Vetement') ;
         $this->vue->genererVue(array( 
            "infoVetement"=> $this->infoVetement($id)
         )) ;
      }

      
   }

   //retourne les 3 derniers vetements
   private function infoVetement($id){
      $VetementManageur = new VetementManager();
      $infoVetement= $VetementManageur->getVetement($id);
   }

}