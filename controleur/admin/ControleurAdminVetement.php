<?php 



class ControleurAdminVetement{
   private $vue;
   private $VetementManager ;
   private $TailleManager ;
   private $CategorieManager ;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{
            //Initialisation des managers 
            $this->VetementManager = new VetementManager;
            $this->TailleManager = new TailleManager;
            $this->CategorieManager = new CategorieManager;
            $this->GenreManager = new GenreManager;
            $this->CouleurManager = new CouleurManager;
        

            //Vetement
            if( isset($_POST["modifierVet"]) ){
               $this->modifierVet($url[2], $_POST["nomVet"], $_POST["prixVet"],  $_POST["motifConfigVet"], $_POST["genreVet"], $_POST["descVet"], $_POST["categVet"]);
            }

            //Couleur du Vetement
            if( isset($_POST["modifierCouleur"]) ){
               $this->modifierCouleur($_POST["modifierCouleur"],$_POST["nomClr"] ,$_POST["filterCssCodeClr"], $_POST["dispoClr"]);
            }
            else if( isset($_POST["ajouterCouleur"]) ){
               $this->ajouterCouleur($url[2], $_POST["nomClr"], $_POST["filterCssCodeClr"], $_POST["dispoClr"]);
            }
            else if( isset($_POST["supprimerCouleur"]) ){
               $this->supprimerCouleur($_POST["supprimerCouleur"]);
            }

            //Taille du Vetement
            if( isset($_POST["supprimerTaille"]) ){
               $this->supprimerTaille($url[2],$_POST["supprimerTaille"]);
            }
            else if( isset($_POST["ajouterTaille"]) ){
               $this->ajouterTaille($url[2],$_POST["taille"]);
            }

         
            //Modifier une vetement
            if( isset($url[2]) && $this->message != "La vetement à bien été supprimé." ){
               $vue = "AdminVetementModifier" ;
               $donnee = array( 
                  "vetement" => $this->vetementInfo($url[2]),
                  "listTaille" => $this->listTaille(),
                  "listCateg" => $this->listCateg(),
                  "listGenre" =>$this->listGenre()
               );
            }
            //Listing des vetements 
            else{
               $vue = "AdminVetement" ;
               $donnee = array( 
                  "vetementList" =>  $this->listVetement()
               );
            }
            

         $this->vue = new VueAdmin($vue) ;
         $this->vue->Popup->setMessage($this->message);
         $this->vue->genererVue($donnee) ;
      }
   }
   

   // Taille
   private function listTaille(){
      return $this->TailleManager->getListeTaille();
   }

   // Categorie
   private function listCateg(){
      return $this->CategorieManager->getListeCateg();
   }

   // Categorie
   private function listGenre(){
      return $this->GenreManager->getListeGenre();
   }



   // Vetement
   private function listVetement(){
      return $this->VetementManager->getListeVetement();
   }
   private function vetementInfo($id){
      return $this->VetementManager->getVetement($id);
   }
   
   private function modifierVet($id, $nom, $prix ,$motifPosition ,$codeGenre ,$description ,$idCateg){
      
      try{
         //Apres le vide du panier, la vetement se supprime automatiquement  
         $this->VetementManager->updateBDD($id, $nom, $prix ,$motifPosition ,$codeGenre ,$description ,$idCateg);
        
         $this->message= "La vetement à bien été modifié.";
  
      } catch (Exception $e) {
      
         if($e->getCode() == 45000){
            $this->message = $e->getMessage();
         }
         else{
            $this->message = "<b> Erreur lors de la modification du vêtement $id </b> <br>";
         }
        
      }

   }

   // Couleur vêtement
   private function ajouterCouleur($idVet, $nomClr, $filterCssCodeClr, $dispoClr){
    
      $this->CouleurManager->insertBDD($idVet, $nomClr, $filterCssCodeClr, $dispoClr);
   }

   private function modifierCouleur($numClr, $nomClr, $filterCssCodeClr, $dispoClr){

      $this->CouleurManager->updateBDD($numClr, $nomClr, $filterCssCodeClr, $dispoClr);
   }

   private function supprimerCouleur($numClr){
      $this->CouleurManager->deleteBDD($numClr);
   }

   // Taille vêtement
   private function ajouterTaille($idVet,$taille){
      $this->TailleManager->insertBDD($idVet, $taille);
   }

   private function supprimerTaille($idVet, $taille){
      $this->TailleManager->deleteBDD($idVet, $taille);
   }
  


  

}

?>