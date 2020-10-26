<?php 



class ControleurAdminCommande{
   private $vue;
   private $CommandeManager ;
   private $ClientManager ;
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
            $this->ClientManager = new ClientManager;
            $this->FactureManager = new FactureManager;
      

            if( isset($_POST["modifierClientCmd"]) ){
               $this->modifierClient($url[2], $_POST["Client"]);
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
               "listClient" => $this->listClient(),
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

   private function listClient(){
      return $this->ClientManager->getListClient();
   }

   private function modifierClient($numCmd, $idClient){

      try{

         $this->CommandeManager->modifierClient($numCmd, $idClient);
         $this->message= "L'Client à bien été modifié";
  
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