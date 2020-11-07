<?php

class ContactManager extends DataBase{
    
    public function insertBDDContact(){
        $this->getBdd();
        $newID = $this->getNewIdTable('contact','idContact');
        $req= "INSERT INTO contact VALUES(?, ?, ?, ?, ?, ?, NOW())";
        $this->getBdd();
        $this->execBDD($req,[$newID, $_POST['nom'], $_POST['email'],$_POST['tel'], $_POST['sujet'],$_POST['message']]);
    }

    public function getListeContact(){
        $req= "SELECT * FROM contact";
        $this->getBdd();
        return $this->getModele( "Contact",  $req) ;    
    }

    public function getContact($idContact){
        $req="SELECT * FROM contact WHERE idContact=?";
        $this->getBdd();
        return $this->getModele( "Contact",  $req,[$idContact])[0] ; 
    }


}

?>