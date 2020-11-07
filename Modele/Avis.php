<?php 


class Avis{
   private  $id;
   private  $idClient;
   private  $idVet;
   private  $commentaire;
   private  $note;
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
    public function setId($id){
        $this->id = $id;
    }

    public function setIdClient($idClient){
        $this->idClient = $idClient ;
    }

    public function setIdVet($idVet){
        $idVet = (int) $idVet;
            if($idVet > 0){
                $this->idVet = $idVet;
            }
    }

    public function setCommentaire($commentaire){
        if(is_string($commentaire)){
            $this->commentaire = $commentaire;
        }
        
    }

   public function setNote($note){
        if(is_string($note)){
            $this->note = $note;
        }
    }

    public function setDate($date){

        if(is_string($date)){
            $this->date = $date;
        }
       
    }



   //GETTER

    public function id(){
        return $this->id;
    }

    public function Client(){
        $ClientManager = new ClientManager;
        $Client = $ClientManager->getClient($this->idClient);
        return $Client; // Objet Client
    }

    public function idVet(){
        return $this->idVet;
    }

    public function commentaire(){
        return $this->commentaire;
    }
   
    public function note(){
        return $this->note;
    }

    public function date($format){
        $date= new DateTime($this->date);
        return date_format($date, $format); //Format date choisis en paramètre
    }



}

?>