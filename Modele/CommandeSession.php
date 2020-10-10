<?php 

class CommandeSession extends Commande{
  
   public function __construct(){
 
   }





   public function ajouterPanier( ArticleSession $ArticleSession ){

      
      $indiceArticle = $this->indiceArticlePanier($ArticleSession->id(), $ArticleSession->Taille()->libelle(), $ArticleSession->Couleur()->num() ) ;
  
      if($indiceArticle !== null ){
         $totalQte = $this->panier[$indiceArticle]->qte() + $ArticleSession->qte() ;
         $this->panier[$indiceArticle]->setQte($totalQte);
      }
      else{
         $this->panier[] = $ArticleSession;
      }

     

   }

   




   public function supprimerArticle( $idVet, $taille, $numClr){

      $indice = $this->indiceArticlePanier( $idVet, $taille, $numClr);
      unset($this->panier[$indice]) ;

   }

   public function diminuerArticle( $idVet, $taille, $numClr){
      $indice = $this->indiceArticlePanier( $idVet, $taille, $numClr);
      $qteActuelle = $this->panier[$indice]->qte();
      $this->panier[$indice]->setQte($qteActuelle-1) ;

   }




}

?>