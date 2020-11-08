<?php 


class ContactReponse{
    private  $idContact;
    private  $reponse ;
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


    public function setReponse($reponse){
        if(is_string($reponse)){
            $this->reponse = $reponse; 
        }     
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

   
    public function reponse(){
        return $this->reponse;
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