<?php 

class Facture{
   private   $Commande; //Object
   private   $nomProp;
   public    $prenomProp;
   private   $rueLiv;
   private   $CodePostal; // OBJET
   private   $typePaiement; 
   public    $datePaiement; 


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
    
      $CommandeManager = new CommandeManager;
      $this->Commande = $CommandeManager->getCommande($numCmd);
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
   
   public function setCpLiv($cpLiv){
      $CodePostalManager= new CodePostalManager;
      $this->CodePostal = $CodePostalManager->getCp($cpLiv);
   }



 
   

   //GETTER

   public function Commande(){
      return $this->Commande;
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

   public function datePaiement(){
      return $this->datePaiement;
   }

   public function rueLiv(){
      return $this->rueLiv;
   }

   public function CodePostal(){
      return $this->CodePostal;
   }


   




}

?>