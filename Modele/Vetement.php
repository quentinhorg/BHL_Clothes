<?php 

class Vetement{
   private   $id;
   private   $nom;
   public    $prix;
   private   $motifPosition;
   private   $idCateg; 
   public    $listeIdCouleurDispo = array(); 
   private   $codeGenre;
   private   $listeIdTailleDispo = array();
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
         $this->idCateg = $idCateg;
      }

      

   }

   public function setListeIdCouleurDispo($listIdCouleur){
   
      if($listIdCouleur != null){
         $listIdCouleur = explode(",", $listIdCouleur);
         $this->listeIdCouleurDispo = $listIdCouleur;

      }
      
   }

   public function setListeTailleDispo($listeTaille){

      if($listeTaille != null){
         $listeTaille = explode(",", $listeTaille);
         $this->listeIdTailleDispo = $listeTaille ;
      }
      

   }

   public function setCodeGenre($codeGenre){
      
      if(is_string($codeGenre)){
         $this->codeGenre = $codeGenre;
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

   public function Categ(){
      $CategManager = new CategorieManager;
      $Categ = $CategManager->getCateg($this->idCateg);
      return $Categ;
   }

   //Tableau d'object
   public function listeCouleurDispo(){

      
      $CouleurManager = new CouleurManager;

      foreach ($this->listeIdCouleurDispo as $idCouleur) {
         $idCouleur = (int) $idCouleur;

         if($idCouleur > 0){
            $listeCouleurDispo[] = $CouleurManager->getCouleur($idCouleur);
         }
         
      }

      return $listeCouleurDispo;
   }

   public function listeTailleDispo(){
      $TailleManager = new TailleManager;

      foreach ($this->listeIdTailleDispo as $taille) {
            $listeTailleDispo[] = $TailleManager->getTaille($taille);
      }

      return $listeTailleDispo;
   }

   public function Genre(){
      $GenreManager = new GenreManager;
      $Genre = $GenreManager->getGenre($this->codeGenre);
      return $Genre;
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