<?php 



class ControleurAdminCommande{
   private $vue;
   private $CommandeManager ;
   private $EtatManager ;
   private $FactureManager ;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){
     
      if( isset($url) && count($url) > 4 ){
       
         throw new Exception(null, 404); //Erreur 404
      }
      else{
            //Initialisatio ndes managers 
            $this->CommandeManager = new CommandeManager;
            $this->EtatManager = new EtatManager;
            $this->FactureManager = new FactureManager;
            $this->ArticleManager = new ArticleManager;
      
            //Etat d'une commande
            if( isset($_POST["modifierEtatCmd"]) ){
               $this->modifierEtat($url[2], $_POST["etat"]);
            }

            //Facture
            if( isset($_POST["supprimerFacture"]) ){
               $this->supprimerFacture($url[2]);
            }
            else if( isset($_POST["supprimerCommande"]) ){
               $this->supprimerCommande($url[2]);
            }

            //Article
            if(isset($_POST["supprimerArticle"])){
             
               $this->supprimerArticle($url[2], $_POST["supprimerArticle"], $_POST["tailleArt"], $_POST["numClrArt"]);
            }
            else if(isset($_POST["modifierArticle"])){
               $this->modifierArticle($url[2], $_POST["modifierArticle"], $_POST["tailleArt"], $_POST["numClrArt"], $_POST["qteArt"], $_POST["ancien"]);
            }
            
            
            //Modifier une commande
            if( isset($url[3]) && $url[3] == "facture" ){
               $facture = $this->commandeInfo($url[2])->getFacture() ;
               $client = $this->commandeInfo($url[2])->Client() ;
               
               include "vue/vueFacture.php";
               $pdf->buildPDF();
               $pdf->Output();
               exit();
            }
            else if( isset($url[2]) && $this->message != "La commande à bien été supprimé." ){

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
   
   //Commande
   private function listCommande(){
      return $this->CommandeManager->getListCommande();
   }
   private function commandeInfo($num){
      return $this->CommandeManager->getCommande($num);
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

   //Etat
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

   //Facture
   private function supprimerFacture($numCmd){
      try{

         $this->FactureManager->supprimerFacture($numCmd);
         $this->message= "La facture à bien été supprimé.";
  
      } catch (Exception $e) {
     
         $this->message = $e->getMessage() ;
         
      
      }

 
   }

   //Article
   private function supprimerArticle($numCmd, $idVet, $tailleArt, $numClrArt){
      try{

         $this->ArticleManager->supprimer($numCmd, $idVet, $tailleArt, $numClrArt);
         $this->message= "L'article à bien été supprimé.";
  
      } catch (Exception $e) {
         
         if($e->getCode() == 45000){
            $this->message = $e->getMessage();
         }
         else{
            $this->message = "<b> Erreur lors de la suppression de l'article </b> <br>";
         }
      }

 
   }

   private function modifierArticle($numCmd, $idVet, $tailleArt, $numClrArt, $qte, $ancienValue){

      try{

         $this->ArticleManager->updateBDD($numCmd, $idVet, $tailleArt, $numClrArt, $qte, $ancienValue);
         $this->message= "L'article à bien été modifié.";
  
      } catch (Exception $e) {
         if($e->getCode() == 45000){
            $this->message = $e->getMessage();
         }
         else{
            $this->message = "<b> Erreur lors de la modification de l'article </b> <br>";
         }
      }

 
   }



  

}

?>