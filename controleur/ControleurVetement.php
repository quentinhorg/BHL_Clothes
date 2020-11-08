<?php 
require_once('vue/Vue.php');

class ControleurVetement{
   private $vue;
   private $VetementManager;
   private $AvisManager;
   private $ClientManager;
   
   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 2 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{

         $id= $url[1] ;
         $msg= null;

         /*---------MANAGER---------*/
         $this->VetementManager = new VetementManager(); 
         $this->AvisManager= new AvisManager();
         $this->ClientManager= new ClientManager();
         /*------------------*/
         
         /*---------FORMULAIRE---------*/
         if ( isset($_POST['envoyerAvis']) && !empty($_POST['envoyerAvis'])) { //si le formulaire est envoyé
            
            if ( isset($_POST['avis']) && !empty($_POST['avis'])) { // vérification de l'avis
               
               if ( isset($_POST['note']) && !empty($_POST['note'])) { // vérification de la note
                  $this->insertAvis($id); // insertion
                  
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
         /*------------------*/

          if(  $this->infoVetement($id)->dispoPourVendre() == true){
            $this->vue = new Vue('Vetement') ;
            $this->vue->setListeJsScript(["public/script/js/bootstrapNote.js", 
                                          "public/script/js/jqueryNote.js",
                                          "public/script/js/Vetement.js"]);
            $this->vue->setListeCss(["public/css/fontawesomeNote.css"]); 
            $this->vue->genererVue(array( 
               "infoVetement"     => $this->infoVetement($id),
               "msg"              => $msg,
               "listeAvis" => $this->listeAvis($id),
               "client" => $GLOBALS["user_en_ligne"]
            )) ;
         }
       else{
          throw new Exception("Produit indisponible", 423);
       }
         
      }

   }
   
   // retourne les informations d'un vêtement
   private function infoVetement($id){
      $infoVetement= $this->VetementManager->getVetement($id);
      
      return $infoVetement;
     
   }

   // afficher les avis selon le vêtement
   private function listeAvis($id){
      $listeAvis= $this->AvisManager->getListeAvis($id);

      return $listeAvis;

   }

   // insérer un avis
   private function insertAvis($idVet){
      $idClient= $GLOBALS["user_en_ligne"]->id();

      $this->AvisManager->insertAvis($idVet, $idClient);
   }
}

?>