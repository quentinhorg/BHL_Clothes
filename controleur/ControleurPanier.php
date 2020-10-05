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
           
            $this->vue = new Vue('Paiement') ;
            $donneeVue = array(
               "clientInfo"=> $this->client(),
               "maCommande"=> $this->maCommande()
            ) ;

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

      if( isset($_POST["ajouterArticle"]) ){
         $ArticleManager = new ArticleManager();
         
         if( isset($_SESSION["ma_commande"])){
           $this->maCommande()->ajouterPanier($_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["couleur"]);
         }
         else{
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

   public function client(){
      $ClientManageur = new ClientManager();
      $clientCmd= $ClientManageur->ClientEnLigne();
      return $clientCmd;
  }

   private function suppSession(){
      $CommandeManager = new CommandeManager();
      $CommandeManager->effacerCmdSession();
   }


}

?>