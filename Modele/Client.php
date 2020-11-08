<?php 

class Client{
   private  $id;
   private  $email;
   private  $mdp;
   private  $nom;
   private  $prenom;
   private  $rue;
   private  $tel;
   private  $solde;
   private  $codePostal;
   private  $active;
   private  $dateInscription;


   
   //CONSTRUCTEUR
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
        $id = (int) $id;
            if($id > 0){
                $this->id = $id;
            }
    }

    public function setEmail($email){
        if(is_string($email)){
            $this->email = $email;
        }
        
    }

    public function setMdp($mdp){
        if(is_string($mdp)){
            $this->mdp = $mdp;
        }
        
    }

   public function setNom($nom){
        if(is_string($nom)){
            $this->nom = $nom;
        }
    }

    public function setPrenom($prenom){
        if(is_string($prenom)){
            $this->prenom = $prenom;
        }
    }

    public function setRue($rue){
        if(is_string($rue)){
            $this->rue = $rue;
        }
    }

    public function setCodePostal($CodePostal){
        if(is_string($CodePostal)){
          $this->codePostal = $CodePostal ;
        }
    }

    
    public function setTel($tel){
        $tel = (int) $tel;
            if($tel > 0){
                $this->tel = $tel;
            }
    }
    public function setSolde($solde){
        $solde = (float) $solde;
            if($solde > 0){
                $this->solde = $solde;
            }
    }


    public function setActive($active){

        $this->active = $active;
    }

    public function setDateInscription($dateInscription){
        if($dateInscription != null){
            $this->dateInscription = new DateTime($dateInscription);
        }
    }


   //GETTER
    public function id(){
        return $this->id;
    }

    
    public function email(){
        return $this->email;
    }

    public function mdp(){
        return $this->mdp;
    }
   

    public function nom(){
        return ucfirst($this->nom);
    }

    public function prenom(){
        return ucfirst($this->prenom);
    }

    public function rue(){
        return $this->rue;
    }

    public function CodePostal(){
        $CodePostalManager = new CodePostalManager;   
        $CodePostal =  $CodePostalManager->getCp($this->codePostal);
        return $CodePostal;
    }

    public function tel(){
        return $this->tel;
    }

    //Tableau d'objet
    public function listCmd(){
        $listeCmd = array();
        $CommandeManager = new CommandeManager();
       return $CommandeManager->getListCommandeForClient($this->id);
    }

    //Tableau d'objet
    public function listCmdPaye(){
        $listeCmdPaye = array();
        $listeCommandeClient = $this->listCmd() ;

        foreach ($listeCommandeClient as $cmd){
            if($cmd->Etat()->id() != 1 ){
                $listeCmdPaye[]= $cmd ;
            }
            
        }

       return $listeCmdPaye;
    }

    public function solde(){
        return $this->solde;
    }

    public function active(){
        return $this->active;
    }

    public function dateInscription($format){
        $dateFormat = null ;
        if( $this->dateInscription != null){
           $dateFormat = date_format($this->dateInscription, $format) ;
        }
        return $dateFormat;
    }



    // AUTRE METHODE

    //Verifie si le compte à été activé
    public function compteActive(){
        $active= true;
        if ($this->active == 0) {
            $active= false;
        }
        return $active;
    }




}

?>