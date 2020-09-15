<?php 

class Article extends Vetement{
   private  $Taille ; //Objet
   private  $qte;
   private  $Couleur; //Objet

   
   public function __construct(array $donneeVet, $idTaille, $qte, $idCouleur){
      parent::__construct($donneeVet);
   
      $this->setTaille($idTaille);
      $this->qte = $qte;
      $this->setCouleur($idCouleur);
   }


   //SETTER
   public function setQte($qte){
      $qte = (int) $qte;

      if($qte < 10){
         $this->qte = $qte;
      }
      else{  $this->qte = 10 ; }
      
   }

   public function setCouleur($idCouleur){
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