<?php 

class Article extends Vetement{
   protected  $taille ;
   protected  $qte;
   protected  $numClr; 
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

   public function setNumClr($num){
      $this->numClr = $num;
   }

   public function setTaille($idTaille){
     $this->taille = $idTaille;
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
      $CouleurManager = new CouleurManager;
      $Couleur = $CouleurManager->getCouleur($this->numClr);
      return $Couleur ;
   }

   public function Taille(){
      $TailleManager = new TailleManager;
      $Taille = $TailleManager->getTaille($this->taille);
      return $Taille;
   }

   public function prixTotalArt(){
      return number_format($this->prixTotalArt,2);
   }


   //AUTRES MÉTHODE

   public function dispo(){
      $VetementManager = new VetementManager;
      //Retourne VRAI si l'article est disponible dans la BDD ou FAUX si n'est pas disponible par rapport  à la taille et la couleur choisies des attributs de la classe
      return  $VetementManager->verifDisponibiliteTailleCouleur($this->id(), $this->Couleur()->num(), $this->Taille()->libelle() );
      
   }






 




}

?>