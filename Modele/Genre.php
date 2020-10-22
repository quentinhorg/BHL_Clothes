<?php 

class Genre{
   private  $code;
   private  $libelle;
   private  $listeIdCategorie;
   
   
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
      
         $this->listeCateg =  $listeIdCateg  ;
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

      $CategManager = new CategorieManager ;

      foreach ($this->listeCateg as $categCode) {
         $listObjCateg[] = $CategManager->getCateg($categCode) ;
      }

      return $listObjCateg ;
   }


}

?>