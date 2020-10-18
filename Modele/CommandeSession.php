<?php 

class CommandeSession extends Commande{

   private $CommandeManager;


   public function viderPanier(){
      $this->panier = array();
   }
  
   public function __construct(){
      $this->CommandeManager = new CommandeManager;
   }

   public function reloadPrixHT(){
     
      $prixPanierHT = $this->CommandeManager->getPrixTotalPanierHT($this->panier());
      parent::setPrixHT($prixPanierHT);
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

      $this->reloadPrixHT();
     

   }

   




   public function supprimerArticle( $idVet, $taille, $numClr){

      $indice = $this->indiceArticlePanier( $idVet, $taille, $numClr);
      unset($this->panier[$indice]) ;
      $this->reloadPrixHT();

   }

   public function diminuerArticle( $idVet, $taille, $numClr){
      $indice = $this->indiceArticlePanier( $idVet, $taille, $numClr);
      $qteActuelle = $this->panier[$indice]->qte();
      $this->panier[$indice]->setQte($qteActuelle-1) ;
      $this->reloadPrixHT();

   }




}

?>