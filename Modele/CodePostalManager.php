<?php

class CodePostalManager extends DataBase{

   private $reqBase = "SELECT * FROM code_postal c";

   public function getCp($id){
      $req = $this->reqBase." WHERE c.cp = ?";
      $this->getBdd();
      return @$this->getModele( "CodePostal", $req, [$id])[0];
  }

   public function getListCp(){
      $req = $this->reqBase;
      $this->getBdd();
      return $this->getModele("CodePostal", $req);
   }


}

?>