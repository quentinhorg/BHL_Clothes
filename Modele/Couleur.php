<?php 

class Couleur{
   private  $num;
   private  $nom;
   private  $idVet;
   private  $filterCssCode;
   private  $dispo;
   





   public function __construct(array $donnee){
     
      $this->hydrate($donnee);
   }


   //HYDRATATION
   public function hydrate(array $donnee){
      foreach($donnee as $cle => $valeur){
         $methode = 'set'.ucfirst($cle);
      
         if(method_exists($this, $methode)){
            $this->$methode($valeur);
         }
      }
   }


   
   //SETTER
   public function setNum($num){
      $num = (int) $num;

      if($num > 0){
         $this->num = $num;
      }
   }

   public function setNom($nom){
     
      if(is_string($nom)){
         $this->nom = $nom;
      }

   }

   public function setFilterCssCode($filterCssCode){
      if(is_string($filterCssCode)){
         $this->filterCssCode = $filterCssCode;
      }
   }

   public function setDispo($dispo){
      $dispo = (int) $dispo;
      if($dispo == 1 || $dispo == 0){
         $this->dispo = $dispo;
      }
      else{
         $this->dispo = 0;
      }
   }

   public function setIdVet($idVet){
      $idVet = (int) $idVet;

      if($idVet > 0){
         $this->idVet = $idVet;
      }
  
   
   }

 
   

   //GETTER

   public function num(){
      return $this->num;
   }

   public function nom(){
      return $this->nom;
   }

   public function filterCssCode(){
      return "filter: ".$this->filterCssCode;
   }

   public function Vetement(){
      return $this->Vetement;
   }

   public function dispo(){
      return $this->dispo;
   }


   



}

?>