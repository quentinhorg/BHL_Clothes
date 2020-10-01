<?php 
require_once('vue/Vue.php');

class ControleurAuthentification{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception('Page introuvable');
      }
      //Inscription
      else if(@strtolower($url[1]) == "inscription"){
         $message=null;
         if (isset($_POST['submit'])){
            if (!empty($_POST['nom'])) {
               if (!empty($_POST['prenom'])) {
                  if (!empty($_POST['adresse'])) {
                     if (!empty($_POST['email'])) {
                        if (!empty($_POST["mdp"])){
                           if (!empty($_POST['tel'])) {
                              $idClientRegister = $this->insertClient();

                              if( $_SESSION["ma_commande"]->panier() != NULL ){
                                 $this->addPanierSessionToBdd($idClientRegister, $_SESSION["ma_commande"]);

                                 $mail= $_POST['email'];
                                 $mdp = $_POST['mdp'];
                                 $this->connexionClient($mail, $mdp);
                              }
                              
                           }else {  $message = "Veuillez entrer votre numéro de téléphone"; }
                        }else {  $message = "Veuillez entrer un mot de passe"; }
                     }else {  $message = "Veuillez entrer votre Email"; }
                  }else {  $message = "Veuillez entrer votre adresse"; }
               }else {  $message = "Veuillez entrer un Prénom"; }
            }else {  $message = "Veuillez entrer un nom";   }
         }
         $this->vue = new Vue('Inscription') ;
         $this->vue->genererVue(array("message"=>$message)) ;
      }
      //Connexion
      else if( @strtolower($url[1])=="connexion" || !isset($url[1])|| empty($url[1])){
         $message=null;
         if (isset($_POST['submit'])){
            if (!empty($_POST['email'])){
               if (!empty($_POST['mdp'])){

                  $mail= $_POST['email'];
                  $mdp = $_POST['mdp'];
                  $this->connexionClient($mail, $mdp);
                  
               }else {  $message = "Veuillez entrer un mot de passe"; }
            }else {  $message = "Veuillez entrer votre Email"; }
         }
         $this->vue = new Vue('Connexion') ;
         $this->vue->genererVue(array("message"=>$message)) ;
      }
   }

   //retourne les 3 derniers vetements
   private function insertClient(){
      $ClientManager = new ClientManager();
      return $ClientManager->insertBDD();
   }

   private function addPanierSessionToBdd($idCli, $cmdObj){
      $CommandeManager = new CommandeManager();
      $ArticleManager = new ArticleManager();

      //Remplacer par un trigger 
      $idCmd = $CommandeManager->insertCommande($idCli);

      $ArticleManager->insertListeArticle($idCmd, $cmdObj->panier());
      $CommandeManager->effacerCmdSession();

   }

   private function connexionClient($mail, $mdp){
      $ClientManager = new ClientManager();
      $ClientManager->connexion($mail, $mdp);
   }


}


?>
