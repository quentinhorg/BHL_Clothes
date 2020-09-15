<?php 

class Vetement{
   private   $id;
   private   $nom;
   private   $prix;
   private   $codeRgbOriginal;
   private   $motifPosition;
   private   $categ; // OBJET
   public    $listeCouleurDispo; // OBJET
   private   $genre; // OBJET
   private   $listeTailleDispo; // OBJET
   private   $description;

   
   
   public function __construct(array $donnee){
      $this->hydrate($donnee);

   }

   //HYDRATATION
   public function hydrate(array $donnee){
      foreach($donnee as $cle => $valeur){
         $methode = 'set'.ucfirst($cle);
      
         if(method_exists($this, $methode)){
            $this->$methode($valeur);
         }
      }
   }


   
   //SETTER
   public function setId($id){
      $id = (int) $id;

      if($id > 0){
         $this->id = $id;
      }
   }

   public function setNom($nom){
      if(is_string($nom)){
         $this->nom = $nom;
      }
   }

   public function setCodeRgbOriginal($codeRgbOriginal){
      if(is_string($codeRgbOriginal)){
         $this->codeRgbOriginal = $codeRgbOriginal;
      }
   }

   public function setPrix($prix){
      if(is_string($prix)){
         $this->prix = $prix;
      }
   } 
   
   public function setMotifPosition($motifPosition){
      if(is_string($motifPosition)){
         $this->motifPosition = $motifPosition;
      }
   }

   public function setIdCateg($idCateg){
      $idCateg = (int) $idCateg;
      if($idCateg > 0){
         $CategManager = new CategorieManager;
         $this->categ = $CategManager->getCateg($idCateg);
      }

   }

   public function setListeIdCouleurDispo($listIdCouleur){
      $listIdCouleur = explode(",", $listIdCouleur);
      
      if($listIdCouleur != null){

         $CouleurManager = new CouleurManager;

         foreach ($listIdCouleur as $idCouleur) {
            $idCouleur = (int) $idCouleur;
            if($idCouleur > 0){
               $this->listeCouleurDispo[] = $CouleurManager->getCouleur($idCouleur);

            }
            
         }

      }
      
   }

   public function setListeIdTailleDispo($listIdTaille){

      $listIdTaille = explode(",", $listIdTaille);
      $TailleManager = new TailleManager;

      foreach ($listIdTaille as $idTaille) {
         $idTaille = (int) $idTaille;
         if($idTaille > 0){
            $this->listeTailleDispo[] = $TailleManager->getTaille($idTaille);
         }
         
      }

   }

   public function setNumGenre($numGenre){

      $numGenre = (int) $numGenre;
      if($numGenre > 0){
         $GenreManager = new GenreManager;
         $this->genre = $GenreManager->getGenre($numGenre);
      }

   }

   public function setDescription($description){
      if(is_string($description)){
         $this->description = $description;
      }
   }


 
   

   //GETTER

   public function id(){
      return $this->id;
   }

   public function nom(){
      return $this->nom;
   }

   public function prix(){
      return number_format($this->prix, 2) ;
   }

   public function categ(){
      return $this->categ;
   }

   public function listeCouleurDispo(){
      return $this->listeCouleurDispo;
   }

   public function listeTailleDispo(){
      return $this->listeTailleDispo;
   }

   public function genre(){
      return $this->genre;
   }
   
   public function codeRgbOriginal(){
      return $this->codeRgbOriginal;
   }

   public function motifPosition(){
      return $this->motifPosition;
   }

   public function getTextureDefaut(){

   }

   public function description(){
      return $this->description;
   }



   // function getUrlImg(){
			
   //    $chemin = "public/media/cookies/img$this->id.jpg";
   
    
   //    if ( !file_exists( $chemin) ) {
   //       $chemin= "public/media/cookies/img/default.jpg";
   //    }

   //    return $chemin;
   // }


}

?>