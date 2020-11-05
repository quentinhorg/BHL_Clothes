<?php 
require_once('vue/Vue.php');

class ControleurAuthentification{
   private $vue;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){

     
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception(null, 404); //Erreur 404
      }

      else {

         if( $GLOBALS["user_en_ligne"] == null ){

            // Connexion
            if( @strtolower($url[1])=="connexion" || !isset($url[1])|| empty($url[1]) ){
             
               if (isset($_POST['submit'])){
                  if (!empty($_POST['email'])){
                     if (!empty($_POST['mdp'])){
      
                        $mail= $_POST['email'];
                        $mdp = $_POST['mdp'];

                        $ClientManager = new ClientManager();
                        $idClient = $ClientManager->getId($mail, $mdp);

                        if( $idClient >= 1 ){
                           $active= $ClientManager->getClient($idClient)->compteActive() ;
                           
                           if ($active == true) {
                              $this->tryConnexion($mail, $mdp);
                           } else{  $this->message = "Votre compte n'a pas encore été activé.";  }
                        } else{  $this->message = "Identifiants incorrecte.";  }
                     } else { $this->message = "Veuillez entrer un mot de passe";  }
                  } else {  $this->message = "Veuillez entrer votre Email";  }
               }
      
               $nomVue = "Connexion" ;
               $donnee = array() ;
            }
            //Inscription
            else if(@strtolower($url[1]) == "inscription"){
           
            
               if (isset($_POST['submit'])){
                  if (!empty($_POST['nom'])) {
                     if (!empty($_POST['prenom'])) {
                        if (!empty($_POST['cp'])) {
                           if (!empty($_POST["rue"])){
                              if (!empty($_POST['email'])) {
                                 $ClientManager = new ClientManager();

                                 if( !$ClientManager->emailExiste($_POST['email'])  ){
                                    if (!empty($_POST["mdp"])){
                                       if (!empty($_POST['tel'])) {
                                          
                                          $idClientRegister = $this->inscrireClient();
                                          $this->message =  "<div style='text-align:center;'> Un mail de confirmation a été envoyé à <b>".$_POST['email']."</b> <p style='font-style: italic ; font-size:0.8rem'> Si vous n'avez pas reçu le lien de confirmation <a href='contact'> contactez-nous </a>. </p> </div>" ;
                                          if( $_SESSION["ma_commande"]->panier() != NULL ){
                                             $this->insertPanierSessionToBdd($idClientRegister, $_SESSION["ma_commande"]);
                                          }
                                    
                                       }else {  $this->message = "Veuillez entrer votre numéro de téléphone"; }
                                    }else {  $this->message = "Veuillez entrer un mot de passe"; }
                                 } else{ $this->message = "Ce mail est déjà utilisé." ;}
                              }else {  $this->message = "Veuillez entrer votre Email"; }
                           }else{ $this->message = "Veuillez entrer votre rue";} 
                        }else {  $this->message = "Veuillez entrer votre code postal"; }
                     }else {  $this->message = "Veuillez entrer un Prénom"; }
                  }else {  $this->message = "Veuillez entrer un nom";   }
               }
               $nomVue = "Inscription" ;
               $donnee = array( "listCp" => $this->getListCpReunion() ) ;
            }
            //Activation
            else if( @strtolower($url[1]) == "activation" ){
               //Vue pour le lien d'activation recus par mail
               if( isset($_GET["email"]) && isset($_GET["cle"])  ){
                  $this->tryActiveCompte($_GET["email"], $_GET["cle"]);
                  $nomVue = "Activation" ;
                  $donnee = array( "listCp" => $this->getListCpReunion() ) ;
               }
               else{ throw new Exception("Manque d'informations pour pouvoir procéder à l'activation du compte.", 400);  }
            }
             //Désactivation
            else if( @strtolower($url[1]) == "desactivation" && isset($_GET["email"]) && isset($_GET["cle"]) ){
               
               
               $this->desactiveCompte($_GET["email"], $_GET["cle"]);
               $nomVue = "Desactivation" ;
               $donnee = array() ;
               
            
            }
            else { throw new Exception(null, 404);  }//Erreur 404

         }
         else{

            if( @strtolower($url[1]) == "deconnexion" && $GLOBALS["user_en_ligne"] != null ){
               $this->deconnexion();
               header("Location: ".URL_SITE);
               exit();
            }
            else{
               throw new Exception(null, 404); //Erreur 404
            }
                 
               
         }
        

         $this->vue = new Vue($nomVue) ;
         $this->vue->Popup->setMessage($this->message);
         $this->vue->genererVue($donnee) ;
         
        

      }
      
   }

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

      $this->envoyerMailVerifCompte($_POST['email']);

      return $ClientManager->getId($_POST['email'], $_POST['mdp']);


   }

   private function deconnexion(){
      $_SESSION["id_client_en_ligne"] = null;
      unset($_SESSION["id_client_en_ligne"]);
      session_destroy();
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
         $idClient = $ClientManager->getId($mail, $mdp);
        
         $_SESSION["id_client_en_ligne"] = $idClient ;
         $GLOBALS["user_en_ligne"] = $ClientManager->getClient($idClient) ;
         $this->suppSessionCmd();
         header("Location: ".URL_SITE."catalogue");
   
   }
   //Supprime un compte pas encore activé
   public function desactiveCompte($email, $cle){
      $ClientManager = new ClientManager;

      try{
      
         $ClientManager->desactiveCompte($email, $cle);
         $this->message = "<b style='color:#51a251'> Votre compte à bien été désactivé / supprimé </b>";
 
      } catch (Exception $e) {
         
         if($e->getMessage() == "La clé est incorrecte"){
            $this->message = "Impossible de désactiver le compte, la clé est incorrecte.";
         }
         else if ($e->getMessage() == "Le compte est déjà activé") {
            $this->message = "Ce compte est déjà activé, impossible de le supprimer / désactiver.";
         }

         else if ($e->getMessage() == "L'email n'existe pas") {
            $this->message = "Ce compte n'existe pas ou il à été déjà désactivé / supprimé.";
         }
         else{
            $this->message = "Erreur, votre compte n'a pas pu être désactivé." ;
         }


      }

    
  

         
   }

   public function tryActiveCompte($mail, $cle){
      $ClientManager = new ClientManager;
      $this->deconnexion();
      try{
         $ClientManager->tryActiveCompte($mail, $cle) ;
         $this->message = "<b style='color:#51a251'> Votre compte à bien été activé </b>, vous pouvez maintenant <a href='authentification/connexion'> vous connectez </a>. ";
  
      } catch (Exception $e) {
     
         if($e->getMessage() == "L'email n'existe pas"){
            $this->message = $mail." n'existe pas, veuillez nous <a href='contact'> contactez </a> si le problème persiste.";
         }
         else if ($e->getMessage() == "La clé d'activation est incorrecte") {
            $this->message = "La clé d'activation est incorrecte.";
         }
         else if ($e->getMessage() == "Le compte est déjà activé") {
            $this->message = "Ce compte est déjà activé, veuillez <a href='authentification/connexion'> vous connectez </a>.";
         }
         else{
            $this->message = "Erreur, votre compte n'a pas pu être activé." ;
        }

      
      }

 

   }

   private function getListCpReunion(){
     
     $CodePostalManager = new CodePostalManager;
      return $CodePostalManager->getListCp();
   }

   private function envoyerMailVerifCompte($email){

      $ClientManager = new ClientManager;
      $cle = $ClientManager->getCleClient($email);
      
      $baseDirectory = "btssio/BTS2/BHL_Clothes" ;
      $urlActivation = "http://".$_SERVER["SERVER_NAME"]."/".$baseDirectory."/authentification/activation?email=".$email."&cle=".$cle;
      $urlDesactivation = "http://".$_SERVER["SERVER_NAME"]."/".$baseDirectory."/authentification/desactivation?email=".$email."&cle=".$cle;
      

      
      // email stuff (change data below)
      $to = $email; 
      $from = "email.test.qh@gmail.com" ; 
      $subject = "Activation de votre compte BHL Clothes"; 
      $message = "<h3> Bienvenue ! </h3> 
      <br> Votre compte a bien été créé, il ne vous reste plus qu'à l'activer en cliquant sur le lien ci-dessous.
      <br> <a href='".$urlActivation."'> $urlActivation <a> <br> <br>
      <p> Si vous êtes pas à l'origine de ce compte <a href='$urlDesactivation'> cliquer ici </a>. </p>
      ";

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