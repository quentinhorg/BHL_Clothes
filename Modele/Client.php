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
   private  $listeIdCmd = array();
   private  $codePostal;
   private  $active;
   private  $dateInscription;


   
   
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

    public function setListeIdCmd($listeIdCmd){

        if($listeIdCmd != null){
            $tabIdCmd = explode(",",$listeIdCmd);
            $this->listeIdCmd = $tabIdCmd ;
        }
        
    }

    public function setActive($active){

        $this->active = $active;
    }

    public function setDateInscription($dateInscription){

        $this->dateInscription = $dateInscription;
    }


   //GETTER
    public function getId(){
        return $this->id;
    }

    
    public function email(){
        return $this->email;
    }

    public function mdp(){
        return $this->mdp;
    }
   

    public function getNom(){
        return $this->nom;
    }

    public function getPrenom(){
        return $this->prenom;
    }

    public function rue(){
        return $this->rue;
    }
    public function CodePostal(){
        $CodePostalManager = new CodePostalManager;   
        $CodePostal =  $CodePostalManager->getCp($this->codePostal);
        return $CodePostal;
    }

    public function getTel(){
        return $this->tel;
    }

    //Tableau d'objet
    public function listCmd(){
        $listeCmd = array();
        $CommandeManageur = new CommandeManager();
       

        foreach ($this->listeIdCmd as $id){
            $listeCmd[]= $CommandeManageur->getCommande($id);
        }

       return $listeCmd;
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

    public function dateInscription(){
        $dateFormat = null ;
  
        if( $this->dateInscription != null){
           $date= new DateTime($this->dateInscription);
           $dateFormat = date_format($date, 'd/m/Y à H\hi') ;
        }
  
        return $dateFormat;
    }



    // AUTRE METHODE
    public function compteActive(){
        $active= true;
        
        if ($this->active == 0) {
            $active= false;
        }


        return $active;
    }




}

?>