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

         $numCommande = null;
         if( isset($_POST["ajouterArticle"]) ){
            $this->ajouterArticle(null, $_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["couleur"]);
         }
         

         //$this->suppSession(); 

         $this->vue = new Vue('Panier') ;
         $this->vue->setListeJsScript(["publique/script/js/HtmlArticle.js","publique/script/js/HtmlPanier.js" ]);
         $this->vue->genererVue(array( 
            "maCommande"=> $this->maCommande($numCommande)
         )) ;

         
         
      }
   }

   private function ajouterArticle($idCmd, $idVet, $idTaille, $qte, $idClr){
      $ArticleManager = new ArticleManager();
      $ArticleManager->inserer($idCmd, $idVet, $idTaille, $qte, $idClr);
   }


   private function maCommande($numCommande){
      $CommandeManager = new CommandeManager();

      //Si le client est connecté
      if( isset($clientConnecte) ){
         $this->suppSession();
         $maCommande = $CommandeManager->getCommande($numCommande);
      }else{
         $maCommande = $CommandeManager->getCommande(null);
      }
     
      return $maCommande ;
   }

   private function suppSession(){
      $CommandeManager = new CommandeManager();
      $CommandeManager->renitialiseSession();
   }


}

?>