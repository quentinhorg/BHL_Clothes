<?php

class GenreManager extends DataBase{
    
    public function getGenre($code){
        $req = "SELECT * FROM genre WHERE num = ?";
        $this->getBdd();
        return @$this->getModele($req, [$code], "Genre")[0];
    }

    public function getListeGenre(){
        $req = "SELECT * FROM vue_categpargenre vcg 
        INNER JOIN genre g ON vcg.codeGenre = g.code";
        $this->getBdd();
        return $this->getModele($req, ["*"], "Genre");
    }


}

?>