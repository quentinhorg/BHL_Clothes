<?php 

class Commentaire{
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

    public function setIdClient($idClient){
        $idClient = (int) $idClient;
            if($idClient > 0){
                $this->idClient = $idClient;
            }
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
        $this->date = $date;
    }



   //GETTER

    public function id(){
        return $this->id;
    }

    public function idClient(){
        return $this->idClient;
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

    public function date(){
        return $this->date;
    }



}

?>