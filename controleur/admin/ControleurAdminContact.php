<?php 



class ControleurAdminContact{
   private $vue;
   public $message;
   private $ContactManager;
   
   // CONSTRUCTEUR 
   public function __construct($url){


      if( isset($url) && count($url) > 3 ){
      
         throw new Exception(null, 404); //Erreur 404
      }
      else{

         /*---------MANAGER---------*/
         $this->ContactManager= new ContactManager();
         /*------------------*/

      
         /*---------FORMULAIRE---------*/
         if( isset($_POST["supMessage"]) ){ //Si formulaire supprimé
            $this->ContactManager->supprimer($_POST["supMessage"]);
         }

         if (isset($_POST["reponseEnvoi"])) {
            $this->envoyerReponseContact($this->ContactManager->getContact($url[2]), $_POST["reponseMail"]);
         }
         /*------------------*/
     
         /*---------VUE---------*/
         if(isset($url[2])) {
            $idContact=$url[2];
            $nomVue= "AdminContactMessage";
            $donnee= array(
               "contactInfo" => $this->ContactManager->getContact($idContact) //Obtenir un contact
            );
         }
         else{
            $nomVue= "AdminContact";
            $donnee= array(
               "contactList" => $this->ContactManager->getListeContact(), //Obtenir la liste des contacts
              // "reponse"     => $this->ContactManager->getReponse($idContact)
            );
         }

         $this->vue = new VueAdmin($nomVue) ;
         $this->vue->Popup->setMessage($this->message);
         $this->vue->genererVue($donnee) ;
         /*------------------*/
      }
   }

   
private function envoyerReponseContact(Contact $Contact, $reponseBHL){

   

   // email stuff (change data below)
   $to = $Contact->email(); 
   $from = "email.test.qh@gmail.com" ; 
   $subject = "Réponse BHL Clothes - ".$Contact->sujet(); 
   
   /*---Toutes les réponses d'un contact---*/
   $AncienneReponse = "";
   foreach($Contact->listeReponse() as $reponse){
      $AncienneReponse .= "<p> <span style=''> ".$reponse->date('d/m/Y à H\hi').":</span> ".$reponse->reponse()."<p>";
      $AncienneReponse .= "<hr>";
   }
   /*------------------------------------*/
   $MessageDeBase= "<p> <span>".$Contact->date('d/m/Y à H\hi')."</span>: ".$Contact->message()."</p>"; //Premier message

   $message = "<p> <span> Dernière réponse:  </span> $reponseBHL </p> <hr> ".$AncienneReponse.$MessageDeBase;


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

   $this->ContactManager->insertReponse($Contact->idContact(), $reponseBHL); //Insérer la réponse dans la BDD
   mail($to, $subject, $body, $headers); //Envoyer la réponse par mail
   $this->message = "Votre réponse à bien été envoyée." ;
 
 }


   

}

?>