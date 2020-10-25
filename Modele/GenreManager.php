<?php

class GenreManager extends DataBase{
    private $reqBase = "SELECT * FROM vue_categpargenre vcg 
    RIGHT JOIN genre g ON vcg.codeGenre = g.code";
    
    public function getGenre($code){
        $req = $this->reqBase." WHERE g.code LIKE ?";
        $this->getBdd();
        return @$this->getModele($req, [$code], "Genre")[0];
    }

    public function getListeGenre(){
        $req = $this->reqBase;
        $this->getBdd();
        return $this->getModele($req, ["*"], "Genre");
    }


}

?>