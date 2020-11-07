<?php

class CategorieManager extends DataBase{
    
    public function getCateg($id){
        
        $req = "SELECT * FROM categorie WHERE id = ?";
        $this->getBdd();
        return @$this->getModele("Categorie", $req, [$id])[0];

    }

    public function getListeCateg(){
        $req = "SELECT * FROM categorie";
        $this->getBdd();
        return $this->getModele("Categorie", $req);
    }

    public function getListeGategForGenre($codeGenre){
        $req = "SELECT DISTINCT c.* 
        FROM genre g 
        INNER JOIN vetement v ON v.codeGenre = g.code
        INNER JOIN categorie c ON c.id = v.idCateg 
        WHERE g.code = ?
        order by c.nom";
        $this->getBdd();
        return $this->getModele( "Categorie",  $req,[$codeGenre]);
    }





}

?>

