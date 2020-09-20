<?php

class TailleManager extends DataBase{
    
    public function getTaille($libelle){
        $req = "SELECT * FROM taille WHERE libelle = ?";
        $this->getBdd();
        return $this->getModele($req, [$libelle], "Taille")[0]; // Si une valeur
    }

    public function getListeTaille(){
        $req = "SELECT * FROM taille";
        $this->getBdd();
        return $this->getModele($req,["*"],"Taille");
    }


}

?>