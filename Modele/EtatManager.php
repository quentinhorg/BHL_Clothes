<?php

class EtatManager extends DataBase{
    private $reqBase = "SELECT * FROM etat e";
    
    public function getEtat($id){
        $req = $this->reqBase." WHERE id = ?";
        $this->getBdd();
        return @$this->getModele($req, [$id], "Etat")[0];
    }

    public function getListeEtatSuivi(){
        $req = $this->reqBase." WHERE id != 1";
        $this->getBdd();
        return $this->getModele($req, ["*"], "Etat");
    }




}

?>