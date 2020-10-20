<?php 
require_once('vue/Vue.php');

class ControleurAuthentification{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){

     
      
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404);
      }

      else {

         if( $GLOBALS["client_en_ligne"] == null ){

            // Connexion
            if( @strtolower($url[1])=="connexion" || !isset($url[1])|| empty($url[1]) ){
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
      
               $nomVue = "Connexion" ;
               $donnee = array("message"=>$message) ;
            }
            //Inscription
            else if(@strtolower($url[1]) == "inscription"){
               $message=null;
               if (isset($_POST['submit'])){
                  if (!empty($_POST['nom'])) {
                     if (!empty($_POST['prenom'])) {
                        if (!empty($_POST['cp'])) {
                           if (!empty($_POST["rue"])){
                              if (!empty($_POST['email'])) {
                                 if (!empty($_POST["mdp"])){
                                    if (!empty($_POST['tel'])) {
      
                                       $idClientRegister = $this->inscrireClient();
      
                                       if( $_SESSION["ma_commande"]->panier() != NULL ){
                                          $this->insertPanierSessionToBdd($idClientRegister, $_SESSION["ma_commande"]);
                                       }
                                 
                                    }else {  $message = "Veuillez entrer votre numéro de téléphone"; }
                                 }else {  $message = "Veuillez entrer un mot de passe"; }
                              }else {  $message = "Veuillez entrer votre Email"; }
                           }else{ $message = "Veuillez entrer votre rue";} 
                        }else {  $message = "Veuillez entrer votre code postal"; }
                     }else {  $message = "Veuillez entrer un Prénom"; }
                  }else {  $message = "Veuillez entrer un nom";   }
               }
               $nomVue = "Inscription" ;
               $donnee = array("message"=>$message, "listCp" => $this->getListCpReunion()) ;
            }
            //Activation
            else if( @strtolower($url[1]) == "activation" ){

               if( isset($_GET["email"]) && isset($_GET["cle"])  ){
                  $message = $this->tryActiveCompte($_GET["email"], $_GET["cle"]);
                  $nomVue = "Activation" ;
                  $donnee = array("message"=>$message, "listCp" => $this->getListCpReunion()) ;
               }else{ throw new Exception("Manque d'informations pour pouvoir procédé à l'activation du compte.", 400);  }
               
            }else { throw new Exception(null, 404); }
         }
         else{

            if( @strtolower($url[1]) == "deconnexion" && $GLOBALS["client_en_ligne"] != null ){
               $this->deconnexion();
               header("Location: ".URL_SITE);
               exit();
            }
            else{
               throw new Exception(null, 404);
            }
                 
               
         }
        
     

         $this->vue = new Vue($nomVue) ;
         $this->vue->genererVue($donnee) ;
         
        

      }
      
   }

   //retourne les 3 derniers vetements
   private function inscrireClient(){
      $ClientManager = new ClientManager();
      $cleActivation = sha1(rand(1,9000));

      $ClientManager->insertBDD(
         $_POST['email'], 
         $_POST['mdp'], 
         $_POST['nom'], 
         $_POST['prenom'], 
         $_POST['cp'] ,
         $_POST['rue'],
         $_POST['tel'], 
         $cleActivation
      );

      $this->envoyerMailVerifCompte($_POST['email'], $cleActivation);

      return $ClientManager->getId($_POST['email'], $_POST['mdp']);


   }

   private function deconnexion(){
      $ClientManager = new ClientManager();
      $ClientManager->deconnexion();
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
         header("Location: ".URL_SITE."catalogue");
      }
      else{
         echo "Connexion échouée.";
      }
   
   }

   public function tryActiveCompte($mail, $cle){
      $ClientManager = new ClientManager;
      $ClientManager->deconnexion();
      try{
         $ClientManager->tryActiveCompte($mail, $cle) ;
         $message = "<b style='color:#51a251'> Votre compte à bien été activé </b>, vous pouvez maintenant <a href='authentification/connexion'> vous connectez </a>. ";
         var_dump("azaz");
      } catch (Exception $e) {
     
         if($e->getMessage() == "L'email n'existe pas"){
            $message = $mail." n'existe pas, veuillez nous <a href='contact'> contactez </a> si le problème persiste.";
         }
         else if ($e->getMessage() == "La clé d'activation est incorrecte") {
            $message = "La clé d'activation est incorrecte.";
         }
         else if ($e->getMessage() == "Le compte est déjà activé") {
            $message = "Ce compte est déjà activé, veuillez <a href='authentification/connexion'> vous connectez </a>.";
         }
         else{
            $message = "Erreur, votre compte n'a pas pu être activé." ;
        }

      
      }

      return $message;

   }

   private function getListCpReunion(){
     
     $CodePostalManager = new CodePostalManager;
      return $CodePostalManager->getListCp();
   }

   private function envoyerMailVerifCompte($email, $cleActivation){
      

      $baseDirectory = "btssio/BTS2/BHL_Clothes" ;
      $urlActivation = "http://".$_SERVER["SERVER_ADDR"]."/".$baseDirectory."/authentification/activation?email=".$email."&cle=".$cleActivation;
     

      // email stuff (change data below)
      $to = $email; 
      $from = "email.test.qh@gmail.com" ; 
      $subject = "Activation de votre compte BHL Clothes"; 
      $message = "<h3> Bienvenue ! </h3> 
      <br> Votre compte a bien été créé, il ne vous reste plus qu'à l'activer en cliquant sur le lien ci-dessous.
      <br> <a href='".$urlActivation."'> $urlActivation <a> ";

      // a random hash will be necessary to send mixed content
      $separator = md5(time());

      // carriage return type (we use a PHP end of line constant)
      $eol = PHP_EOL;
    

      // main header
      $headers  = "From: ".$from.$eol;
      $headers .= "MIME-Version: 1.0".$eol; 
      $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

      // no more headers after this, we start the body! //

      $body = "--".$separator.$eol;
      $body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
    

      // message
      $body .= "--".$separator.$eol;
      $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
      $body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
      $body .= $message.$eol;



      // send message
      mail($to, $subject, $body, $headers);

    
    }

}


?>