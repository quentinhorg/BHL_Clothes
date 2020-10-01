<?php

class ArticleManager extends DataBase{

   public function getListeArticleBDD(){
      $req = "SELECT * FROM categorie WHERE id = ?";
      $this->getBdd();
      
      return $this->getModele($req, [$id], "Article");
   }

   public function getListeArticleByCmd($idCmd){
      //Vérifie si null (null = Commande provisoir -> user non connecté)
      $listArticle = array() ;
      if($idCmd != null){
        
         $reqArt = "SELECT * FROM article_panier WHERE numCmd = ?";
         $this->getBdd();
         $donneeArt = $this->execBDD($reqArt, [$idCmd]);


         foreach ($donneeArt as $article) {
            $reqVet = "SELECT * FROM vetement WHERE id = ?";
            $this->getBdd();
            $donneeVet = $this->execBDD($reqVet, [$article["idVet"]])[0];
            $listArticle[] = new Article($donneeVet, $article["taille"], $article["qte"], $article["numClr"]);
         }
        
      }
    
      return $listArticle;
   }

   public function insertListeArticle($idCmd, $listeArticleObj){
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
          $req = "INSERT INTO article_panier VALUES(?,?,?,?,?)";
          $this->getBdd();
          $this->execBDD($req, [$idCmd, $idVet, $idTaille, $idClr, $qte]);
      }
      else{
         $reqVet = "SELECT * FROM vetement WHERE id = ?";
         $this->getBdd();
         
         $donneeVet = $this->execBDD($reqVet, [$idVet])[0];

         $nouvelArticle = new Article($donneeVet, $idTaille, $qte, $idClr);
         $_SESSION["ma_commande"]->ajouterPanier($nouvelArticle) ;
      }

   }

   public function supprimer($idCmd, $idVet, $idTaille, $idClr){

      if( !isset($_SESSION["ma_commande"]) ){
         //  $req = "DELETE FROM";
         //  $this->getBdd();
         //  $this->execBDD($req, [$numCmdBDD]);
      }
      else{
         // $reqVet = "SELECT * FROM vetement WHERE id = ?";
         // $this->getBdd();
         
         // $donneeVet = $this->execBDD($reqVet, [$idVet])[0];

         // $nouvelArticle = new Article($donneeVet, $idTaille, $qte, $idClr);
         // $_SESSION["ma_commande"]->ajouterPanier($nouvelArticle) ;
      }

   }






}

?>