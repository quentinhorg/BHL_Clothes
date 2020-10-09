<?php

class CategorieManager extends DataBase{
    
    public function getCateg($id){
        
        $req = "SELECT * FROM categorie WHERE id = ?";
        $this->getBdd();
        return @$this->getModele($req, [$id], "Categorie")[0];

    }

    public function getListeCateg(){
        $req = "SELECT * FROM categorie";
        $this->getBdd();
        return $this->getModele($req, ["*"], "Categorie");
    }





}

?>