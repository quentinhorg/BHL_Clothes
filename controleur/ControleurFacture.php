<?php 

require_once('vue/Vue.php');

class ControleurFacture{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){

      if( isset($url) && count($url) > 2 ){
         throw new Exception('Page introuvable');
      }
      else{
       


         
         if(
            isset($url[1]) && $GLOBALS["client_en_ligne"] != null 
            && $this->facture($url[1])->Commande()->Etat()->id() != 1 
            && $GLOBALS["client_en_ligne"]->getId() == $this->facture($url[1])->Commande()->idClient()
         ){
       
            $client = $GLOBALS["client_en_ligne"] ;
            $facture = $this->facture($url[1]) ;
            $listeCp = $this->listeCp();

            include "vue/vueFacture.php";

         }
         else{
            throw new Exception('Page introuvable');
         }
         
      }

   }

 
   
   
   public function facture($idCmd){
      $FactureManager = new FactureManager();
      $facture = $FactureManager->getFacture($idCmd);
      return $facture ;
   }


   //$CodePostalManager->getListCp();
   public function listeCp(){
      $CodePostalManager = new CodePostalManager();
      $listeCodePostal = $CodePostalManager->getListCp();

      return $listeCodePostal;
   }

}

?>