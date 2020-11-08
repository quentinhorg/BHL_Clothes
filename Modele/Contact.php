<?php 


class Contact{
   private  $idContact;
   private  $idClient;
   private  $nom;
   private  $email;
   private  $numero;
   private  $sujet;
   private  $message;
   private  $date;

   
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
        $idContact = (int) $idContact;
        if($idContact > 0){
            $this->idContact = $idContact;
        }
    }

    public function setIdClient($idClient){
        $idClient = (int) $idClient;
        if($idClient > 0){
            $this->idClient = $idClient;
        }    }

    public function setNom($nom){
        if(is_string($nom)){
            $this->nom = $nom ;
        }
    }

    public function setEmail($email){
        if(is_string($email)){
            $this->email = $email; 
        }     
    }

    public function setNumero($numero){
        $this->numero = $numero;   
    }

   public function setSujet($sujet){
        if(is_string($sujet)){
            $this->sujet = $sujet;
        }
    }

    public function setMessage($message){
            $this->message = $message;
    }

    public function setDate($date){

        if(is_string($date)){
            $this->date = new DateTime($date);;
        } 
    }



   //GETTER

    public function idContact(){
        return $this->idContact;
    }

    public function idClient(){
        return $this->idClient;
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

    public function listeReponse(){
        $ContactManager = new ContactManager;
        return $ContactManager->getReponse($this->idContact);
    }


    public function date($format){
  
        return date_format($this->date, $format); //Format date choisis en paramètre
    }
    public function Client(){
        
        if($this->idClient != null){
            $ClientManager = new ClientManager;
            return  $ClientManager->getClient($this->idClient) ;
        }else{ return null ;}
       
      
    }
}

?>