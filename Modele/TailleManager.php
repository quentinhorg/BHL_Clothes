<?php

class TailleManager extends DataBase{
    
    public function getTaille($id){
        $req = "SELECT * FROM taille WHERE id = ?";
        $this->getBdd();
        return $this->getModele($req, [$id], "Taille")[0]; // Si une valeur
    }

    public function getListeTaille(){
        $req = "SELECT * FROM taille";
        $this->getBdd();
        return $this->getModele($req,["*"],"Taille");
    }


}

?>