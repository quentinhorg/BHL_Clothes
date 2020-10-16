<?php 

class Commande{
   private  $num;
   private  $dateCreation;
   private  $idClient;
   private  $Etat; //Object
   private  $prixTTC;
   private  $typePaiement;
   protected  $panier= array() ; //Tableau Objet : Article
   private  $totalArticle;
   private  $prixHT;
 
   
   
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
      $this->setPanier();
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

   public function setTypePaiement($typeP){
      
      $this->typePaiement = $typeP;
   
}

   public function setNum($num){
      $num = (int) $num;
      
      if($num > 0){
         $this->num = $num;
      }
   }

   public function setPanier(){
      $ArticleManager = new ArticleManager;
      $this->panier = $ArticleManager->getListeArticleByCmd($this->num) ;
   }

   public function setIdEtat($idEtat){
      $EtatManager = new EtatManager;
      $this->Etat = $EtatManager->getEtat($idEtat);
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
      return $this->panier;
   }

   public function Etat(){
      return $this->Etat;
   }

   public function typePaiement(){
      return $this->typePaiement;
   }

   public function prixTTC(){
      return number_format($this->prixTTC, 2) ;
   }

   public function totalArticle(){
      return $this->totalArticle;
   }

   public function prixHT(){
      return $this->prixHT;
   }

   


   //AUTRES METHODES


   public function getQuantiteArticle(){
      $totalQte = 0;
      
         foreach ($this->panier as $article) {
          
            $totalQte = $totalQte+$article->qte();
         }
     
      

      return $totalQte;
   }


   public function indiceArticlePanier( $idVet, $taille, $numClr){
      $indicePanier = null;

      if($this->panier != null){
         foreach ($this->panier as $indice => $article) {
         
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