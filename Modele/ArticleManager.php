<?php

class ArticleManager extends DataBase{

   public function getListeArticleBDD(){
      $req = "SELECT * FROM categorie WHERE id = ?";
      $this->getBdd();
      
      return $this->getModele($req, [$id], "Article");
   }

   public function insertListeArticle($idCmd, $listeArticleObj){
      var_dump($listeArticleObj) ;
      foreach ($listeArticleObj as $article) {
          $req = "INSERT INTO article_panier VALUES(?,?,?,?,?)";
          $this->getBdd();
          $this->execBDD($req, [$idCmd, 
            $article->id(), 
            $article->Taille()->libelle(), 
            $article->Couleur()->num(),
            $article->qte()
         ]);
      }
   }

   public function inserer($idCmd, $idVet, $idTaille, $qte, $idClr){

      if( !isset($_SESSION["ma_commande"]) ){
         //  $req = "INSERT INTO";
         //  $this->getBdd();
         //  $this->execBDD($req, [$numCmdBDD]);
      }
      else{
         $reqVet = "SELECT * FROM vetement WHERE id = ?";
         $this->getBdd();
         
         $donneeVet = $this->execBDD($reqVet, [$idVet])[0];

         $nouvelArticle = new Article($donneeVet, $idTaille, $qte, $idClr);
         $_SESSION["ma_commande"]->ajouterPanier($nouvelArticle) ;
      }

   }

   public function supprimer($idCmd, $idVet, $idTaille, $qte, $idClr){

      if( !isset($_SESSION["ma_commande"]) ){
         //  $req = "DELETE FROM";
         //  $this->getBdd();
         //  $this->execBDD($req, [$numCmdBDD]);
      }
      else{
         $reqVet = "SELECT * FROM vetement WHERE id = ?";
         $this->getBdd();
         
         $donneeVet = $this->execBDD($reqVet, [$idVet])[0];

         $nouvelArticle = new Article($donneeVet, $idTaille, $qte, $idClr);
         $_SESSION["ma_commande"]->ajouterPanier($nouvelArticle) ;
      }

   }






}

?>