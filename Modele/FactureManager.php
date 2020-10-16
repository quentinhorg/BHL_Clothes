<?php

class FactureManager extends DataBase{

    private $reqBase = "SELECT * FROM facture f";
    
    public function getFacture($numCmd){
      
        $req = $this->reqBase." WHERE f.numCmd = ?";
        $this->getBdd();

        return @$this->getModele($req, [$numCmd], "Facture")[0];
    }

    public function getListeFacture(){
        $req = $this->reqBase;
        $this->getBdd();
        return $this->getModele($req, ["*"], "Facture");
    }


}

?>