<?php

class AvisManager extends DataBase{

    // afficher les aviss selon le vêtement
    public function getListeAvis($id){

        $req="SELECT * FROM avis WHERE idVet=? ORDER BY date DESC";
        $this->getBdd();
        $avis= $this->getModele("Avis", $req,  [$id]);

        return $avis;
    }


    public function insertAvis($idVet, $idClient){
        $this->getBdd(); //Autoriser l'access a la BDD
        $newID = $this->getNewIdTable('avis','id');

        $req= "INSERT INTO avis VALUES (?,?,?,?,?, NOW() ) ";

        $this->getBdd();
        $this->execBdd($req, [$newID, $idClient, $idVet, $_POST['avis'], $_POST['note'] ]);
    }

   
    
}