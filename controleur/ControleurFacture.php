<?php 

require_once('vue/Vue.php');

class ControleurFacture{
   private $vue;

   // CONSTRUCTEUR 
   public function __construct($url){

      if( isset($url) && count($url) > 2 ){
         throw new Exception('Page introuvable');
      }
      else{
       


         
         if(
            isset($url[1]) && $GLOBALS["client_en_ligne"] != null 
            && $this->facture($url[1])->Commande()->Etat()->id() != 1 
            && $GLOBALS["client_en_ligne"]->getId() == $this->facture($url[1])->Commande()->idClient()
         ){
       
            $client = $GLOBALS["client_en_ligne"] ;
            $facture = $this->facture($url[1]) ;
            $listeCp = $this->listeCp();
            
            include "vue/vueFacture.php";
            $pdf->buildPDF();

            if( isset($_GET["envoyerFactureMail"]) ){
               $this->envoyerMailFacture($client, $facture, $pdf);   
            }
            $pdf->Output(); //Affichage du PDF

        

         

           

      
      

         }
         else{
            throw new Exception('Page introuvable');
         }
         
      }

   }

 
   
   
   public function facture($idCmd){
      $FactureManager = new FactureManager();
      $facture = $FactureManager->getFacture($idCmd);

   

      return $facture ;
   }



   //$CodePostalManager->getListCp();
   public function listeCp(){
      $CodePostalManager = new CodePostalManager();
      $listeCodePostal = $CodePostalManager->getListCp();

      return $listeCodePostal;
   }

   public function envoyerMailFacture(Client $Client, Facture $Facture, facturePDF $pdf ){


      // email stuff (change data below)
      $to = $Client->email(); 
      $from = "email.test.qh@gmail.com" ; 
      $subject = "BHL Clothes - Détails de votre commande"; 
      $message = "<p>Bonjour ".$Client->getNom()." ".$Client->getPrenom().",  <br> 
            Merci de votre commande du jour ! Votre confiance nous touche beaucoup…
            Nous mettons tout en œuvre pour la préparer avec les meilleures précautions d'hygiène et de sécurité, ce qui nous impose des délais moins rapides qu'habituellement.
            Trouvez ci-joint votre facture. Vous pourrez aussi suivre son expédition depuis votre compte client. <br>
            Encore un grand merci et à très bientôt.<br> <br>
      
            L'équipe BHL.

      </p>";

      // a random hash will be necessary to send mixed content
      $separator = md5(time());

      // carriage return type (we use a PHP end of line constant)
      $eol = PHP_EOL;

      // attachment name
      $filename = "Facture N".$Facture->Commande()->num()." - ".$Client->getNom()." ".$Client->getPrenom().".pdf";

      // encode data (puts attachment in proper format)
      $pdfdoc = $pdf->Output("", "S");
      $attachment = chunk_split(base64_encode($pdfdoc));

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

      // attachment
      $body .= "--".$separator.$eol;
      $body .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol; 
      $body .= "Content-Transfer-Encoding: base64".$eol;
      $body .= "Content-Disposition: attachment".$eol.$eol;
      $body .= $attachment.$eol;
      $body .= "--".$separator."--";

      // send message
      mail($to, $subject, $body, $headers);
      

   
   }

}

?>