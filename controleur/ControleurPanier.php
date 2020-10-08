<?php 
require_once('vue/Vue.php');

class ControleurPanier{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
    $id=1;
      if( isset($url) && count($url) > 2 ){
         throw new Exception('Page introuvable');
      }
      else{
      
      
         
        
         $this->ajouterArticle();
         
        
         if( isset($url[1]) && strtolower($url[1]) == "paiement"){

            $this->payerPanierActif();

            
            if( $this->peutPayer() ){

               $this->vue = new Vue('Paiement') ;
               $donneeVue = array(
                  "clientInfo"=> $GLOBALS["client_en_ligne"],
                  "maCommande"=> $this->maCommande()
               ) ;

            }
            else{
               throw new Exception('Page introuavable');
            }
            

         }
         else{
            $this->vue = new Vue('Panier') ;
            $this->vue->setListeJsScript(["public/script/js/HtmlArticle.js","public/script/js/HtmlPanier.js" ]);
            $donneeVue = array(
               "maCommande"=> $this->maCommande()
           ) ;
         }

         $this->vue->genererVue($donneeVue) ;
        

         

         
         
      }
   }

   private function ajouterArticle(){

   //  var_dump($GLOBALS["client_en_ligne"]);



      if( isset($_POST["ajouterArticle"]) ){
         $ArticleManager = new ArticleManager();
         
         if( isset($_SESSION["ma_commande"])){
            $this->maCommande()->ajouterPanier($_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["couleur"]);
         }
         else{
            $CommandeManager = new CommandeManager;
            if(  $CommandeManager->possedeCommandeNonPayer( $GLOBALS["client_en_ligne"]->getId() ) == false ){
               $numCmd = $CommandeManager->insertCommande( $GLOBALS["client_en_ligne"]->getId() );
            }

            $numCmd = $this->maCommande()->num();
           
            $ArticleManager->inserer($numCmd,  $_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["couleur"] );
         }

      }
   }


   private function maCommande(){
      $CommandeManager = new CommandeManager();
      //Si le client est connecté
         $maCommande = $CommandeManager->getCmdActiveClient();
       
     
      return $maCommande ;
   }



   private function suppSession(){
      $CommandeManager = new CommandeManager();
      $CommandeManager->effacerCmdSession();
   }

   private function payerPanierActif(){
      if( $this->peutPayer() && isset($_POST["payerCmd"]) ){
         
         $CommandeManager = new CommandeManager;
         $CommandeManager->payerPanierActif($GLOBALS["client_en_ligne"]->getId());
         

      }
   }

   private function peutPayer(){
      //Si au moins un article, si la commande n'a pas encore été payé et si on est connecté
      return $this->maCommande()->panier() != null && $this->maCommande()->Etat()->id() == 1 && $GLOBALS["client_en_ligne"] != null;
      
   }


}

?>