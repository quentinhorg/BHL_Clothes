<?php 

class Commande{
   private  $num;
   private  $date;
   private  $idClient;
   private  $Etat; //Object
   private  $panier= array() ; //Tableau Objet : Article
 
   
   
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


   //AUTRES METHODES

   public function ajouterPanier( $idVet, $taille, $qte, $idClr){
      $possedeDeja = false ;

      $ArticleManager = new ArticleManager;

      $nouvelArticle = $ArticleManager->tranformArticle($idVet) ;
      $nouvelArticle->setTaille($taille);
      $nouvelArticle->setQte($qte);
      $nouvelArticle->setCouleur($idClr);

      

      foreach ($this->panier as $article) {
         
          //Vérifie si les caractéritique de l'article à ajouté se trouve déjà dans le panier
         if( 
            $article->id() == $nouvelArticle->id()
            && $article->Taille()->libelle() == $nouvelArticle->Taille()->libelle()
            && $article->Couleur()->num() == $nouvelArticle->Couleur()->num()
         ){
            //Faire la somme des quantités si même caractéritique
            $totalQte = $article->qte() + $nouvelArticle->qte() ;
            $article->setQte($totalQte);
            $possedeDeja = true; 
            break;
         }

      }

      if($possedeDeja == false){
         $this->panier[] = $nouvelArticle;
      }

   }

   public function getQuantiteArticle(){
      $totalQte = 0;

         foreach ($this->panier as $article) {
            $totalQte = $totalQte+$article->qte();
         }
     
      

      return $totalQte;
   }




}

?>