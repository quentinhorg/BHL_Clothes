<?php 



class ControleurAdminCommande{
   private $vue;
   private $CommandeManager ;
   private $EtatManager ;
   private $FactureManager ;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception(null, 404);
      }
      else{
         //Initialisatio ndes managers 
         $this->CommandeManager = new CommandeManager;
         $this->EtatManager = new EtatManager;
         $this->FactureManager = new FactureManager;

         $popup = null;

         if( isset($_POST["modifierEtatCmd"]) ){
            $popup[0] = "Commande Numéro ".$url[2] ;
            $popup[1] = $this->modifierEtat($url[2], $_POST["etat"]);
         }
         else if( isset($_POST["supprimerFacture"]) ){
            $popup[0] = "Commande Numéro ".$url[2] ;
            $popup[1] = $this->supprimerFacture($url[2]);
         }
         else if( isset($_POST["supprimerCommande"]) ){
            $popup[0] = "Commande Numéro ".$url[2] ;
            $popup[1] = $this->supprimerCommande($url[2]);
         }
         
        
         //Modifier une commande
         if( isset($url[2]) ){

            $vue = "AdminCommandeModifier" ;
            $donnee = array( 
              "listEtat" => $this->listEtat(),
              "commande" => $this->commandeInfo($url[2]),
              "popup" => $popup
            );
         }

         //Listing des commandes 
         else{
            $vue = "AdminCommande" ;
            $donnee = array( 
               "commandeList" =>  $this->listCommande(),
               "popup" => $popup
              
            );
         }
         
         

         $this->vue = new VueAdmin($vue) ;
         $this->vue->genererVue($donnee) ;
      }
   }
   
   private function listCommande(){
      return $this->CommandeManager->getListCommande();
   }
   private function commandeInfo($num){
      return $this->CommandeManager->getCommande($num);
   }

   private function listEtat(){
      return $this->EtatManager->getListEtat();
   }

   private function modifierEtat($numCmd, $idEtat){

      try{

         $this->CommandeManager->modifierEtat($numCmd, $idEtat);
         $message= "L'etat à bien était modifié";
  
      } catch (Exception $e) {
     
         $message = $e->getMessage() ;
      
      }

      return $message ;
   }

   private function supprimerFacture($numCmd){

      try{

         $this->FactureManager->supprimerFacture($numCmd);
         $message= "La facture à bien été supprimé.";
  
      } catch (Exception $e) {
     
         $message = $e->getMessage() ;
         
      
      }

      return $message ;
   }

   private function supprimerCommande($numCmd){

      try{
         //Apres le vide du panier, la commande se supprime automatiquement  
         $this->CommandeManager->viderPanier($numCmd);
         $message= "La commande à bien été supprimé.";
  
      } catch (Exception $e) {

         if($e->getCode() == 45000 ){
            $message = $e->getMessage() ;
         }
         else{
            $message = "<b>Erreur lors de la suppression </b> <br>".$e->getMessage();
         }
        
      }

      return $message ;
   }

  

}

?>