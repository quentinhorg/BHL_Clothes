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

    public function getListeGategForGenre($codeGenre){
        $req = "SELECT DISTINCT c.* 
        FROM genre g 
        INNER JOIN vetement v ON v.codeGenre = g.code
        INNER JOIN categorie c ON c.id = v.idCateg 
        WHERE g.code = ?
        order by c.nom";
        $this->getBdd();
        return $this->getModele($req, [$codeGenre], "Categorie");
    }





}

?>

