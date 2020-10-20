<?php 
require_once('vue/Vue.php');

class ControleurVetement{
   private $vue;
   // CONSTRUCTEUR 
   public function __construct($url){
      
     

      if( isset($url) && count($url) > 2 ){
         throw new Exception('Page introuvable', 404);
      }
      else{

         $id= $url[1] ;
         $msg= null;
         
         if ( isset($_POST['envoyerAvis']) && !empty($_POST['envoyerAvis'])) {
            
            if ( isset($_POST['avis']) && !empty($_POST['avis'])) {
               
               if ( isset($_POST['note']) && !empty($_POST['note'])) {
                  $this->insertAvis($id);
                  $msg="Votre avis a bien été posté.";
               }

               else{
                  $msg= "Veuillez ajouter une note.";
               }
           }
            else{
               $msg= "Veuillez ajouter un avis.";
            }
         }

         $this->vue = new Vue('Vetement') ;
         $this->vue->setListeJsScript(["public/script/js/bootstrapNote.js", 
                                       "public/script/js/jqueryNote.js",
                                       "public/script/js/Vetement.js"]);
         $this->vue->setListeCss(["public/css/fontawesomeNote.css"]); 
         $this->vue->genererVue(array( 
            "infoVetement"     => $this->infoVetement($id),
            "msg"              => $msg ,
            "listeAvis" => $this->listeAvis($id),
            "client" => $GLOBALS["client_en_ligne"]
         )) ;
         
      }

   }

   private function infoVetement($id){
      $VetementManageur = new VetementManager();
      $infoVetement= $VetementManageur->getVetement($id);
      
      return $infoVetement;
     
   }

   // afficher les aviss selon le vêtement
   private function listeAvis($id){

      $AvisManager= new AvisManager();

      $listeAvis= $AvisManager->getListeAvis($id);

      return $listeAvis;

   }

   // insérer un avis
   private function insertAvis($idVet){
      $AvisManager = new AvisManager();
      $ClientManager= new ClientManager();
      $idClient= $GLOBALS["client_en_ligne"]->getId();

      $AvisManager->insertAvis($idVet, $idClient);
   }


  

}

?>