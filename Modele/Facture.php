<?php 

class Facture{
   private   $numCmd;
   private   $nomProp;
   public    $prenomProp;
   private   $rueLiv;
   private   $cpLiv;
   private   $typePaiement; 
   private   $datePaiement; 
   private   $soldeAvantPaiement; 
   private   $soldeApresPaiement; 
  


   public function __construct(array $donnee){
 
      foreach($donnee as $cle => $valeur){
         $methode = 'set'.ucfirst($cle);
      
         if(method_exists($this, $methode)){
            $this->$methode($valeur);
         }
      }
   }



   
   //SETTER
   public function setNumCmd($numCmd){
    
    $this->numCmd = $numCmd;
   }

   public function setNomProp($nomProp){
      if(is_string($nomProp)){
         $this->nomProp = $nomProp;
      }
   }

   public function setRueLiv($rueLiv){
      if(is_string($rueLiv)){
         $this->rueLiv = $rueLiv;
      }
   }

   public function setTypePaiement($typeP){
      if(is_string($typeP)){
         $this->typePaiement = $typeP;
      }
   } 

   public function setPrenomProp($prenomProp){
      if(is_string($prenomProp)){
         $this->prenomProp = $prenomProp;
      }
   }

   public function setSoldeAvantPaiement($solde){
      $solde = (float) $solde;
      if($solde > 0){
         $this->soldeAvantPaiement = $solde;
      }

   } 

   public function setSoldeApresPaiement($solde){
      $solde = (float) $solde;
      if($solde > 0){
         $this->soldeApresPaiement = $solde;
      }

   } 
   
   
   public function setCpLiv($cpLiv){

      $this->cpLiv = $cpLiv;
   }

   public function setDatePaiement($date){

         $this->datePaiement = $date;
  
   } 


 
   

   //GETTER

   public function Commande(){
      $CommandeManager = new CommandeManager;
      $Commande = $CommandeManager->getCommande($this->numCmd);
      return $Commande;
   }

   public function nomProp(){
      return $this->nomProp;
   }

   public function prenomProp(){
      return $this->prenomProp ;
   }

   public function typePaiement(){
      return $this->typePaiement;
   }

   public function soldeAvantPaiement(){
      return $this->soldeAvantPaiement;
   }

   public function soldeApresPaiement(){
      return $this->soldeApresPaiement;
   }



   public function datePaiement(){

      $dateFormat = null ;

      if( $this->datePaiement != null){
         $date= new DateTime($this->datePaiement);
         $dateFormat = date_format($date, 'd/m/Y à H\hi') ;
      }

      return $dateFormat;
      
   }

   public function rueLiv(){
      return $this->rueLiv;
   }

   public function CodePostal(){

      $CodePostalManager= new CodePostalManager;
      $CodePostal = $CodePostalManager->getCp($this->cpLiv);

      return $CodePostal;
   }


   




}

?>