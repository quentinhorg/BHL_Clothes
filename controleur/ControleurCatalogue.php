<?php 
require_once('vue/Vue.php');

class ControleurCatalogue{
   private $vue;
   private $VetementManager;
   private $TailleManager;
   private $GenreManager;
   private $CouleurManager;
   private $CategorieManager;
 

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception(null, 404); //Erreur 404
      }
      else{
         /*---------Initialisation des managers---------*/
         $this->VetementManager = new VetementManager;
         $this->TailleManager = new TailleManager;
         $this->GenreManager = new GenreManager;
         $this->CouleurManager = new CouleurManager;
         $this->CategorieManager = new CategorieManager;
         $this->GenreManager = new GenreManager;
         $this->VetementManager->setPagination(10); //Propriété de la pagination du catalogue
         /*------------------*/
          
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

         /*---------VUE---------*/
         $this->vue = new Vue('Catalogue') ;
         $this->vue->setListeJsScript(["public\script\js\Catalogue.js"]);
         $this->vue->genererVue(array( 
            "listeVetementDispo"=> $listeVetementDispo,
            "vuePagination" => $vuePagination,
            "listeTaille"=> $this->listeTaille() ,
            "listeGenre"=> $this->GenreManager->getListeGenre(),
            "listClrPrincipale" => $this->CouleurManager->getPrincipaleCouleur(),
            "genreActive" => $this->GenreManager->getGenre($codeGenre),
            "categActive" => $this->CategorieManager->getCateg($idCateg)
         )) ;
         /*------------------*/
         
      }
   }

   //Retourne la liste des vêtement par catégorie ou non =)
   private function listeVetementDispo(){
      $listeVetementDispo = $this->VetementManager->getListeVetement(true);
      return $listeVetementDispo;
   }

   //Obtention de la liste de tailles (Chiffre et Lettre)
   public function listeTaille(){
      $listeTaille = array(
         "chiffre" => $this->TailleManager->getListeTailleChiffre(),
         "lettre" =>$this->TailleManager->getListeTailleLettre()
      );
      return $listeTaille; // Tableau associatif d'objets Taille
   }

   //Trier le catalogue
   public function recherche($genre, $categorie){
      
      $prixIntervale = null; 
      $listeTaille = null;
      $listeCouleur=null;
      $motCle=null;

      //Obtention des critères de recherche
      if (!empty($_GET['budget']) ){ $prixIntervale = [0, $_GET['budget']]; }
      if (!empty($_GET['taille'])){$listeTaille = $_GET['taille'];}
      if(!empty($_GET['couleur'])){$listeCouleur = $_GET['couleur'];}
      if(!empty($_GET['motCle'])){$motCle = $_GET['motCle']; }

      //Recherche du vêtement
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