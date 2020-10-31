<?php 

class Vetement{
   private   $id;
   private   $nom;
   public    $prix;
   private   $motifPosition;
   private   $idCateg; 
   private   $codeGenre;
   private   $description;
   private   $nbAvis;


   public function __construct(array $donnee){
      //var_dump($donnee);
      foreach($donnee as $cle => $valeur){
         $methode = 'set'.ucfirst($cle);
      
         if(method_exists($this, $methode)){
            $this->$methode(htmlspecialchars($valeur));
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
   public function listeCouleur(){
      $CouleurManager = new CouleurManager;
      return $CouleurManager->getListeCouleurForVet($this->id);
   }

   public function listeTailleDispo(){
      $TailleManager = new TailleManager;
      return $TailleManager->getListeTailleForVet($this->id);
   }

   //Retourne une liste d'object couleur disponible pour le veteemnt conerné
   public function listeCouleurDispo(){
      $listeCouleurDispo = array();
      foreach ($this->listeCouleur() as $couleur) {
         if( $couleur->dispo() == 1 ){
            $listeCouleurDispo[] = $couleur;
         }
      }
      return $listeCouleurDispo;
   }

   public function Genre(){
      $GenreManager = new GenreManager;
      $Genre = $GenreManager->getGenre($this->codeGenre);
      return $Genre;
   }
   
   public function motifPosition(){
      return $this->motifPosition;
   }


   public function description(){
      return $this->description;
   }

   
   public function nbAvis(){
      return $this->nbAvis;
   }

   //AUTRES MÉTHODES
   public function dispoPourVendre(){
      $peutVendre = true ;
      if( $this->listeTailleDispo() == null ){
         $peutVendre = false ;
      }
      if( $this->listeCouleurDispo() == null ){
         $peutVendre = false ;
      }
      return $peutVendre;
   }

   public function possedeTaille($taille){
      $possedeTaille=false;

      foreach ($this->listeTailleDispo() as $tailleDispo) {
         if ($taille == $tailleDispo->libelle()) {
            $possedeTaille= true;
            break;
         }
      return $possedeTaille;
   }
   }

   
   public function vueMotif(Couleur $Couleur){
      $checked = null;
      $idMotif = "Vet".$this->id."_motif_Clr".$Couleur->num();
      
      if($this->listeCouleurDispo()[0]->num() == $Couleur->num() ){ $checked = "checked" ;}

      echo "<div class='motifVet'>";
      echo "<label for='$idMotif' style='background-image:url(public/media/vetement/id$this->id.jpg); filter: ".$Couleur->filterCssCode()." ; $this->motifPosition'></label>" ;
      echo "<input id='$idMotif' name='numClr' $checked style='display:none' value='".$Couleur->num()."' type='radio' >";
      echo "</div>";
   }




   



   



}

?>