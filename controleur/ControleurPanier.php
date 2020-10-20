<?php 
require_once('vue/Vue.php');

class ControleurPanier{
   private $vue;
   private $ArticleManager;
   private $CommandeManager;

   

   // CONSTRUCTEUR 
   public function __construct($url){
      
   if( isset($url) && count($url) > 1 ){
      throw new Exception('Page introuvable', 404);
   }
   else{

      //Initialisation des Managers
      $this->ArticleManager = new ArticleManager;
      $this->CommandeManager = new CommandeManager;


      
      if( isset($_POST["ajouterArticle"]) ){
         $this->ajouterArticle();
      }
      else if(isset($_POST["deleteArticle"])){
         $this->supprimerArticle();
      }
      else  if(isset($_POST["diminuerQte"])) {
         $this->diminuerArticle();
      }
      else  if(isset($_POST["viderPanier"])) {
         $this->viderPanierActif();
      }


      $this->vue = new Vue('Panier') ;
      $this->vue->setListeJsScript(["public/script/js/HtmlArticle.js","public/script/js/HtmlPanier.js" ]);
      $donneeVue = array(
         "cmdActif"=> $this->maCommandeActif()
      ) ;

      $this->vue->genererVue($donneeVue) ;
      

      

      
      
   }
   }


   private function ajouterArticle(){

        
         //Panier Session (Hors ligne)
         if( $GLOBALS["client_en_ligne"] == null ){

               $ArticleSession = new ArticleSession($_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["numClr"]);
               
               if( $ArticleSession->dispo() ){
                  $this->maCommandeActif()->ajouterPanier($ArticleSession);
               }
               
               
         }
         //Panier BDD (Connecté)
         else{

            if(  $this->CommandeManager->possedeCommandeNonPayer( $GLOBALS["client_en_ligne"]->getId() ) == false ){
               $numCmd = $this->CommandeManager->insertCommande( $GLOBALS["client_en_ligne"]->getId() );
            }
            $numCmd = $this->maCommandeActif()->num();
            $this->ArticleManager->inserer( $numCmd,  $_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["numClr"] );
            
         }

      //Json encode
      $indiceNouvelArt = $this->maCommandeActif()->indiceArticlePanier($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      $nouvelArticle = $this->maCommandeActif()->panier()[$indiceNouvelArt];

      $jsonData = array(
         'totalQtePanier' => $this->maCommandeActif()->getQuantiteArticle(),
         "newPrixArt" => $nouvelArticle->prixTotalArt(),
         "prixCmdHT" => $this->maCommandeActif()->prixHT(),
         "prixCmdTTC" => $this->maCommandeActif()->prixTTC()
      );
      echo json_encode($jsonData);
      exit();   

   }



   private function supprimerArticle(){
      $numCmd = $this->maCommandeActif()->num() ;

      //Panier Session (Hors ligne)
      if( $GLOBALS["client_en_ligne"] == null ){
         $this->maCommandeActif()->supprimerArticle($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      }
      //Panier BDD (Connecté)
      else{
         $this->ArticleManager->supprimer($numCmd, $_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      }

      $jsonData = array(
         'totalQtePanier' => $this->maCommandeActif()->getQuantiteArticle(),
         "prixCmdHT" => $this->maCommandeActif()->prixHT()
      );
      echo json_encode($jsonData);
      exit();   
 
      
   }

   private function diminuerArticle(){
         //Panier Session (Hors ligne)
         if( $GLOBALS["client_en_ligne"] == null ){
            $this->maCommandeActif()->diminuerArticle($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }
         //Panier BDD (Connecté)
         else{
           $numCmd = $this->maCommandeActif()->num() ;
           $this->ArticleManager->diminuerQte($numCmd, $_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }

      //Json encode
      $indiceArticleModifier = $this->maCommandeActif()->indiceArticlePanier($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      $ArticleModifier = $this->maCommandeActif()->panier()[$indiceArticleModifier];


      $jsonData = array(
         'totalQtePanier' => $this->maCommandeActif()->getQuantiteArticle(),
         "newPrixArt" => $ArticleModifier->prixTotalArt(),
         "prixCmdHT" => $this->maCommandeActif()->prixHT(),
         "prixCmdTTC" => $this->maCommandeActif()->prixTTC()
      );
      
      echo json_encode($jsonData);
      exit();   

      
   }

 


   private function maCommandeActif(){
      return $this->CommandeManager->getCmdActiveClient();
   }


   private function viderPanierActif(){
      if( $GLOBALS["client_en_ligne"] == null ){
         $this->maCommandeActif()->viderPanier();
      }
      else{
         $this->CommandeManager->viderPanier( $this->maCommandeActif()->num() );
      }
      
   }






}

?>