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
         $numCmd = $this->maCommande()->num();
   
         
        if( isset($_POST["ajouterArticle"]) ){
    
            $this->ajouterArticle($numCmd, $_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["couleur"]);
         }
         

        

         $this->vue = new Vue('Panier') ;
       
         $this->vue->setListeJsScript(["public/script/js/HtmlArticle.js","public/script/js/HtmlPanier.js" ]);
         $this->vue->genererVue(array( 
            "maCommande"=> $this->maCommande()
         )) ;

         
         
      }
   }

   private function ajouterArticle($idCmd, $idVet, $taille, $qte, $idClr){
      $ArticleManager = new ArticleManager();
      $ArticleManager->inserer($idCmd, $idVet, $taille, $qte, $idClr);
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