<?php

class Popup{

   // ATTRIBUTS
    public $titre = "Popup";
    public $Message;

    

   public function setTitre($valeur){
      $this->titre = $valeur;
   }

   //Html possible
   public function setMessage($valeur){
      $this->Message = $valeur;
   }


   public function genererVue(){

      include "vue/vueModal.php";

      if( $this->Message != null ){
         echo "<script> popup(\"".$this->titre."\",\"".str_replace("\n", "", $this->Message)."\", false); </script>";
      }
   }



}



?>