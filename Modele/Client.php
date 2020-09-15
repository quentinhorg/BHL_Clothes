<?php 

class Client{
   private  $id;
   private  $nom;
   private $prenom;
   private $adresse;
   private $tel;


   
   
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

   public function setNom($nom){
        if(is_string($nom)){
            $this->nom = $nom;
        }
    }

    public function setPrenom($prenom){
        if(is_string($prenom)){
            $this->prenomnom = $prenomnom;
        }
    }

    public function setAdresse($adresse){
        if(is_string($adresse)){
            $this->adresse = $adresse;
        }
    }

    
    public function setTel($tel){
        $tel = (int) $tel;
            if($tel > 0){
                $this->tel = $tel;
            }
    }
    

 
   

   //GETTER

    public function getId(){
        return $this->id;
    }

    public function getNom(){
        return $this->nom;
    }

    public function getPrenom(){
        return $this->prenom;
    }

    public function getAdresse(){
        return $this->adresse;
    }

    public function getTel(){
        return $this->tel;
    }
   



}

?>