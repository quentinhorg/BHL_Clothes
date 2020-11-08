<?php 
require_once('vue/Vue.php');

class ControleurContact{
   private $vue;
   public $message;


   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 1 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{
         if (isset($_POST['Envoyer']) ) {
            $this->insertBDDContact();
         }
         
         $this->vue = new Vue('Contact') ;
         $this->vue->Popup->setMessage($this->message); //Initialisation du message 
         $this->vue->setHeader("vue/header.php") ;
         $this->vue->genererVue(array( 

          )) ; 
      }
   }

   //retourne les 3 derniers vetements
   private function insertBDDContact(){
      
      $ContactManager = new ContactManager();
      
      try {
         //Obtention des infos clients
         if( $GLOBALS["user_en_ligne"] == null ){ //Si pas connecté
            $idCli= null;
            if(!empty($_POST['nom'])){
               if (str_word_count($_POST['nom']) >= 2) { 
                  if(!empty($_POST['email'])){
                     if(!empty($_POST['tel'])){
                        $nom = $_POST['nom'];
                        $email = $_POST['email'];
                        $tel = $_POST['tel'];
                     } else{ throw new Exception("Veuillez entrer un numéro."); }
                  } else{ throw new Exception("Veuillez entrer un email." ); }
               }else{ throw new Exception("Veuillez entrer un nom et un prénom."); }
            } else{ throw new Exception("Veuillez entrer un nom."); }
      
         }
         else{ //Si connecté
            $clientEnLigne = $GLOBALS["user_en_ligne"];
            $idCli= $clientEnLigne->id();
            $nom = $clientEnLigne->nom()." ".$clientEnLigne->prenom() ;
            $email = $clientEnLigne->email();
            $tel = $clientEnLigne->tel();
         }
         
         //Obtention des infos du message
         if(!empty($_POST['sujet'])){
            if(!empty($_POST['message'])){
               $sujet = $_POST['sujet'];
               $message = $_POST['message'] ;
            } else{ throw new Exception("Veuillez entrer un message."); }
         } else{ throw new Exception("Veuillez entrer un sujet."); }

         $ContactManager->insertBDDContact($idCli, $nom, $email, $tel, $sujet, $message);
         $this->message= "Votre message a bien été envoyé.";

         // envoyer la réponse au problème par mail
         if (isset($_POST['reponseEnvoi'])) {
            $this->envoyerReponseContact($_POST['email']);
         }else{  echo "Il y a eu un problème lors de l'envoi. Réessayez."; }

      } catch (Exception $e) {
         $this->message= $e->getMessage();
      } 
   }

//    private function envoyerReponseContact($email){
        
//       $header="MIME-Version: 1.0\r\n";
//       $header.='From:"Andrea"<support@nomdomaine.com>'."\n";
//       $header.='Content-Type:text/html; charset="uft-8"'."\n";
//       $header.='Content-Transfer-Encoding: 8bit';

//       $to = $email; 
//       $from = "email.test.qh@gmail.com" ; 
//       $subject = "Réponse à votre problème"; 
//       $message = $_POST['reponseMail'];

//       mail($to, $subject, $message, $header);
     
//   }

}

?>