<?php 

class Taille{
   private  $id;
   private  $libelle;
   
   
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

   public function setLibelle($libelle){
      if(is_string($libelle)){
         $this->libelle = $libelle;
      }
   }

 
   

   //GETTER

   public function id(){
      return $this->id;
   }

   public function libelle(){
      return $this->libelle;
   }


}

?>