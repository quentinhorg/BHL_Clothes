<?php 

class CodePostal{
   private  $cp;
   private  $libelle;
   private  $prixLiv;


   
   
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
    public function setCp($cp){
 
        $this->cp = $cp;

    }

    public function setLibelle($libelle){
        if(is_string($libelle)){
            $this->libelle = $libelle;
        }
        
    }

    public function setPrixLiv($prixLiv){
        $prixLiv = (float) $prixLiv;

            if($prixLiv >= 0){
                $this->prixLiv = $prixLiv;
            }
            else{
                $this->prixLiv = 0;
            }
        
    }



   //GETTER

    public function cp(){
        return $this->cp;
    }

    
    public function libelle(){
        return $this->libelle;
    }

    public function prixLiv(){
        return $this->prixLiv;
    }
   




}

?>