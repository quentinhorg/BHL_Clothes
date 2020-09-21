<?php

class TailleManager extends DataBase{
    
    public function getTaille($libelle){
        $req = "SELECT * FROM taille WHERE libelle = ?";
        $this->getBdd();
        return $this->getModele($req, [$libelle], "Taille")[0]; // Si une valeur
    }

    public function getListeTailleByCateg($idCateg){
        var_dump($idCateg);
        $req = "SELECT t.* FROM taille t
        WHERE t.type LIKE (SELECT typeTaille c FROM categorie c WHERE c.id = ?)";
        $this->getBdd();
        return $this->getModele($req,[$idCateg],"Taille");
    }


}

?>