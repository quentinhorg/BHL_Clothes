<?php

class GenreManager extends DataBase{
    
    public function getGenre($id){
        $req = "SELECT * FROM genre WHERE num = ?";
        $this->getBdd();
        return @$this->getModele($req, [$id], "Genre")[0];
    }

    public function getListeGenre(){
        $req = "SELECT * FROM vue_categpargenre";
        $this->getBdd();
        return $this->getModele($req, ["*"], "Genre");

    }


}

?>