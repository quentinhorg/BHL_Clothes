<?php 
require_once('vue/Vue.php');

class ControleurPanier{
   private $vue;
   private $ArticleManager;
   private $CommandeManager;
   public $message;
   

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 1 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{
      
         //Initialisation des Managers
         $this->ArticleManager = new ArticleManager;
         $this->CommandeManager = new CommandeManager;
         

         if ( isset($_GET['panierPasDispo']) && $_GET['panierPasDispo'] == "ok") {
            $this->message= "Veuillez supprimer les articles non disponibles avant de payer.";
         }
         //Ajouter un article dans le panier
         if( isset($_POST["ajouterArticle"]) ){
            $this->ajouterArticle();
         }
         //Supprimer un article (toutes les qte y compris)
         else if(isset($_POST["deleteArticle"])){  
            $this->supprimerArticle();
         }
         //Diminuer un article (si superieur à 1)
         else  if(isset($_POST["diminuerQte"])) {
            $this->diminuerArticle();
         }
         //Vider tous les articles du panier
         else  if(isset($_POST["viderPanier"])) {
            $this->viderPanierActif();
         }

         //Génération de la vue
         $this->vue = new Vue('Panier') ;
         $this->vue->Popup->setMessage($this->message); //Initialisation du message dans 
         $this->vue->setListeJsScript(["public/script/js/HtmlArticle.js","public/script/js/HtmlPanier.js" ]); // Ajout des crsipts suppmlémentaire
         $donneeVue = array(
            "cmdActif"=> $this->maCommandeActif()
         ) ;
         $this->vue->genererVue($donneeVue) ;
      }
   }

   //Ajouter un article
   private function ajouterArticle(){
         //Panier Session (Hors ligne)
         if( $GLOBALS["user_en_ligne"] == null ){
               $ArticleSession = new ArticleSession($_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["numClr"]);
               if( $ArticleSession->dispo() ){
                  $this->maCommandeActif()->ajouterPanier($ArticleSession);
               }
         }
         //Panier BDD (Connecté)
         else{
            if(  $this->CommandeManager->possedeCommandeNonPayer( $GLOBALS["user_en_ligne"]->id() ) == false ){
               $numCmd = $this->CommandeManager->insertCommande( $GLOBALS["user_en_ligne"]->id() );
            }
            $numCmd = $this->maCommandeActif()->num();
            $this->ArticleManager->inserer( $numCmd,  $_POST["idVet"], $_POST["taille"], $_POST["qte"], $_POST["numClr"] );
         }
      
      /*---------- Vue Json ----------*/
      $indiceNouvelArt = $this->maCommandeActif()->indiceArticlePanier($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      $nouvelArticle = $this->maCommandeActif()->panier()[$indiceNouvelArt];
      $jsonData = array(
         'totalQtePanier' => $this->maCommandeActif()->totalArticle(),
         "newPrixArt" => $nouvelArticle->prixTotalArt(),
         "prixCmdHT" => $this->maCommandeActif()->prixHT(),
         "prixCmdTTC" => $this->maCommandeActif()->prixTTC()
      );
      echo json_encode($jsonData);
      exit();   
   }


   //Supprimer un article
   private function supprimerArticle(){
      $numCmd = $this->maCommandeActif()->num();
      //Panier Session (Hors ligne)
      if( $GLOBALS["user_en_ligne"] == null ){
         $this->maCommandeActif()->supprimerArticle($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      }
      //Panier BDD (Connecté)
      else{
         $this->ArticleManager->supprimer($numCmd, $_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      }

      /*---------- Vue Json ----------*/
      $jsonData = array(
         'totalQtePanier' => $this->maCommandeActif()->totalArticle(),
         "prixCmdHT" => $this->maCommandeActif()->prixHT()
      );
      echo json_encode($jsonData);
      exit();   
 
      
   }

   //Diminuer un article
   private function diminuerArticle(){
         //Panier Session (Hors ligne)
         if( $GLOBALS["user_en_ligne"] == null ){
            $this->maCommandeActif()->diminuerArticle($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }
         //Panier BDD (Connecté)
         else{
           $numCmd = $this->maCommandeActif()->num() ;
           $this->ArticleManager->diminuerQte($numCmd, $_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
         }

      /*---------- Vue Json ----------*/
      $indiceArticleModifier = $this->maCommandeActif()->indiceArticlePanier($_POST["idVet"], $_POST["taille"], $_POST["numClr"]);
      $ArticleModifier = $this->maCommandeActif()->panier()[$indiceArticleModifier];
      $jsonData = array(
         'totalQtePanier' => $this->maCommandeActif()->totalArticle(),
         "newPrixArt" => $ArticleModifier->prixTotalArt(),
         "prixCmdHT" => $this->maCommandeActif()->prixHT(),
         "prixCmdTTC" => $this->maCommandeActif()->prixTTC()
      );
      
      echo json_encode($jsonData);
      exit();   
   }

 

   //Obtention de la commande actif (Commande session / connecté)
   private function maCommandeActif(){
      return $this->CommandeManager->getCmdActiveClient();
   }

   //Vidage du panier en cours
   private function viderPanierActif(){
      //Panier Session (Hors ligne)
      if( $GLOBALS["user_en_ligne"] == null ){
         $this->maCommandeActif()->viderPanier();
      }
       //Panier BDD (Connecté)
      else{
         $this->CommandeManager->viderPanier( $this->maCommandeActif()->num() );
      }
      
   }






}

?>