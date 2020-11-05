<?php 
require_once('vue/Vue.php');

class ControleurCatalogue{
   private $vue;
   private $VetementManager;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{
         $this->VetementManager = new VetementManager;
         $this->VetementManager->setPagination(10);

     
   
         

          
         $idCateg= null; $codeGenre = null;
         if( isset( $url[2])){ $idCateg = $url[2]; }
         if( isset( $url[1])){ $codeGenre = $url[1]; }

        

         //Systeme de tri
         if( isset($_GET['trier']) || isset($_GET['motCle']) || $idCateg != null || $codeGenre != null  ){
            
            $listeVetementDispo = $this->recherche($codeGenre, $idCateg);
         }
         //Tous les vêtements
         else{
           
            $listeVetementDispo =  $this->listeVetementDispo();
          
         }
      
        

      
           
      

         $vuePagination = $this->VetementManager->Pagination->getVuePagination(LIEN_ACTIVE) ;

         $this->vue = new Vue('Catalogue') ;
         $this->vue->setListeJsScript(["public\script\js\Catalogue.js"]);
         $this->vue->genererVue(array( 
            "listeVetementDispo"=> $listeVetementDispo,
            "vuePagination" => $vuePagination,
            "listeTaille"=> $this->listeTaille() ,
            "listeGenre"=>$this->listeGenre(),
            "listClrPrincipale" => $this->listClrPrincipale(),
            "genreActive" => $this->genre($codeGenre),
            "categActive" => $this->categ($idCateg)
          
            
         )) ;
         
      }
   }

   //Retourne la liste des vêtement par catégorie ou non =)
   private function listeVetementDispo(){
      
      $listeVetementDispo = $this->VetementManager->getListeVetementDispo();

      return $listeVetementDispo;
      
   }

   public function listeTaille(){
      $TaillesCatalogue = new TailleManager();

      $listeTaille = array(
         "chiffre" => $TaillesCatalogue->getListeTailleChiffre(),
         "lettre" =>$TaillesCatalogue->getListeTailleLettre()
       
      );

      return $listeTaille;
   }

   public function listClrPrincipale(){
      $CouleurManager = new CouleurManager();
      return $CouleurManager->getPrincipaleCouleur();
   }

   public function genre($code){
      $this->GenreManager = new GenreManager;
      $genre = $this->GenreManager->getGenre($code);
      return $genre;
   }

   public function categ($idCateg){
      $this->CategorieManager = new CategorieManager;
      $categ = $this->CategorieManager->getCateg($idCateg);
      return $categ;
   }

   public function listeGenre(){
      $this->GenreManager = new GenreManager;
      $listeGenre = $this->GenreManager->getListeGenre();
      return $listeGenre;
   }

   public function recherche($genre, $categorie){
      
      $prixIntervale = null;
      if (!empty($_GET['budget']) ){
         $prixIntervale = [0, $_GET['budget']];
      }

      $listeTaille = null;
      if (!empty($_GET['taille'])){
         $listeTaille = $_GET['taille'];
      }
      
      $listeCouleur=null;
      if(!empty($_GET['couleur'])){
         $listeCouleur = $_GET['couleur'];
      }

      $motCle=null;
      if(!empty($_GET['motCle'])){
     
         $motCle = $_GET['motCle'];
      }
      
      
      $resultat = $this->VetementManager->getRechercheVetement(
         $prixIntervale, 
         $listeTaille, 
         $listeCouleur, 
         $categorie, 
         $genre,
         $motCle
      );

      return $resultat ;

   }



  
   



}

?>