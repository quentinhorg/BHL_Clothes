<?php

class GenreManager extends DataBase{
    private $reqBase = "SELECT g.* FROM genre g";
    
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