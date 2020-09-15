<?php 

class Genre{
   private  $num;
   private  $libelle;
   private  $genre;
   private  $listeCateg; //Objet
   
   
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

   public function setLibelle($libelle){
      if(is_string($libelle)){
         $this->libelle = $libelle;
      }
   }

   public function setGenre($genre){
      if(is_string($genre)){
         $this->genre = $genre;
      }
   }

   public function setListeIdCategorie($listeIdCateg){
      
      
      if($listeIdCateg != null ){
         $listeIdCateg = explode(",", $listeIdCateg);
         $categManager = new CategorieManager ;
         foreach ($listeIdCateg as $categId) {
           
            $this->listeCateg[] = $categManager->getCateg($categId) ;
         }
      }
      

   }
 
   

   //GETTER
   public function num(){
      return $this->num;
   }

   public function genre(){
      return $this->genre;
   }
   public function libelle(){
      return $this->libelle;
   }

   public function listeCateg(){
      return $this->listeCateg;
   }


}

?>