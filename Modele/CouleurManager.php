<?php

class CouleurManager extends DataBase{
    
    public function getCouleur($id){
        $req = "SELECT * FROM vet_couleur WHERE num = ?";
        $this->getBdd();
        return $this->getModele($req, [$id], "Couleur")[0];

    }





}

?>