<?php

class CouleurManager extends DataBase{
    
    public function getCouleur($id){
        $req = "SELECT * FROM vet_couleur WHERE num = ?";
        $this->getBdd();
        return $this->getModele($req, [$id], "Couleur")[0];

    }

    public function insertBDD($idVet, $nomClr, $filterCssCodeClr, $dispoClr){
        $this->getBdd();
        $newNum = $this->getNewIdTable('vet_couleur','num');

        $req = "INSERT INTO vet_couleur VALUES(?,?,?,?,?)";
        $this->getBdd();
        $this->execBDD($req, [$newNum, $idVet, $nomClr, $filterCssCodeClr, $dispoClr]);
    }

    public function getListeCouleurForVet($idVet){
        $req = "SELECT * FROM vet_couleur WHERE idVet = ?";
        $this->getBdd();
        return $this->getModele($req, [$idVet], "Couleur");
    }

    public function getPrincipaleCouleur(){
        $listeClr = [
            "Rouge",
             "Bleu", 
             "Jaune", 
             "Noir", 
             "Blanc", 
             "Vert", 
             "Rose" ,
             "Orange", 
             "Gris", 
             "Mauve" 
        ] ;

        return $listeClr;

    }





}

?>