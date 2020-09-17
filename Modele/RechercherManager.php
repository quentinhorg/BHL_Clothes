<?php

class RechercheManager extends DataBase{
    
    public function getVetementR($id){
        $req = "SELECT * FROM genre WHERE num = ?";
        $this->getBdd();
        return @$this->getModele($req, [$id], "Genre")[0];
    }



}

?>