<?php 

require_once('vue/Vue.php');

class ControleurAccueil{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 1 ){
         throw new Exception('Page introuvable');
      }
      else{

         $this->vue = new Vue('Accueil') ;
         $this->vue->setHeader("vue/header.php") ;
         $this->vue->genererVue(array("nouvVetement"=> $this->nouveauteVetement(),
                                      "listeGenre" => $this->listeGenre()
                                      )) ;
      }
   }

   //retourne les 3 derniers vetements
   private function nouveauteVetement(){
      $VetementManageur = new VetementManager();
    
      $listeNouv= $VetementManageur->getNouveaute();

      return $listeNouv;  
   }

   public function listeGenre(){
      $GenreManager= new GenreManager();

      $listeGenre= $GenreManager->getListeGenre();

      return $listeGenre;
   }




}

?>