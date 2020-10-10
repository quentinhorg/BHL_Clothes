<?php 

class ArticleSession extends Article{


   public function __construct($idVet, $taille, $qte, $numClr)
   {
      $ArticleManager = new ArticleManager; 
      $dataVet = $ArticleManager->getDataVetAssoc($idVet)[0];
      parent::__construct($dataVet);

      $this->setTaille($taille);
      $this->setNumClr($numClr);
      $this->setQte($qte);
      
   }

   public function setQte($qte){
      parent::setQte($qte);
      $this->setPrixTotalArt($this->qte * $this->prix);
   }








 




}

?>