<?php 

class Commande{
   private  $num;
   private  $date;
   private  $idClient;
   private  $Etat; //Object
   private  $prixTTC;
   protected  $panier= array() ; //Tableau Objet : Article
 
   
   
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


   public function setDate($date){
      
         $this->date = $date;
      
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


 
   

   //GETTER

   public function idClient(){
      return $this->idClient;
   }

   public function date(){
      $date= new DateTime($this->date);
      $dateFormat = date_format($date, 'd/m/Y à H\hi') ;

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
   public function prixTTC(){

      
      return number_format($this->prixTTC, 2) ;
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