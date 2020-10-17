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

        
      
         // //Action du panier
         // $ArticleManager = new ArticleManager();
         // $ArticleSession = new ArticleSession(1, "S", 3, 1);
         // $this->maCommande()->ajouterPanier($ArticleSession);

      
        
         if( isset($_POST["ajouterArticle"]) ){
            $this->ajouterArticle();
         }
         else if(isset($_POST["deleteArticle"])){
            $this->supprimerArticle();
         }
         else  if(isset($_POST["diminuerQte"])) {
            $this->diminuerArticle();
     
         }
   

         $this->vue = new Vue('Panier') ;
         $this->vue->setListeJsScript(["public/script/js/HtmlArticle.js","public/script/js/HtmlPanier.js" ]);
         $donneeVue = array(
            "maCommande"=> $this->maCommande()
         ) ;
      

         $this->vue->genererVue($donneeVue) ;
        

         

         
         
      }
   }

   private function ajouterArticle(){

        
      $ArticleManager = new ArticleManager();
         if( $GLOBALS["client_en_ligne"] == null ){

               $ArticleSession = new ArticleSession($_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["numClr"]);
               $this->maCommande()->ajouterPanier($ArticleSession);
         }
         else{
            
            $CommandeManager = new CommandeManager;
            if(  $CommandeManager->possedeCommandeNonPayer( $GLOBALS["client_en_ligne"]->getId() ) == false ){
               $numCmd = $CommandeManager->insertCommande( $GLOBALS["client_en_ligne"]->getId() );
            }
            $numCmd = $this->maCommande()->num();
            $ArticleManager->inserer( $numCmd,  $_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["numClr"] );
            
         }

      //Json encode
      $indiceNouvelArt = $this->maCommande()->indiceArticlePanier($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      $nouvelArticle = $this->maCommande()->panier()[$indiceNouvelArt];

      $jsonData = array(
         'totalQtePanier' => $this->maCommande()->getQuantiteArticle(),
         "newPrixArt" => $nouvelArticle->prixTotalArt(),
         "prixCmdHT" => $this->maCommande()->prixHT(),
         "prixCmdTTC" => $this->maCommande()->prixTTC()
      );
      echo json_encode($jsonData);
      exit();   

   }



   private function supprimerArticle(){

    
         if( $GLOBALS["client_en_ligne"] != null ){
            $ArticleManager = new ArticleManager();
            $numCmd = $this->maCommande()->num() ;
            
            $ArticleManager->supprimer($numCmd, $_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }
         else{
            $this->maCommande()->supprimerArticle($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }

         $jsonData = array(
            'totalQtePanier' => $this->maCommande()->getQuantiteArticle(),
            "prixCmdHT" => $this->maCommande()->prixHT()
         );
         echo json_encode($jsonData);
         exit();   
 
      
   }

   private function diminuerArticle(){
     
         if( $GLOBALS["client_en_ligne"] != null ){
            $ArticleManager = new ArticleManager();
            $numCmd = $this->maCommande()->num() ;
            
            $ArticleManager->diminuerQte($numCmd, $_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }
         else{
           $this->maCommande()->diminuerArticle($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }

      //Json encode
      $indiceArticleModifier = $this->maCommande()->indiceArticlePanier($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      $ArticleModifier = $this->maCommande()->panier()[$indiceArticleModifier];


      $jsonData = array(
         'totalQtePanier' => $this->maCommande()->getQuantiteArticle(),
         "newPrixArt" => $ArticleModifier->prixTotalArt(),
         "prixCmdHT" => $this->maCommande()->prixHT(),
         "prixCmdTTC" => $this->maCommande()->prixTTC()
      );
      
      echo json_encode($jsonData);
      exit();   

      
   }

 


   private function maCommande(){
      $CommandeManager = new CommandeManager();
      $maCommande = $CommandeManager->getCmdActiveClient();
       
      return $maCommande ;
   }



   private function suppSession(){
      $CommandeManager = new CommandeManager();
      $CommandeManager->effacerCmdSession();
   }






}

?>