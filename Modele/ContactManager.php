<?php

class ContactManager extends DataBase{
    
    public function insertBDDContact(){
        $this->getBdd();
        $newID = $this->getNewIdTable('contact','idContact');
        $req= "INSERT INTO contact VALUES(?, ?, ?, ?, ?, ?)";
        $this->getBdd();
        $this->execBDD($req,[$newID, $_POST['nom'], $_POST['email'],$_POST['tel'], $_POST['sujet'],$_POST['message']]);

        
    }





}

?>