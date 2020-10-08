<?php

class ArticleManager extends DataBase{


   public function getListeArticleByCmd($idCmd){
      //Vérifie si null (null = Commande provisoir -> user non connecté

      $reqArt = "SELECT * FROM article_panier ap INNER JOIN vetement v ON v.id = ap.idVet WHERE numCmd = ? ORDER BY ap.ordreArrivee DESC";
      $this->getBdd();
    
      return $this->getModele($reqArt, [$idCmd], "Article");
   }


   public function tranformArticle($idVet){
         $reqVet = "SELECT * FROM vetement WHERE id = ?";
         $this->getBdd();
         $article =$this->getModele($reqVet, [$idVet], "Article")[0];

         return $article ;
   }



   public function inserer($idCmd, $idVet, $idTaille, $qte, $idClr){
         
         $req = "CALL insert_article(?, ?, ?, ?, ?)";
         $this->getBdd();
         $this->execBDD($req, [$idCmd, $idVet, $idTaille, $idClr, $qte]);
  

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