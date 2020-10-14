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
         //$url[0] -> facture ; $url[1] -> numFacture


         
         if(isset($url[1])){
            $client = $GLOBALS["client_en_ligne"] ;
            $commande = $this->getCommande($url[1]) ;
            $listeCp = $this->listeCp();
            include "vue/vueFacture.php";
         
         }else{
            throw new Exception('Page introuvable');
         }
         
      }

   }


   
   
   public function getCommande($num){

      $CommandeManager = new CommandeManager();
      $commande = $CommandeManager->getCommande($num);
      return $commande ;
   }


   //$CodePostalManager->getListCp();
   public function listeCp(){
      $CodePostalManager = new CodePostalManager();
      $listeCodePostal = $CodePostalManager->getListCP();

      return $listeCodePostal;
   }

}

?>