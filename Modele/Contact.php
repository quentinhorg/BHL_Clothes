<?php 


class Contact{
   private  $idContact;
   private  $nom;
   private  $email;
   private  $numero;
   private  $sujet;
   private  $message;

   
   public function __construct(array $donnee){
      $this->hydrate($donnee);
   }

   
   //HYDRATATION
   public function hydrate(array $donnee){
      foreach($donnee as $cle => $valeur){
        $methode = 'set'.ucfirst($cle);
       
        if(method_exists($this, $methode)){
            $this->$methode(htmlspecialchars($valeur));
        }
      }
   }


   
   //SETTER
    public function setIdContact($idContact){
        $this->idContact = $idContact;
    }

    public function setNom($nom){
        $this->nom = $nom ;
    }

    public function setEmail($email){
        $this->email = $email;      
    }

    public function setNumero($numero){
        $this->numero = $numero;   
    }

   public function setSujet($sujet){
            $this->sujet = $sujet;
    }

    public function setMessage($message){
            $this->message = $message;
    }



   //GETTER

    public function idContact(){
        return $this->idContact;
    }

    public function nom(){
        return $this->nom;
    }

    public function email(){
        return $this->email;
    }
   
    public function numero(){
        return $this->numero;
    }

    public function sujet(){
        return $this->sujet;
    }

    public function message(){
        return $this->message;
    }

}

?>