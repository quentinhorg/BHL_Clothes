<?php

class CommentaireManager extends DataBase{

    // afficher les commentaires selon le vêtement
    public function getListeCommentaire($id){

        $req="SELECT * FROM commentaire WHERE idVet=?";
        $this->getBdd();
        $commentaire= $this->getModele($req, [$id], "Commentaire");

        return $commentaire;
    }


    public function insertCommentaire($idVet, $idClient){
        $this->getBdd(); //Autoriser l'access a la BDD
        $newID = $this->getNewIdTable('commentaire','id');

        $req= "INSERT INTO commentaire VALUES (?,?,?,?,?, NOW() ) ";

        $this->getBdd();
        $this->execBdd($req, [$newID, $idClient, $idVet, $_POST['commentaire'], $_POST['note'] ]);
    }
}