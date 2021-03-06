<?php

class Vue{

   // ATTRIBUTS
   public    $base;
   protected $fichier;
   protected $template;
   protected $titre;
   protected $listeCss;
   protected $nav;
   protected $header;
   protected $listeJsScript;
   protected $footer;
   public    $Popup;
    
    //Construction de la vue
   public function __construct($page){
      //Initialisation par défaut
      $this->base = DOSSIER_SERVER;
      $this->fichier= 'vue/vue'.ucfirst($page).'.php';
      $this->template= "vue/template.php" ;
      $this->titre= $page;
      $this->listeCss= ["public/css/".strtolower($page).".css"] ;
      $this->nav= "vue/navigation.php";
      $this->footer = "vue/footer.php";
      $this->listeJsScript= array() ;
      $this->Popup = new Popup;
      
    }

    public function setListeJsScript($listeFichier){

         foreach ($listeFichier as $fichier) {
            if (file_exists($fichier)){
               $this->listeJsScript[] = $fichier;
            }

          }

   }

   public function setListeCss($listeFichier){

      foreach ($listeFichier as $fichier) {
            $this->listeCss[] = $fichier;
       }

}

   public function setHeader($fichier){

      $this->header = $fichier ;

   }


    protected function genererFichier($fichier, $donnee){
      
        if (file_exists($fichier)){
         
           extract($donnee); //Extraction des données
   
           ob_start(); //A COMPLETER
  
           //INCLUT LE FICHIER VUE
           require $fichier;
           
           return ob_get_clean();
  
  
        }
        else{
           throw new Exception('Fichier '.$fichier.' introuvable') ;
        }


     }

      private function getNav(){
         $GenreManager = new GenreManager() ;
         $CommandeManager = new CommandeManager() ;


         $donnee = [
            "listeGenre" => $GenreManager->getListeGenre(),
            "qtePanier" => $CommandeManager->getCmdActiveClient()->totalArticle(),
            "clientEnLigne" => $GLOBALS["user_en_ligne"]
         ] ;

         $nav = $this->genererFichier($this->nav, $donnee) ;
     
         return $nav;
      }


     public function genererVue($donnee){
     
      //PARTIE DE LA VUE
      $contenu = $this->genererFichier($this->fichier, $donnee);
     
      //$nav = null;
      $nav = $this->getNav();
      


      if($this->header != null){
         $header =  $this->genererFichier($this->header, $donnee);
      }else{ $header  = null;}
      
      $footer= $this->genererFichier($this->footer, $donnee);
     
      //Génération final
      $vue = $this->genererFichier($this->template, array(
         'base' => $this->base, 
         'titre' => $this->titre, 
         'contenu' => $contenu,
         'nav' => $nav,
         'header' => $header,
         'footer' => $footer,
         'listeCss' => $this->listeCss,
         "listeJsScript"=> $this->listeJsScript,
         "Popup" => $this->Popup
      ));

      //Intégration de la vue
      echo $vue;
     
       
     }



}



?>