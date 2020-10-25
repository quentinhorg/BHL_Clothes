<?php 



class ControleurAdminCommande{
   private $vue;
   private $CommandeManager ;
   private $EtatManager ;
   private $FactureManager ;
   public $message;

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
      

            if( isset($_POST["modifierEtatCmd"]) ){
               $this->modifierEtat($url[2], $_POST["etat"]);
            }
            else if( isset($_POST["supprimerFacture"]) ){
               $this->supprimerFacture($url[2]);
            }
            else if( isset($_POST["supprimerCommande"]) ){
               $this->supprimerCommande($url[2]);
            }
            
         
            //Modifier une commande
            if( isset($url[2]) && $this->message != "La commande à bien été supprimé." ){

               $vue = "AdminCommandeModifier" ;
               $donnee = array( 
               "listEtat" => $this->listEtat(),
               "commande" => $this->commandeInfo($url[2])
               );
            }

            //Listing des commandes 
            else{
               $vue = "AdminCommande" ;
               $donnee = array( 
                  "commandeList" =>  $this->listCommande()
               );
            }
            
         

         $this->vue = new VueAdmin($vue) ;
         $this->vue->Popup->setMessage($this->message);
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
         $this->message= "L'etat à bien été modifié";
  
      } catch (Exception $e) {
     
         $this->message = $e->getMessage() ;
      
      }

 
   }

   private function supprimerFacture($numCmd){

      try{

         $this->FactureManager->supprimerFacture($numCmd);
         $this->message= "La facture à bien été supprimé.";
  
      } catch (Exception $e) {
     
         $this->message = $e->getMessage() ;
         
      
      }

 
   }

   private function supprimerCommande($numCmd){

      try{
         //Apres le vide du panier, la commande se supprime automatiquement  
         $this->CommandeManager->viderPanier($numCmd);
         $this->message= "La commande à bien été supprimé.";
  
      } catch (Exception $e) {

         if($e->getCode() == 45000 ){
            $this->message = $e->getMessage() ;
         }
         else{
            $this->message = "<b>Erreur lors de la suppression </b> <br>".$e->getMessage();
         }
        
      }

   }

  

}

?>