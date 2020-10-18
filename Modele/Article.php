<?php 

class Article extends Vetement{
   protected  $Taille ; //Objet
   protected  $qte;
   protected  $Couleur; //Objet
   protected  $prixTotalArt;


   

   public function __construct(array $donneeVetArt)
   {
 
      parent::__construct($donneeVetArt);
    }


   //SETTER
   public function setQte($qte){
      $qte = (int) $qte;

      if($qte < 10 && $qte > 0 ){
         $this->qte = $qte;
      }
      else{  $this->qte = 10 ; }

   }

   public function setNumClr($idCouleur){
      $CouleurManager = new CouleurManager;
      $this->Couleur = $CouleurManager->getCouleur($idCouleur);
   }

   public function setTaille($idTaille){
      $TailleManager = new TailleManager;
      $this->Taille = $TailleManager->getTaille($idTaille);
   }

   public function setPrixTotalArt($prix){
      $prix = (float) $prix;
      
      if($prix > 0 ){
         $this->prixTotalArt = $prix;
      }
    
   }




   //GETTER

   public function qte(){
      return $this->qte;
   }

   public function Couleur(){
      return $this->Couleur;
   }

   public function Taille(){
      return $this->Taille;
   }

   public function prixTotalArt(){
      return number_format($this->prixTotalArt,2);
   }


   //AUTRES MÉTHODE

   public function dispo(){
      $VetementManager = new VetementManager;

      //Retourne VRAI si l'article est disponible dans la BDD ou FAUX si n'est pas disponible par rapport  à la taille et la couleur choisies des attributs de la classe
      return  $VetementManager->verifDisponibiliteTailleCouleur($this->id(), $this->Couleur->num(), $this->Taille->libelle() );
      
   }






 




}

?>