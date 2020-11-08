<?php

class ArticleManager extends DataBase{
   
   //Liste de tous les articles d'une commande
   public function getListeArticleByCmd($idCmd){
      //Vérifie si null (null = Commande provisoir -> user non connecté

      $reqArt = "SELECT *, ROUND(ap.qte*v.prix,2) AS 'prixTotalArt' 
               FROM article_panier ap 
               INNER JOIN vetement v ON v.id = ap.idVet
               WHERE numCmd = ? ORDER BY ap.ordreArrivee DESC";
      $this->getBdd();
    
      return $this->getModele("Article", $reqArt, [$idCmd]);
   }

   //Mise à jours d'un article 
   public function updateBDD($idCmd, $idVet, $taille, $idClr, $qte, $ancien){
      $req = "UPDATE article_panier SET taille = ?, numClr = ?, qte = ?
      WHERE numCmd = ? AND idVet = ? AND taille = ? AND numClr = ?";
      $this->getBdd();
      $this->execBDD($req, [ $taille, $idClr, $qte, $idCmd, $idVet, $ancien["taille"], $ancien["numClr"] ]);
   }

   //Insérer un article
   public function inserer($idCmd, $idVet, $idTaille, $qte, $idClr){ 
      $req = "CALL insert_article(?, ?, ?, ?, ?)";
      $this->getBdd();
      $this->execBDD($req, [$idCmd, $idVet, $idTaille, $idClr, $qte]);
   }

   //Diminuer un  article
   public function diminuerQte($idCmd, $idVet, $idTaille, $idClr){
         $req = "CALL insert_article(?, ?, ?, ?, -1)";
         $this->getBdd();
         $this->execBDD($req, [$idCmd, $idVet, $idTaille, $idClr]);
   }


   //Supprimer un article
   public function supprimer($idCmd, $idVet, $idTaille, $idClr){
        $req = "DELETE FROM article_panier WHERE numCmd = ? AND idVet = ? AND taille = ? AND numClr = ?";
        $this->getBdd();
        $this->execBDD($req, [$idCmd, $idVet, $idTaille, $idClr]);

   }


}

?>