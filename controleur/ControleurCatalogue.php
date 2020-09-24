<?php 
require_once('vue/Vue.php');

class ControleurCatalogue{
   private $vue;
   private $vetementManager;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( isset($url) && count($url) > 3 ){
         throw new Exception('Page introuvable');
      }
      else{
         
         //Systeme de recherche

         $idCateg= null;
         if( isset( $url[2])){
            $idCateg = $url[2];
         }

         $codeGenre = null;
         if( isset( $url[1])){
            $codeGenre = $url[1];
         }
         
         


         $categActive = null;




         if(isset($_POST['recherche'])){
            $donnee = $this->recherche($codeGenre, $idCateg);
         }
         else if( isset($url[1]) && isset($url[2]) ){
            $donnee =  $this->listeVetement($url[1], $url[2]);
            
         }
         else if( isset($url[1]) && !isset($url[2]) ){
            $donnee =  $this->listeVetement($url[1], null);
         }
         else{
            $donnee =  $this->listeVetement(null, null);
         }


         



         //A optimisé
         if( $this->vetementManager != null){
            $vuePagination = $this->vetementManager->Pagination->getVuePagination("catalogue&page=") ;
         }else{ $vuePagination = null ;}



         $this->vue = new Vue('Catalogue') ;
         $this->vue->genererVue(array( 
            "listeVetement"=> $donnee,
            "vuePagination" => $vuePagination,
            "listeTaille"=>$this->listTailleByCateg($idCateg),
            "listClrPrincipale" => $this->listClrPrincipale(),
            "genreActive" => $this->genre($codeGenre)
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

   public function listTailleByCateg($idCateg){
      $TaillesCatalogue = new TailleManager();
      return $TaillesCatalogue->getListeTailleByCateg($idCateg);
   }

   public function listClrPrincipale(){
      $CouleurManager = new CouleurManager();
      return $CouleurManager->getPrincipaleCouleur();
   }

   public function recherche($genre, $categorie){

      $this->vetementManager = new VetementManager;
      $this->vetementManager->setPagination(10);

      $prixIntervale = null;
      if (!empty($_POST['prix']) ){
         $prixIntervale = [0, $_POST['prix']];
      }

      $listeTaille = null;
      if (!empty($_POST['taille'])){
         $listeTaille = $_POST['taille'];
      }
      
      $listeCouleur=null;
      if(!empty($_POST['couleur'])){
         $listeCouleur = $_POST['couleur'];
      }
      
      
   $resultat = $this->vetementManager->getRechercheVetement(
      $prixIntervale, 
      $listeTaille, 
      $listeCouleur, 
      $categorie, 
      $genre
   );

   return $resultat ;


      
      

      //return $RechercheManager->getRecherche();
   }



   public function genre($code){
      $this->GenreManager = new GenreManager;
      $genre = $this->GenreManager->getGenre($code);
      return $genre;
   }
   



}

?>