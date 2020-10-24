<?php 

class CommandeSession extends Commande{

   private $CommandeManager;
   public $panier = array();


   public function viderPanier(){
      $this->panier = array();
   }
  
   public function __construct(){
      $this->CommandeManager = new CommandeManager;
   }

   public function reloadPrixHT(){
     
      //Prix HT
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

   public function panier(){
      return $this->panier;
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

   public function totalArticle(){
      $totalQte = 0;
      
         foreach ($this->panier() as $article) {
            $totalQte = $totalQte+$article->qte();
         }
     
      return $totalQte;
   }




}

?>