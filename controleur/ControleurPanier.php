<?php 
require_once('vue/Vue.php');

class ControleurPanier{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
    $id=1;
      if( isset($url) && count($url) > 1 ){
         throw new Exception('Page introuvable');
      }
      else{
         //$this->suppSession(); 
        
         
        
            $this->ajouterArticle();

         

        

         $this->vue = new Vue('Panier') ;
       
         $this->vue->setListeJsScript(["public/script/js/HtmlArticle.js","public/script/js/HtmlPanier.js" ]);
         $this->vue->genererVue(array( 
            "maCommande"=> $this->maCommande()
         )) ;

         
         
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

   private function suppSession(){
      $CommandeManager = new CommandeManager();
      $CommandeManager->effacerCmdSession();
   }


}

?>