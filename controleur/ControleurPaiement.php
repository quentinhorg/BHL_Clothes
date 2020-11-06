<?php 
require_once('vue/Vue.php');

class ControleurPaiement{
   private $vue;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){

      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else if ( isset($url[1]) && $url[1] == "panier" ){
         
         if($GLOBALS["user_en_ligne"] != null ){ //Si l'utilisateur est connecté
            if(  $this->cmdActif()->panier() != null && COUNT($this->cmdActif()->panier()) >= 1){ // Au moins un article
               if( $this->cmdActif()->Etat() != null && $this->cmdActif()->Etat()->id() == 1 ){ //La commande n'a pas encore été payé
                  if($this->verifPanierDispo() ){ //Si ne possède pas un article non dispo

                     /*---------FORMULAIRE---------*/
                     if(  isset($_POST["payerCmd"]) ){
                        $this->payerPanierActif();
                     }
                     /* ------------------ */
                     
                     /*---------VUE---------*/
                     $this->vue = new Vue('Paiement') ; 
                     $donneeVue = array( 
                        "clientInfo"=> $GLOBALS["user_en_ligne"], 
                        "maCommande"=> $this->cmdActif() 
                     ) ;
                     $this->vue->genererVue($donneeVue) ;  
                     /*------------------*/
                     
                  } else{ $this->message = "" ;}
               } else{ header("Location: ".URL_SITE."panier?panierPasDispo=ok"); exit(); } // Redirection vers le panier (Possède au moins un article non dispo)
            } else{ throw new Exception("Vous ne pouvez pas procédez au paiement.", 403); }
         } else if(COUNT($this->cmdActif()->panier()) >= 1 ){header("Location: ".URL_SITE."/authentification/inscription"); } //Redirection vers l'inscription
         else{ throw new Exception(null, 401);  } 
      } else{ throw new Exception("L'objet du paiement n'a pas été précisé dans la requête", 400); }

  
   }


   //
   private function cmdActif(){
      $CommandeManager = new CommandeManager();
      $maCommande = $CommandeManager->getCmdActiveClient();
       
      return $maCommande ;
   }

   private function payerPanierActif(){

      try {

         $CommandeManager = new CommandeManager;
         $numCmdPaye = $this->cmdActif()->num();
         
         $CommandeManager->payerPanierActif($GLOBALS["user_en_ligne"]->id());
         header("Location: ".URL_SITE."facture/".$numCmdPaye."&envoyerFactureMail=Ok");

      } catch (Exception $e) {
         
      }


   }
   
   // Retourne vrai si au moins un article du panier est dispo sinon faux 
   private function verifPanierDispo(){
      $dispo= true;
      foreach ($this->cmdActif()->panier() as $article) {
         if ( $article->dispo() == false ) {
            $dispo= false;
         }
      }
      return $dispo;

   }

   



}

?>