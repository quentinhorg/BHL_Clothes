<?php 

require_once('vue/Vue.php');

class ControleurAccueil{
   private $vue;
   public  $message;
   private $VetementManager;
   private $GenreManager;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 1 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{

         /*---------MANAGER---------*/
         $this->VetementManager= new VetementManager();
         $this->GenreManager= new GenreManager();
         /*------------------*/

         $this->vue = new Vue('Accueil') ;
       
         
         $this->vue->setHeader("vue/header.php") ;
         $this->vue->genererVue(array("nouvVetement"=> $this->nouveauteVetement(),
                                      "listeGenre" => $this->listeGenre(),
                                      )) ;
      }
   }

   //retourne les 3 derniers vetements
   private function nouveauteVetement(){
      $listeNouv= $this->VetementManager->getNouveaute();

      return $listeNouv;  
   }

   //retourne la liste de tous les genres
   public function listeGenre(){
      
      $listeGenre= $this->GenreManager->getListeGenre();

      return $listeGenre;
   }


}

?>