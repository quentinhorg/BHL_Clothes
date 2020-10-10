<?php 
require_once('vue/Vue.php');

class ControleurVetement{
   private $vue;
   // CONSTRUCTEUR 
   public function __construct($url){
      
     

      if( isset($url) && count($url) > 2 ){
         throw new Exception('Page introuvable');
      }
      else{

         $id= $url[1] ;
         $msg= null;
         
         if ( isset($_POST['envoyerCommentaire']) && !empty($_POST['envoyerCommentaire'])) {
            
            if ( isset($_POST['commentaire']) && !empty($_POST['commentaire'])) {
               
               if ( isset($_POST['note']) && !empty($_POST['note'])) {
                  $this->insertCommentaire($id);
                  $msg="Votre commentaire a bien été posté.";
               }

               else{
                  $msg= "Veuillez ajouter une note.";
               }
           }
            else{
               $msg= "Veuillez ajouter un commentaire.";
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
            "listeCommentaire" => $this->listeCommentaire($id),
            "client" => $GLOBALS["client_en_ligne"]
         )) ;
         
      }

   }

   private function infoVetement($id){
      $VetementManageur = new VetementManager();
      $infoVetement= $VetementManageur->getVetement($id);
      
      return $infoVetement;
     
   }

   // afficher les commentaires selon le vêtement
   private function listeCommentaire($id){

      $CommentaireManager= new CommentaireManager();

      $listeCommentaire= $CommentaireManager->getListeCommentaire($id);

      return $listeCommentaire;

   }

   // insérer un commentaire
   private function insertCommentaire($idVet){
      $CommentaireManager = new CommentaireManager();
      $ClientManager= new ClientManager();
      $idClient= $GLOBALS["client_en_ligne"]->getId();

      $CommentaireManager->insertCommentaire($idVet, $idClient);
   }


  

}

?>