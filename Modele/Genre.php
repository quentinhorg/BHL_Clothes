<?php 

class Genre{
   private  $code;
   private  $libelle;
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

   public function setLibelle($libelle){
      if(is_string($libelle)){
         $this->libelle = $libelle;
      }
   }

   public function setCode($code){
      if(is_string($code)){
         $this->code = $code;
      }
   }

   public function setListeIdCategorie($listeIdCateg){
      
      if($listeIdCateg != null ){
         $listeIdCateg = explode(",", $listeIdCateg);
         $categManager = new CategorieManager ;
         foreach ($listeIdCateg as $categcode) {

            $this->listeCateg[] = $categManager->getCateg($categcode) ;
         }
      }

   }
 
   

   //GETTER
   public function num(){
      return $this->num;
   }

   public function code(){
      return $this->code;
   }
   public function libelle(){
      return $this->libelle;
   }

   public function listeCateg(){
      return $this->listeCateg;
   }


}

?>