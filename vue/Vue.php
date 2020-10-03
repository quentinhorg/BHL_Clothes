<?php

class Vue{

   // ATTRIBUTS
    private $fichier;
    private $template;
    private $titre;
    private $listeCss;
    private $nav;
    private $header;
    private $listeJsScript;
    
    //Construction de la vue
   public function __construct($page){
      //Initialisation par défaut
    
      $this->fichier= 'vue/vue'.ucfirst($page).'.php';

      $this->template= "vue/template.php" ;
      $this->titre= $page;
      $this->listeCss= ["public/css/".strtolower($page).".css"] ;
      $this->nav= "vue/navigation.php";
      $this->listeJsScript= array() ;
    }

    public function setListeJsScript($listeFichier){

         foreach ($listeFichier as $fichier) {
            if (file_exists($fichier)){
               $this->listeJsScript[] = $fichier;
            }

          }

   }

   public function setHeader($fichier){

      $this->header = $fichier ;

   }


    private function genererFichier($fichier, $donnee){
      
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
         $ClientManager = new ClientManager() ;
        
     
         $donnee = [
            "listeGenre" => $GenreManager->getListeGenre(),
            "qtePanier" => $CommandeManager->getCmdActiveClient()->getQuantiteArticle(),
             "clientEnLigne" => $ClientManager->ClientEnLigne()
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
      
      $footer= $this->genererFichier("vue/footer.php", $donnee);
     
      //Génération final
      $vue = $this->genererFichier($this->template, array(
         'titre' => $this->titre, 
         'contenu' => $contenu,
         'nav' => $nav,
         'header' => $header,
         'footer' => $footer,
         'listeCss' => $this->listeCss,
         "listeJsScript"=> $this->listeJsScript
      ));

      //Intégration de la vue
      echo $vue;
     
       
     }



}



?>