<?php 

class ArticleSession extends Article{
   private VetementManager $VetementManager ;

   public function __construct($idVet, $taille, $qte, $numClr)
   {
      $this->VetementManager = new VetementManager; 
      $dataVet = $this->VetementManager->tabAssocVet($idVet)[0];
      parent::__construct($dataVet);

      $this->setTaille($taille);
      $this->setNumClr($numClr);
      $this->setQte($qte);
   }

   //Setter
   public function setQte($qte){
      parent::setQte($qte);
      $this->setPrixTotalArt($this->qte * $this->prix);
   }

}

?>