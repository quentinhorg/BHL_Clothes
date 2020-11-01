<?php 
require_once('vue/Vue.php');

class ControleurPaiement{
   private $vue;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      
   
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404);
      }
      else if ( isset($url[1]) && $url[1] == "panier" ){
         
         if ($this->verifPanierDispo() == false) {
            header("Location: ".URL_SITE."panier?panierPasDispo=ok");
            exit();
         }
        
        if( $this->peutProcederPayePanier() ){

            //Payer le panier si envoi du formulaire
            if(  isset($_POST["payerCmd"]) ){
               $this->payerPanierActif();
            }
            
           

               $this->vue = new Vue('Paiement') ; 
               $donneeVue = array( 
                  "clientInfo"=> $GLOBALS["user_en_ligne"], 
                  "maCommande"=> $this->maCommande() 
               ) ;
               $this->vue->genererVue($donneeVue) ;
               
        
            
            
           
        }
         else if ($GLOBALS["user_en_ligne"] == null && COUNT($this->maCommande()->panier()) >= 1 ) {
            header("Location: ".URL_SITE."/authentification/inscription");
         }
         else{
            throw new Exception("Vous ne pouvez pas procédez au paiement.", 403);
         }
         
      }
      else if ( isset($url[1]) && $url[1] == "rechargerSolde" ){
         //AJOUTER UNE PROCEDURE POUR RECHARGER SON SOLDE CLIENT
      }
      else{
         throw new Exception("L'objet du paiement n'a pas été précisé dans la requête", 400);
      }

  
   }



   private function maCommande(){
      $CommandeManager = new CommandeManager();
      $maCommande = $CommandeManager->getCmdActiveClient();
       
      return $maCommande ;
   }

   private function payerPanierActif(){

      
      try {

         $CommandeManager = new CommandeManager;
         $numCmdPaye = $this->maCommande()->num();
         
         $CommandeManager->payerPanierActif($GLOBALS["user_en_ligne"]->id());
         header("Location: ".URL_SITE."facture/".$numCmdPaye."&envoyerFactureMail=Ok");

      } catch (Exception $e) {
         
      }


   }
   
   // Retourne vrai si au moins un article du panier est dispo sinon faux 
   private function verifPanierDispo(){

      $dispo= true;
      foreach ($this->maCommande()->panier() as $article) {
         if ( $article->dispo() == false ) {
            $dispo= false;
         }
      }
      return $dispo;

   }

   private function peutProcederPayePanier(){
      //Si au moins un article, si la commande n'a pas encore été payé et si on est connecté
      return 
      $this->maCommande()->panier() != null //Possède un panier
      && $this->maCommande()->Etat() != null //Possède un État
      && $this->maCommande()->Etat()->id() == 1 //La commande n'a pas encore été payé
      && COUNT($this->maCommande()->panier()) >= 1 // Au moins un article
      && $GLOBALS["user_en_ligne"] != null; //Un client est en ligne
      
   }
   



}

?>