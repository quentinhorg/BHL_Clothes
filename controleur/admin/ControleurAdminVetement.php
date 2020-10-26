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
         throw new Exception(null, 404);
      }
      else{
            //Initialisation des managers 
            $this->VetementManager = new VetementManager;
            $this->TailleManager = new TailleManager;
            $this->CategorieManager = new CategorieManager;
            $this->GenreManager = new GenreManager;
      

            if( isset($_POST["modifierVet"]) ){
               $this->modifierVet($url[2], );
            }

         
            //Modifier une vetement
            if( isset($url[2]) && $this->message != "La vetement à bien été supprimé." ){

               $vue = "AdminVetementModifier" ;
               $donnee = array( 
                  "vetement" => $this->vetementInfo($url[2]),
                  "listTaille" => $this->listTaille(),
                  "listCate" => $this->listCateg(),
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
         $this->VetementManager->modifierVetement($id, $nom, $prix ,$motifPosition ,$codeGenre ,$description ,$idCateg);
         $this->message= "La vetement à bien été supprimé.";
  
      } catch (Exception $e) {

         if($e->getCode() == 45000 ){
            $this->message = $e->getMessage() ;
         }
         else{
            $this->message = "<b>Erreur lors de la modification du vêtement $id </b> <br>".$e->getMessage();
         }
        
      }

   }

  

}

?>