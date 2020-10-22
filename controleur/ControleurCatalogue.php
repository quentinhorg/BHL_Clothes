<?php 
require_once('vue/Vue.php');

class ControleurCatalogue{
   private $vue;
   private $vetementManager;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception(null, 404);
      }
      else{


         $idCateg= null;
         
         if( isset( $url[2])){
            $idCateg = $url[2];
         }

         $codeGenre = null;
         if( isset( $url[1])){
            $codeGenre = $url[1];
         }

       

         //Systeme de recherche
         if(isset($_GET['trier']) || isset($_POST['motCle']) ){
            $listeVetement = $this->recherche($codeGenre, $idCateg);
         }
       
         else{
            $listeVetement =  $this->listeVetement($codeGenre,  $idCateg);
         }
       

      
            //Récupèration des arguments de l'url actif
            $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
            "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .  
            $_SERVER['REQUEST_URI']; 
            $argUrl = parse_url($link, PHP_URL_QUERY);

            $vuePagination = $this->vetementManager->Pagination->getVuePagination("catalogue?".$argUrl) ;
      



         $this->vue = new Vue('Catalogue') ;
         $this->vue->setListeJsScript(["public\script\js\Catalogue.js"]);
         $this->vue->genererVue(array( 
            "listeVetement"=> $listeVetement,
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
   private function listeVetement($libelleGenre, $idCateg){
      $this->vetementManager = new VetementManager;
      $this->vetementManager->setPagination(10);

      if($libelleGenre != null && $idCateg != null){
         $listeVetement =  $this->vetementManager->getListeVetByCategGenre($libelleGenre, $idCateg);
      }
      else if($libelleGenre != null && $idCateg == null){
         $listeVetement =  $this->vetementManager->getListeVetByGenre($libelleGenre);
      }
      else{
         $listeVetement = $this->vetementManager->getListeVetement();
      }

      return $listeVetement;
      
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

      $this->vetementManager = new VetementManager;
      $this->vetementManager->setPagination(10);

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
      
      
      $resultat = $this->vetementManager->getRechercheVetement(
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