<?php 

class Commande{
   private  $num;
   private  $dateCreation;
   private  $idClient;
   private  $idEtat;
   private  $prixTTC;
   private  $totalArticle;
   protected  $prixHT;
   
 
   
   
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
   public function setIdClient($idClient){
      $idClient = (int) $idClient;

      if($idClient > 0){
         $this->idClient = $idClient;
      }
   }


   public function setDateCreation($date){

         $this->dateCreation = $date;
      
   }


   public function setNum($num){
      $num = (int) $num;
      
      if($num > 0){
         $this->num = $num;
      }
   }

   public function setIdEtat($idEtat){
      $this->idEtat = $idEtat;
   }

   public function setPrixTTC($prix){
      
      $prix = (float) $prix;

      if($prix > 0){
      
         $this->prixTTC = $prix;
      }
   }

   public function setTotalArticle($totalArticle){
      $this->totalArticle = $totalArticle;
   }

   public function setPrixHT($prixHT){
      $this->prixHT = $prixHT;
   }



   


 
   

   //GETTER

   public function idClient(){
      return $this->idClient;
   }

   public function Client(){
      $ClientManager = new ClientManager;
      return $ClientManager->getClient($this->idClient);
   }

   public function dateCreation(){
      $dateFormat = null ;

      if( $this->dateCreation != null){
         $date= new DateTime($this->dateCreation);
         $dateFormat = date_format($date, 'd/m/Y à H\hi') ;
      }

      return $dateFormat;
   }

   public function num(){
      return $this->num;
   }

   public function panier(){
      $ArticleManager = new ArticleManager;
      $panier = $ArticleManager->getListeArticleByCmd($this->num) ;
      return $panier;
   }

   public function Etat(){
      $EtatManager = new EtatManager;
      $Etat = $EtatManager->getEtat($this->idEtat);
      return $Etat;
   }

   public function prixTTC(){
      return number_format($this->prixTTC, 2) ;
   }

   public function totalArticle(){
      return $this->totalArticle;
   }

   public function prixHT(){
      return number_format($this->prixHT, 2) ;
   }

   public function getFacture(){
      $FactureManager = new FactureManager;
      return $FactureManager->getFacture($this->num);
   }

   


   //AUTRES METHODES

   


   public function indiceArticlePanier( $idVet, $taille, $numClr){
      $indicePanier = null;

      if($this->panier() != null){
         foreach ($this->panier() as $indice => $article) {
         
            if( 
               $article->id() == $idVet
               && $article->Taille()->libelle() ==  $taille
               && $article->Couleur()->num() == $numClr
            ){
         
               $indicePanier = $indice; 
             
            }
    
         }
      }
    
     return $indicePanier;
   }



}

?>