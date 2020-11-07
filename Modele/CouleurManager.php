<?php

class CouleurManager extends DataBase{
    
    public function getCouleur($id){
        $req = "SELECT * FROM vet_couleur WHERE num = ?";
        $this->getBdd();
        return $this->getModele( "Couleur", $req, [$id])[0];

    }

    public function insertBDD($idVet, $nomClr, $filterCssCodeClr, $dispoClr){
        if( empty($filterCssCodeClr)){$filterCssCodeClr= null;}
        
        $this->getBdd();
        $newNum = $this->getNewIdTable('vet_couleur','num');

        $req = "INSERT INTO vet_couleur VALUES(?,?,?,?,?)";
        $this->getBdd();
        $this->execBDD($req, [$newNum, $idVet, $nomClr, $filterCssCodeClr, $dispoClr]);
    }

    public function deleteBDD($numClr){
        $req = "DELETE FROM vet_couleur WHERE num = ?";
        $this->getBdd();
        $this->execBDD($req, [$numClr]);
    }
     public function updateBDD($numClr, $nomClr, $filterCssCodeClr, $dispoClr){
        if( empty($filterCssCodeClr)){$filterCssCodeClr= null;}
        $req = "UPDATE vet_couleur SET nom = ?,filterCssCode=?, dispo =? WHERE num = ?";
        $this->getBdd();
        $this->execBDD($req, [$nomClr, $filterCssCodeClr, $dispoClr, $numClr]);
    }

    public function getListeCouleurForVet($idVet){
        $req = "SELECT * FROM vet_couleur WHERE idVet = ? ORDER BY filterCssCode ASC";
        $this->getBdd();
        return $this->getModele( "Couleur",  $req, [$idVet]);
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