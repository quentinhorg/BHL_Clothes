<?php 

class Article extends Vetement{
   private  $Taille ; //Objet
   private  $qte;
   private  $Couleur; //Objet

   

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



}

?>