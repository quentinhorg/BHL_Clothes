<?php 
require_once('vue/Vue.php');

class ControleurPaiement{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
   
      if( isset($url) && count($url) > 2 ){
         throw new Exception('Page introuvable');
      }
      else{
      
        if($url[1] == "panier" && $this->peutProcederPaye() ){
       
         if(  isset($_POST["payerCmd"]) ){
            $this->payerPanierActif();
         }
            

         $this->vue = new Vue('Paiement') ;
         $donneeVue = array(
            "clientInfo"=> $GLOBALS["client_en_ligne"],
            "maCommande"=> $this->maCommande()
         ) ;
         $this->vue->genererVue($donneeVue) ;
       
           
        }
         else if ($GLOBALS["client_en_ligne"] == null && COUNT($this->maCommande()->panier()) >= 1 ) {
            header("Location: ".URL_SITE."/authentification/inscription");
         }
         else{
            throw new Exception('Page introuvable');
         }
      

         
      }

  
   }



   private function maCommande(){
      $CommandeManager = new CommandeManager();
      $maCommande = $CommandeManager->getCmdActiveClient();
       
      return $maCommande ;
   }

   private function payerPanierActif(){

      $CommandeManager = new CommandeManager;
      $numCmdPaye = $this->maCommande()->num();
      $CommandeManager->payerPanierActif($GLOBALS["client_en_ligne"]->getId());

      header("Location: ".URL_SITE."facture/".$numCmdPaye."&envoyerFactureMail=Ok");


   }

   private function peutProcederPaye(){
      //Si au moins un article, si la commande n'a pas encore été payé et si on est connecté
 
      return 
      $this->maCommande()->panier() != null 
      && $this->maCommande()->Etat() != null 
      && $this->maCommande()->Etat()->id() == 1 
      && $GLOBALS["client_en_ligne"] != null;
      
   }
   



}

?>