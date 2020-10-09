<?php 

class CommandeSession extends Commande{
  
   public function __construct(){
 
   }


   public function ajouterPanier( $idVet, $taille, $qte, $idClr){
      $possedeDeja = false ;

      $ArticleManager = new ArticleManager;

      $nouvelArticle = $ArticleManager->tranformArticle($idVet) ;
      $nouvelArticle->setTaille($taille);
      $nouvelArticle->setQte($qte);
      $nouvelArticle->setNumClr($idClr);

      foreach ($this->panier as $article) {
         
          //Vérifie si les caractéritique de l'article à ajouté se trouve déjà dans le panier
         if( 
            $article->id() == $nouvelArticle->id()
            && $article->Taille()->libelle() == $nouvelArticle->Taille()->libelle()
            && $article->Couleur()->num() == $nouvelArticle->Couleur()->num()
         ){
            //Faire la somme des quantités si même caractéritique
            $totalQte = $article->qte() + $nouvelArticle->qte() ;
            $article->setQte($totalQte);
            $possedeDeja = true; 
            break;
         }

      }

      if($possedeDeja == false){
         $this->panier[] = $nouvelArticle;
      }

   }

   
   public function indiceArticlePanier( $idVet, $taille, $numClr){
      $indicePanier = null;

      foreach ($this->panier as $indice => $article) {
         
        if( 
           $article->id() == $idVet
           && $article->Taille()->libelle() ==  $taille
           && $article->Couleur()->num() == $numClr
        ){
     
           $indicePanier = $indice; 
           break;
        }

     }
     
     return $indicePanier;
   }



   public function supprimerArticle( $idVet, $taille, $numClr){

      $indice = $this->indiceArticlePanier( $idVet, $taille, $numClr);
      unset($this->panier[$indice]) ;

   }




}

?>