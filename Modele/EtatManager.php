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

    public function getListClassIconEtat(){
    
        $listeIcone= array(
            "1" => "pe-7s-clock",
            "2" =>"pe-7s-config",
            "3" => "pe-7s-box2",
            "4" => "pe-7s-car",
            "5" => "pe-7s-home"
        );

        return $listeIcone;
    }


}

?>