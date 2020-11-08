<?php

class CategorieManager extends DataBase{
    
    //Obtention d'un objet Catégorie
    public function getCateg($id){
        $req = "SELECT * FROM categorie WHERE id = ?";
        $this->getBdd();
        return @$this->getModele("Categorie", $req, [$id])[0];
    }

    //Liste d'objets Catégorie
    public function getListeCateg(){
        $req = "SELECT * FROM categorie";
        $this->getBdd();
        return $this->getModele("Categorie", $req);
    }

    //Liste d'objet Catégorie d'un Genre
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

