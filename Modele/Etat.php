<?php 

class Etat{
   private  $id;
   private  $libelle;
   private  $description;


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
   public function setId($id){
      $id = (int) $id;

      if($id > 0){
         $this->id = $id;
      }
   }

   public function setlibelle($libelle){
      if(is_string($libelle)){
         $this->libelle = $libelle;
      }
   }


   public function setDescription($description){

         $this->description = $description;

  
   
   }

 
   

   //GETTER

   public function id(){
      return $this->id;
   }

   public function libelle(){
      return $this->libelle;
   }


   public function description(){
      //return $this->dispo;
      return $this->description;
   }


   



}

?>