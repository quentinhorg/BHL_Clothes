<?php 

class Vetement{
   private   $id;
   private   $nom;
   public    $prix;
   private   $motifPosition;
   private   $categ; // OBJET
   public    $listeCouleurDispo; // OBJET
   private   $genre; // OBJET
   private   $listeTailleDispo; // OBJET
   private   $description;
   private   $nbAvis;


   public function __construct(array $donnee){
      //var_dump($donnee);
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


   public function setPrix($prix){

         $this->prix = $prix;

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

   public function setListeTailleDispo($listeTaille){

      $listeTaille = explode(",", $listeTaille);
      $TailleManager = new TailleManager;

      foreach ($listeTaille as $taille) {
            $this->listeTailleDispo[] = $TailleManager->getTaille($taille);
      }

   }

   public function setCodeGenre($codeGenre){
      
      if(is_string($codeGenre)){
         $GenreManager = new GenreManager;
         $this->genre = $GenreManager->getGenre($codeGenre);
      }

   }

   public function setDescription($description){
      if(is_string($description)){
         $this->description = $description;
      }
   }

   public function setNbAvis($nbAvis){
      $nbAvis = (int) $nbAvis;
      $this->nbAvis = $nbAvis;

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
   
   public function motifPosition(){
      return $this->motifPosition;
   }

   public function getTextureDefaut(){

   }

   public function description(){
      return $this->description;
   }

   
   public function nbAvis(){
      return $this->nbAvis;
   }



   



}

?>