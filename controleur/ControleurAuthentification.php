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
                                 $this->insertPanierSessionToBdd($idClientRegister, $_SESSION["ma_commande"]);
                              }

                              $mail= $_POST['email'];
                              $mdp = $_POST['mdp'];

                              $this->tryConnexion($mail, $mdp) ;
                            
                             
                  
                              
                           }else {  $message = "Veuillez entrer votre numéro de téléphone"; }
                        }else {  $message = "Veuillez entrer un mot de passe"; }
                     }else {  $message = "Veuillez entrer votre Email"; }
                  }else {  $message = "Veuillez entrer votre adresse"; }
               }else {  $message = "Veuillez entrer un Prénom"; }
            }else {  $message = "Veuillez entrer un nom";   }
         }
         $this->vue = new Vue('Inscription') ;
         $this->vue->genererVue(array("message"=>$message, "listCp" => $this->getListCpReunion() )) ;
      }
      //Connexion
      else if( @strtolower($url[1])=="connexion" || !isset($url[1])|| empty($url[1])){
         $message=null;
         if (isset($_POST['submit'])){
            if (!empty($_POST['email'])){
               if (!empty($_POST['mdp'])){

                  $mail= $_POST['email'];
                  $mdp = $_POST['mdp'];
                  $this->tryConnexion($mail, $mdp);
                  
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

   
   private function suppSessionCmd(){
      $CommandeManager = new CommandeManager();
      $CommandeManager->effacerCmdSession();
   }

   private function insertPanierSessionToBdd($idCli,Commande $cmdSessionObj){
      $CommandeManager = new CommandeManager();
      $ArticleManager = new ArticleManager();

      //Remplacer par un trigger 
      $idCmd = $CommandeManager->insertCommande($idCli);

      foreach ($cmdSessionObj->panier() as $Article) {
         $ArticleManager->inserer($idCmd,  $Article->id(), $Article->taille()->libelle(), $Article->qte(), $Article->couleur()->num() );
      }
     
   }

   private function tryConnexion($mail, $mdp){
      $ClientManager = new ClientManager();
      $idClient = $ClientManager->getId($mail, $mdp) ;
    
      if( $idClient >= 1 ){
        
         $_SESSION["id_client_en_ligne"] = $idClient ;
         $GLOBALS["client_en_ligne"] = $ClientManager->getClient($idClient) ;
         $this->suppSessionCmd();
         header("Location: ".URL_SITE."/catalogue");
      }
      else{
         echo "Connexion échouée.";
      }
   
   }

   private function getListCpReunion(){
      $ListCpVilleReunion = array(
         "97400" => 	"Saint-Denis",
         "97410" => 	"Saint-Pierre",
         "97412" => 	"Bras-Panon",
         "97413" => 	"Cilaos",
         "97414" => 	"Entre-Deux",
         "97419" => 	"La Possession",
         "97420" => 	"Le port",
         "97425" => 	"Les Avirons",
         "97426" => 	"Trois-Bassins",
         "97427" => 	"L'Etang-salé",
         "97429" => 	"Petit-Ile",
         "97430" => 	"Tampon",
         "97431" => 	"La Plaine des Palmistes",
         "97433" => 	"Salazie",
         "97436" => 	"Saint-Leu",
         "97438" => 	"Sainte-Marie",
         "97439" => 	"Sainte-Rose",
         "97440" => 	"Saint-André",
         "97441" => 	"Sainte-Suzanne",
         "97442" => 	"Saint-Philippe",
         "97450" => 	"Saint-Louis",
         "97460" => 	"Saint-Paul",
         "97470" => 	"Saint-Benoit",
         "97480" => 	"Saint-Joseph"
      );
   
    return $ListCpVilleReunion ;
   }

}


?>